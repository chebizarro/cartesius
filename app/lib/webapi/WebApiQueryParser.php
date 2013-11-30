<?php

namespace WebApi;

use \PDO;

class WebApiQueryParser {
	protected $resource;
	protected $metadata;
	
	protected $filter;
	protected $expand;
	protected $select;
	protected $orderby;
	protected $top;
	protected $skip;
	protected $inlinecount;
	protected $format;

	function __construct($resource, $connection, $query, $metadata) {
		// expand: expand, select
		// join: filter, orderby
		$this->resource = $resource;
		$this->metadata = $metadata;
		$this->connection = $connection;
		$this->filter = isset($query['$filter']) ? QueryLexer::run('filter',$query['$filter']) : null;
		$this->expand = isset($query['$expand']) ? QueryLexer::run('expand', $query['$expand']) : null;
		$this->select = isset($query['$select']) ? QueryLexer::run('select', $query['$select']) : null;
		$this->orderby = isset($query['$orderby']) ? QueryLexer::run('orderby', $query['$orderby']) : null;
		$this->top = isset($query['$top']) ? intval($query['$top']) : null;
		$this->skip = isset($query['$skip']) ? intval($query['$skip']) : null;
		$this->inlinecount = isset($query['$inlinecount']) ? $query['$inlinecount'] : null;
		$this->format = isset($query['$format']) ? $query['$format'] : null;
		$this->data = $data = \ORM::for_table($resource, $connection);
	}
	
	public function parse() {
		// These two will create joins if there are expanded properties
		(isset($this->filter)) ? $this->filter() : null;
		(isset($this->orderby)) ? $this->orderby() : null;

		(isset($this->top)) ? $this->top() : null;
		(isset($this->skip)) ? $this->skip() : null;
		
		
		//$this->data = $this->data->find_many();
		// These two will expand the data
		(isset($this->select)) ? $this->select() : null;		
		(isset($this->expand)) ? $this->expand() : null;
	}
	
	protected function filter() {
		$token = $this->filter[0]["token"];
		switch ($token)
		{
			case T_RESOURCE: return $this->filter_column();
			case T_BLOCK: return $this->filter_block();
			case T_LENGTH: return $this->filter_length();
			case T_SUBSTRING_OF: return $this->filter_substring_of();
			case T_STARTS_WITH: return $this->filter_starts_with();
			case T_NOT: return $this->filter_not();
			//case T_TO_UPPER: return $this->filter_function(T_TO_UPPER,);
			//case T_SUBSTRING: return $this->filter_function(T_SUBSTRING,);
			default: return $data;	
		}
	}

	protected function filter_column() {
		$column_name = $this->resource.".".$this->filter[0]["match"];
		$condition = $this->filter[1]["token"];
		$value = $this->filter[2]["match"];
		switch ($condition)
		{
			case T_GT: $this->data = $this->data->where_gt($column_name, $value);
			case T_LT: $this->data = $this->data->data->where_lt($column_name, $value);
			case T_EQ: $this->data = $this->data->data->where_equal($column_name, $value);
			case T_GE: $this->data = $this->data->data->where_gte($column_name, $value);
			case T_LE: $this->data = $this->data->data->where_lte($column_name, $value);
			case T_NE: $this->data = $this->data->data->where_not_equal($column_name, $value);
		}		
	}

	protected function expand() {

	}

	protected function select() {
		foreach($this->select as $select) {
			$token = $select["token"];
			$entityCollection = $this->metadata->get_structural_type($this->resource);
			switch($token) {
				case T_EXPAND:
					$expandedEntityCollection = $entityCollection;
					foreach($orderby["match"] as $expand) {
						$navProperty = $expandedEntityCollection->get_navigation_property($expand["match"]);
						if($navProperty) {
							$this->join($navProperty, $expand["match"]);
							$expandedEntityCollection = $this->metadata->get_structural_type($expand["match"]);
						} else {
							$navProperty = $expandedEntityCollection->get_data_property($expand["match"]);
							if($navProperty) {
								$column = "{$expandedEntityCollection->defaultResourceName}.{$expand['match']}";
								$this->data = $this->data->select($column);
							}
						}
					}
					break;
				case T_RESOURCE:
					if($entityCollection->get_data_property($select["match"])) {
						$this->data = $this->data->select("{$this->resource}.{$select['match']}");
					}
					break;
			}
		}
	}

	protected function join($navProperty, $resource) {
		$navEntityCollection = $this->metadata->get_structural_type($resource);
		$revNavProperty = $navEntityCollection->get_navigation_property($this->resource);
		$this->data = $this->data->join(
			$resource,
			array("{$expand['match']}.{$revNavProperty['invForeignKeyNames'][0]}",
			"=",
			"{$this->resouce}.{$navProperty['foreignKeyNames'][0]}"),
			$this->resource
		);
	}

	protected function orderby() {

		foreach($this->orderby as $orderby) {
			$token = $orderby["token"];
			$entityCollection = $this->metadata->get_structural_type($this->resource);
			switch($token) {
				case T_EXPAND:
					$expandedEntityCollection = $entityCollection;
					foreach($orderby["match"] as $expand) {
						$navProperty = $expandedEntityCollection->get_navigation_property($expand["match"]);
						if($navProperty) {
							$this->join($navProperty, $expand["match"]);
							$expandedEntityCollection = $this->metadata->get_structural_type($expand["match"]);
						} else {
							$navProperty = $expandedEntityCollection->get_data_property($expand["match"]);
							if($navProperty) {
								$column = "{$expandedEntityCollection->defaultResourceName}.{$expand['match']}";
								if($expand["token"] === T_ORDERBY) {
									$this->data = $this->data->order_by_asc($column);
								} else{
									$this->data = $this->data->order_by_desc($column);
								}
							}
						}
					}
					break;
				case T_ORDERBY:
					if($entityCollection->get_data_property($orderby["match"])) {
						$this->data = $this->data->order_by_asc("{$this->resource}.{$orderby['match']}");
					}
					break;
				case T_ORDERBYDESC:	
					if($entityCollection->get_data_property($orderby["match"])) {
						$this->data = $this->data->order_by_desc("{$this->resource}.{$orderby['match']}");
					}
					break;
			}
		}
	}
	
	/* 
	 * Top and Skip functions (limit & offset)
	 */

	protected function top() {
		$this->data = $this->data->limit($this->top);
	}

	protected function skip() {
		$this->data = $this->data->offset($this->skip);
	}


}
