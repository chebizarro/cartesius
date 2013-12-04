<?php

namespace WebApi;

use \PDO;

interface QueryParserInterface {
	public function parse();
	public function execute();
}

abstract class QueryParser implements QueryParserInterface {

	protected $resource;
	protected $service;
	protected $metadata;
	protected $response;
	
	protected $filter;
	protected $expand;
	protected $select;
	protected $orderby;
	protected $top;
	protected $skip;
	protected $inlinecount;
	protected $format;
	
	protected $count;
	protected $joins;

	public function __construct($resource, $query, $service, $metadata) {
		// expand: expand, select
		// join: filter, orderby
		$this->service = $service;
		$this->metadata = $metadata;
		$this->resource = $resource;
		$this->entities = $this->metadata->get_resource($this->service->parse_nc($this->resource));

		$this->filter = isset($query['$filter']) ? QueryLexer::run('filter',$query['$filter']) : null;
		$this->expand = isset($query['$expand']) ? QueryLexer::run('expand', $query['$expand']) : null;
		$this->select = isset($query['$select']) ? QueryLexer::run('select', $query['$select']) : null;
		$this->orderby = isset($query['$orderby']) ? QueryLexer::run('orderby', $query['$orderby']) : null;
		$this->top = isset($query['$top']) ? intval($query['$top']) : null;
		$this->skip = isset($query['$skip']) ? intval($query['$skip']) : null;
		$this->inlinecount = isset($query['$inlinecount']) ? $query['$inlinecount'] : null;
		$this->format = isset($query['$format']) ? $query['$format'] : null;
		
		$this->joins = [];
		
		$this->response = $this->new_response();
	}
	
	abstract protected function new_response();
	abstract protected function top();
	abstract protected function skip();

	abstract protected function _filter($property, $value, $condition);
	abstract protected function _orderby($property, $order);
	abstract protected function _join($resource);
	
	public function parse() {
		// These two will create joins if there are expanded properties
		(isset($this->filter)) ? $this->filter($this->filter) : null;
		(isset($this->orderby)) ? $this->orderby() : null;

		(isset($this->top)) ? $this->top() : null;
		(isset($this->skip)) ? $this->skip() : null;
		
		// These two will expand the data
		(isset($this->select)) ? $this->select() : null;		
		(isset($this->expand)) ? $this->expand() : null;
	}

	public function execute() {

	}

	/* Filter */

	protected function filter($filter) {
		$token = $filter[0]["token"];
		switch ($token)
		{
			case T_RESOURCE: $this->filter_property($filter); break;
			case T_BLOCK: $this->filter_block($filter); break;
			case T_LENGTH: $this->filter_length($filter); break;
			case T_SUBSTRING_OF: $this->filter_substring_of($filter); break;
			case T_STARTS_WITH: $this->filter_starts_with($filter); break;
			case T_NOT: $this->filter_not(); break;
			//case T_TO_UPPER: return $this->filter_function(T_TO_UPPER,);
			//case T_SUBSTRING: return $this->filter_function(T_SUBSTRING,);
		}
	}

	protected function filter_property($filter) {
		$property = $filter[0]["match"];
		if($this->entities->property_exists($property)) {
			$property = $this->resource.".".$property;
			$condition = $filter[1]["token"];
			$value = $filter[2]["match"];
			$this->response = $this->_filter($property, $value, $condition);
		} else {
			throw new \Exception('Query error: Property does not exist');
		}
	}

	private function filter_block($filter) {
		$token = $filter[0]["match"][0]["token"];
	
		$operator = $filter[1]["token"];
		$left = $filter[0]["match"];
		$right = $filter[2]["match"];
		switch ($token) {
			case T_RESOURCE :
				if($operator == T_AND) {
					$this->filter($left);
					$this->filter($right);
				} elseif ($operator == T_OR) {
					/*
					$data = $data->where_raw(
						'("'.$left[0]['match'].'"'.$left[1]['match']."'".$left[2]['match']."'".
						" OR ".
						'"'.$right[0]['match'].'"'.$right[1]['match']."'".$right[2]['match']."')"
					);
					*/
				}
				break;
			case T_BLOCK :
				$operator = $filter[1]["token"];
				if(!is_array($operator)) {
					if($operator == T_AND || $operator == T_OR) {
						$this->filter($left);
						$this->filter($right);
					}
				} else {
					
				}
				break;
			case T_STARTS_WITH :
				if($operator == T_AND) {
					$this->filter($left);
					$this->filter($right);
					break;
				}
		}
	}

	private static function filter_not($filter) {
		$property = '"'.$filter[1]["match"][0]["match"].'"';
		$condition = $filter[1]["match"][1]["match"];		
		$value = "'".$filter[1]["match"][2]["match"]."'";
		//return $data->where_raw('NOT ('.$column_name.$condition.$value.')');
	}

	/*
	 * Functions
	 */

	private static function filter_function($function, $query, $data) {
		switch ($function) {
			case T_TO_UPPER:
				$string = self::filter_to_upper($query);
				break;
		}
		//return $data->where_raw($string);
	}

	private static function filter_substring_of($query, $data) {
		$column_name = $query[1]["match"][0]["match"][1]["match"];
		$value = '%' . $query[1]["match"][0]["match"][0]["match"] . '%';
		$condition = $query[3]["token"];		
		//return ($condition == T_TRUE) ? $data->where_like($column_name, $value) : $data->where_not_like($column_name, $value);
	}

