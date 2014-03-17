<?php

namespace WebApi;

use \PDO;

interface ResourceInterface {
	//public function parse();
	//public function execute();
}

abstract class Resource implements ResourceInterface, \JsonSerializable {

	public function __construct($resourceName, $metadata) {
		$this->metadata = $metadata;
		$this->entities = $this->metadata->getResource($this->metadata->parseNc($resourceName));
		$this->resourceName = $this->entities->getName();
		$this->resource = $this->newResource();
	}

	abstract protected function newResource();

	public function setQuery(Array $query) {
		(!isset($query['$filter'])) ? : $this->setFilter($query['$filter']);
		(!isset($query['$expand'])) ? : $this->setExpand($query['$expand']);
		(!isset($query['$select'])) ? : $this->setSelect($query['$select']);
		(!isset($query['$orderby'])) ? : $this->setOrderby($query['$orderby']);
		(!isset($query['$top'])) ? : $this->setTop($query['$top']);
		(!isset($query['$skip'])) ? : $this->setSkip($query['$skip']);
		(!isset($query['$inlinecount'])) ? : $this->setInlinecount($query['$inlinecount']);
		(!isset($query['$format'])) ? : $this->setFormat($query['$format']);		
	}

	public function setFilter($filter) {
		$this->filter[] = QueryLexer::run('filter',$filter);
	}

	public function setExpand($expand) {
		$this->expand[] = QueryLexer::run('expand', $expand);
	}
	
	public function setSelect($select) {
		$this->select[] = QueryLexer::run('select', $select);		
	}
	
	public function setOrderby($orderby) {
		$this->orderby[] = QueryLexer::run('orderby', $orderby);		
	}

	public function setTop($top) {
		$this->top = intval($top);		
	}

	public function setSkip($skip) {
		$this->skip = intval($skip);		
	}

	public function setInlinecount($inlinecount) {
		$this->inlinecount = $inlinecount;		
	}

	public function setFormat($format) {
		$this->format = $format;
	}

	/* Filter */

	protected function filter($filter) {
		$token = $filter[0]["token"];
		switch ($token)
		{
			case T_RESOURCE: $this->filter_property($filter); break;
			case T_EXPAND: return $this->filter_expand($filter[0]);
			case T_BLOCK: $this->filter_block($filter); break;
			case T_NOT: $this->filter_not($filter); break;
			case T_FUNCTION: $this->filter_function($filter); break;
			default: throw new \Exception("Query error: Filter type {$filter[0]["match"]} does not exist");

		}
	}

	protected function filter_property($filter) {
		$property = $filter[0]["match"];
		$condition = $filter[1]["token"];
		$value = $filter[2]["match"];
		if($this->entities->data_property_exists($property)) {
			$property = $this->resourceName.".".$property;
			// Modify to be a method
			$this->resource = $this->_filter($property, $value, $condition);
		} else {
			throw new \Exception('Query error: Property does not exist');
		}
	}


	protected function filter_expand($filter) {
		$expandedEntities = $this->entities;
		foreach($filter["match"] as $expand) {
			if($expandedEntities->navigation_property_exists($expand["match"])) {
				$nav_from = $expandedEntities;
				$nav_to = $this->metadata->get_resource($expand["match"]);
				$this->join($nav_from, $nav_to);
				$expandedEntities = $nav_to;
			} else {
				$nav_property = $expandedEntities->get_data_property($expand["match"]);
				$property["resource"] = $expandedEntities->get_name();
				$property["property"] = $expand['match'];
				return $property;
			}
		}
	}

	protected function filter_block($filter) {
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
					$this->_filter_or($left, $right);
				}
				break;
			case T_BLOCK :
				$operator = $filter[1]["token"];
				$this->filter($left);
				$this->filter($right);
				break;
			case T_FUNCTION :
				$this->filter($left);
				$this->filter($right);
				break;
			case T_NOT:
				$this->filter_not($filter);
				break;
		}
	}
	
	protected function filter_not($filter) {
		$property = $filter[1]["match"][0]["match"];
		if($this->entities->data_property_exists($property)) {
			$condition = $filter[1]["match"][1]["match"];
			$value = $filter[1]["match"][2]["match"];
			$this->_filter_not($property, $condition, $value);
		} else {
			throw new \Exception('Query error: Property does not exist');
		}
	}

	/* to be abstacted */
	protected function filter_function($filter) {
		$function = $this->call_function($filter);
		if($function) {
			$this->resource = $this->resource->where_raw($function);
		}
	}


	/* Orderby */

	protected function orderby() {
		foreach($this->orderby as $orderby) {
			$token = $orderby["token"];
			$entityCollection = $this->entities;
			switch($token) {
				case T_EXPAND:
					$property = $this->filter_expand($orderby);
					$property = "{$property['resource']}.{$property['property']}";
					$order = end($orderby['match']);
					$this->_orderby($property, $order['token']); 
					break;
				case T_ORDERBY:
				case T_ORDERBYDESC:
					$property = $orderby["match"];
					if($entityCollection->data_property_exists($property)) {
						$this->_orderby("{$this->resource}.{$property}", $token);
					} else {
						throw new \Exception("Query Orderby error: Property: {$property} does not exist");
					}
					break;
				default: throw new \Exception("Query error: Invalid Orderby clause");
			}
		}
	}


	/* abstract functions to be implemented by the sub class */
	abstract protected function newResource();

	abstract protected function top();
	abstract protected function skip();

	abstract protected function _filter($property, $value, $condition);
	abstract protected function _orderby($property, $order);
	abstract protected function _filterNot($property, $condition, $value);
	abstract protected function _filterOr($left, $right);


	abstract public function jsonSerialize();

}

class ORMResource extends Resource {

	protected function newResource() {
		return \ORM::for_table($this->resourceName, $this->metadata->getServiceName())->create();
	}

	/* implementation specific functions */

	// Modify to be a method
	protected function _filter($property, $value, $condition) {
		switch ($condition)
		{
			case T_GT: return $this->resource->where_gt($property, $value);
			case T_LT: return $this->resource->where_lt($property, $value);
			case T_EQ: return $this->resource->where_equal($property, $value);
			case T_GE: return $this->resource->where_gte($property, $value);
			case T_LE: return $this->resource->where_lte($property, $value);
			case T_NE: return $this->resource->where_not_equal($property, $value);
			default : return $this->resource;
		}
	}
	
	protected function _filterNot($property, $condition, $value) {
		$this->resource = $this->resource->where_raw("NOT (\"{$property}\" {$condition} '{$value}')");
	}
	
	protected function _filterOr($left, $right) {
		$this->resource = $this->resource->where_raw(
			"(\"{$left[0]['match']}\"{$left[1]['match']}'{$left[2]['match']}')".
			" OR ".
			"(\"{$right[0]['match']}\"{$right[1]['match']}'{$right[2]['match']}')"
		);
	}
	
	protected function _orderby($property, $order) {
		switch ($order)
		{
			case T_ORDERBY:
				$this->resource = $this->resource->order_by_asc($property);
				break;
			case T_ORDERBYDESC:
				$this->resource = $this->resource->order_by_desc($property);
				break;
			default: throw new \Exception('Query error: Invalid Orderby clause');
		}
	}


	/* 
	 * Top and Skip functions (limit & offset)
	 */

	protected function top() {
		$this->resource = $this->resource->limit($this->top);
	}

	protected function skip() {
		$this->resource = $this->resource->offset($this->skip);
	}

	public function jsonSerialize() {
		
	}

}
