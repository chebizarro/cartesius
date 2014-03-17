<?php

namespace WebApi;

use \PDO;

interface ResourceInterface {
	//public function parse();
	//public function execute();
}

abstract class Resource implements ResourceInterface, \JsonSerializable {
	
	protected $metadata;
	protected $entities;
	protected $resourceName;
	protected $resource;

	protected $filter;
	protected $expand;
	protected $select;
	protected $orderby;
	protected $top;
	protected $skip;
	protected $inlinecount;
	protected $format;

	public function __construct($resourceName, $metadata) {
		$this->metadata = $metadata;
		$this->entities = $this->metadata->getResource($this->metadata->parseNc($resourceName));
		$this->resourceName = $this->entities->getName();
		$this->resource = $this->newResource();
	}

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
			$this->_filter($property, $value, $condition);
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

	/* helper functions */
	
	/* to be abstracted */
	protected function join($nav_from, $nav_to) {
		$nav_to_property = $nav_from->get_navigation_property($nav_to->get_default_resource_name());
		$nav_from_property = $nav_to->get_navigation_property($nav_from->get_default_resource_name());
		$nav_from_name = $nav_from->get_name();
		$nav_to_name = $nav_to->get_name();

		if(!isset($this->joins[$nav_to_name])) {
			$nav_to_key = ($nav_from_property['isScalar']) ? $nav_from_property['foreignKeyNames'][0] : $nav_from->get_primary_key();
			$nav_from_key = ($nav_to_property['isScalar']) ? $nav_to_property['foreignKeyNames'][0] : $nav_from->get_primary_key();

			$this->resource = $this->resource->join(
				$nav_to_name,
				array("{$nav_to_name}.{$nav_to_key}",
				"=",
				"{$nav_from_name}.{$nav_from_key}")
			);
			$this->joins[$nav_to_name] = array($nav_from_property, $nav_to_property);
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
					/* split parameters */
					$property = "{$property['resource']}.{$property['property']}";
					$order = end($orderby['match']);
					$this->_orderby($property, $order['token']); 
					break;
				case T_ORDERBY:
				case T_ORDERBYDESC:
					$property = $orderby["match"];
					/* split parameters */
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

	protected function _filter($property, $value, $condition) {
		switch ($condition)
		{
			case T_GT: $this->resource = $this->resource->where_gt($property, $value); break;
			case T_LT: $this->resource = $this->resource->where_lt($property, $value); break;
			case T_EQ: $this->resource = $this->resource->where_equal($property, $value); break;
			case T_GE: $this->resource = $this->resource->where_gte($property, $value); break;
			case T_LE: $this->resource = $this->resource->where_lte($property, $value); break;
			case T_NE: $this->resource = $this->resource->where_not_equal($property, $value); break;
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