	private static function filter_starts_with($filter) {
		$column_name = self::filter_join($query[1]["match"][0]["match"], $data);		
		$value = $query[1]["match"][1]["match"][0]["match"] . '%';
		$condition = $query[3]["token"];		
		//return ($condition == T_TRUE) ? $data->where_like($column_name, $value) : $data->where_not_like($column_name, $value);
	}

	private static function filter_to_upper($query) {
		$function = "upper(";
		if($query[1]["token"][0]["token"] != T_RESOURCE) {
			$function .= self::filter($query[1]["match"]).")";
		} else {
			$function .= '"'.$query[1]["match"][0]["match"].")";
		}		
		$function .= $query[2]["match"] . $query[3]["match"];
		//return $function;
	}

	private static function filter_length($query, $data) {
		$column_name = $query[1]["match"][0]["match"];
		//return $data->where_raw('length("'.$column_name.'")'.$query[2]["match"].trim($query[3]["match"]));
	}


	/* Select */

	protected function select() {
		foreach($this->select as $select) {
			$token = $select["token"];
			$entityCollection = $this->entities;
			switch($token) {
				case T_EXPAND:
					$expandedEntityCollection = $entityCollection;
					foreach($orderby["match"] as $expand) {
						$navProperty = $expandedEntityCollection->get_navigation_property($expand["match"]);
						if($navProperty) {
							$this->join($navProperty, $expand["match"]);
							$expandedEntityCollection = $this->metadata->get_resource($expand["match"]);
						} else {
							$navProperty = $expandedEntityCollection->get_data_property($expand["match"]);
							if($navProperty) {
								$column = "{$expandedEntityCollection->defaultResourceName}.{$expand['match']}";								
								//$this->response = $this->resource->select($this->response, $column);
							}
						}
					}
					break;
				case T_RESOURCE:
					if($entityCollection->get_data_property($select["match"])) {
						//$this->response = $this->resource->select($this->response, "{$this->resource}.{$select['match']}");
					}
					break;
			}
		}
	}


	/* Orderby */

	protected function orderby() {
		foreach($this->orderby as $orderby) {
			$token = $orderby["token"];
			$entityCollection = $this->entities;
			switch($token) {
				case T_EXPAND:
					$expandedEntityCollection = $entityCollection;
					foreach($orderby["match"] as $expand) {
						$navProperty = $expandedEntityCollection->navigation_property_exists($expand["match"]);
						if($navProperty) {
							
							$this->join($navProperty, $expand["match"]);
							$expandedEntityCollection = $this->metadata->get_resource($expand["match"]);
						} else {
							$navProperty = $expandedEntityCollection->get_data_property($expand["match"]);
								$property = "{$expandedEntityCollection->defaultResourceName}.{$expand['match']}";
								$this->response = $this->_orderby($property, $expand["token"]); 
							} else{
								throw new \Exception('Query error: Property does not exist');
							}
						}
					}
					break;
				case T_ORDERBY:
				case T_ORDERBYDESC:	
					if($entityCollection->data_property_exists($orderby["match"])) {
						$this->response = $this->_orderby("{$this->resource}.{$orderby['match']}", $token);
					} else {
						throw new \Exception('Query error: Property does not exist');
					}
					break;
				default: throw new \Exception('Query error: Invalid Orderby clause');
			}
		}
	}


	protected function join($resource, $service) {
		if(!isset($this->joins[$resource])) {
			
			$property = $this->entities->get_navigation_property($this->resource);
			if ($property) {
				$this->_join($property);
			} else {
				throw new \Exception("Query error: Property: {$property} does not exist");
			}
		}
	}

	/* Expand */

	protected function expand() {

	}
	
}

class ORMQueryParser extends QueryParser {
	
	protected function new_response() {
		return \ORM::for_table($this->resource, $this->service->get_name())->create();
	}


	protected function _filter($property, $value, $condition) {
		switch ($condition)
		{
			case T_GT: return $this->response->where_gt($property, $value);
			case T_LT: return $this->response->where_lt($property, $value);
			case T_EQ: return $this->response->where_equal($property, $value);
			case T_GE: return $this->response->where_gte($property, $value);
			case T_LE: return $this->response->where_lte($property, $value);
			case T_NE: return $this->response->where_not_equal($property, $value);
			default : return $this->response;
		}
	}
	
	protected function _orderby($property, $order) {
		switch ($order)
		{
			case: T_ORDERBY: return $this->response->order_by_asc($column);
			case: T_ORDERBYDESC: return $this->response->order_by_desc($column);
			default: throw new \Exception('Query error: Invalid Orderby clause');
		}
	}

	protected function _join($property) {
		$this->response = $this->response->join(
			$this->resource,
			array("{$property['name']}.{$property['invForeignKeyNames'][0]}",
			"=",
			"{$this->resouce}.{$navProperty['foreignKeyNames'][0]}"),
			$this->resource
		);		
	}
	/* 
	 * Top and Skip functions (limit & offset)
	 */

	protected function top() {
		$this->response = $this->response->limit($this->top);
	}

	protected function skip() {
		$this->response = $this->response->offset($this->skip);
	}

	public function execute() {
		$this->count = ($this->inlinecount == "allpages") ? $this->response->count(): null;
		$this->response = $this->response->find_many();
	}


}


