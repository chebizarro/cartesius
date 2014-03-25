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
				$nav_to = $this->metadata->getResource($expand["match"]);
				$this->join($nav_from, $nav_to);
				$expandedEntities = $nav_to;
			} else {
				$nav_property = $expandedEntities->get_data_property($expand["match"]);
				$property["resource"] = $expandedEntities->getName();
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
					$this->_filterOr($left, $right);
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
				$this->filterNot($filter);
				break;
		}
	}
	
	protected function filter_not($filter) {
		$property = $filter[1]["match"][0]["match"];
		if($this->entities->data_property_exists($property)) {
			$condition = $filter[1]["match"][1]["match"];
			$value = $filter[1]["match"][2]["match"];
			$this->_filterNot($property, $condition, $value);
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
		$nav_from_name = $nav_from->getName();
		$nav_to_name = $nav_to->getName();

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
	
	
	/*
	 * Expand
	 */ 
	
		/* Expand */
	
	protected function expand_resource($nav_from, $nav_to_name, &$result) {
		$nav_to = $this->metadata->get_resource($nav_to_name);
		$this->join($nav_from, $nav_to);
		$response = clone $this->resource;
		$resource[$nav_to_name]["resource"] = $nav_to;
		$response = $response->use_id_column($nav_to->get_primary_key());
		$resource[$nav_to_name]["response"] = $response;
		$resource[$nav_to_name]["from"] =& $result;
		$resource[$nav_to_name]["result"] = $response->select("{$nav_to->get_name()}.*")->find_many()->as_array();
		$result["expand"][] = $resource;

	}


	protected function expand() {
		$resource[$this->resourceName]["resource"] = &$this->entities;
		$resource[$this->resourceName]["expand"]=[];
		$resource[$this->resourceName]["response"]= clone $this->resource;

		// These two will create joins if there are expanded properties
		//(isset($this->filter)) ? $this->filter($this->filter) : null;
		if(isset($this->filter)) {			
			foreach($this->filter as $filter) {
				$this->filter($filter);
			}
		}
		
		$this->count = ($this->inlinecount == "allpages") ? $this->resource->distinct()->count(): null;
		(isset($this->orderby)) ? $this->orderby() : null;
		
		$resource[$this->resourceName]["result"] = $this->resource->select("{$this->resourceName}.*")->find_many()->as_array();

		//$this->select();
/*
		if(count($this->selected > 0)) {
			foreach($this->selected as $select) {
				$resource[$this->resource]["select"] = $select;
			}
		}
*/
	
		$resource[$this->resourceName]["from"] = null;
				
		if(isset($this->expand)) {
			foreach($this->expand as $expanded => $expand) {
				$token = $expanded["token"];
				switch($token) {
					case T_RESOURCE:
						$this->expand_resource($this->entities, $expanded["match"], $resource[$this->resourceName]);
						break;
					case T_EXPAND: 	
						$expandedEntities = $this->entities;
						$expandedArray = &$resource[$this->resourceName];
						foreach($expanded["match"] as $expand) {
							if($expandedEntities->navigation_property_exists($expand["match"])) {
								$this->expand_resource($expandedEntities, $expand["match"], $expandedArray);
								$expandedEntities = $this->metadata->get_resource($expand["match"]);
								$oldArray = &$expandedArray["expand"][count($expandedArray["expand"])-1][$expand["match"]];
								$expandedArray = &$expandedArray["expand"][count($expandedArray["expand"])-1][$expand["match"]];
							} else {
								$oldArray["select"][] = $expand["match"];
							}					
						}
						break;
				}
			}
		}		
		
		$stack = [];
		$result = $this->expand_recursive($resource[$this->resourceName], null, null, $stack);
	}

	protected function expand_recursive($resource, $navkey, $navvalue, &$stack) {
		$ref = 0;
		$result = [];
		
		foreach ($resource["result"] as $row) {
			$row_array = $row->as_array();
			if($navkey) {
				if($row_array[$navkey] != $navvalue) {
					continue;
				}
			}

			$cereal = serialize($row_array);
			foreach($stack as $key => $val) {
				if($val === $cereal) {
					$ref = $key+1;
					break;
				}
			}
			if($ref === 0) {
				$stack[] = $cereal;
				$entity_type_name = $resource["resource"]->get_entity_type_name();
				$resource_name = $resource["resource"]->get_default_resource_name();
				$row_array = array_merge(array('$id'=>count($stack), '$type'=>$entity_type_name), $row_array);
				if(isset($resource["expand"])) {

					foreach($resource["expand"] as $expand) {
						foreach($expand as $expanded) {
							$nav_to = $expanded["resource"]->get_navigation_property($resource_name);
							$nav_to_key = ($nav_to['isScalar']) ? $nav_to['foreignKeyNames'][0] : $resource["resource"]->get_primary_key();
							$nav_from_name = $expanded["resource"]->get_default_resource_name();
							$nav_from = $resource["resource"]->get_navigation_property($nav_from_name);
							$nav_from_key = ($nav_from['isScalar']) ? $nav_from['foreignKeyNames'][0] : $resource["resource"]->get_primary_key();
							$nav_to_value = $row_array[$nav_from_key];
							$row_array[$nav_from_name] = $this->expand_recursive($expanded, $nav_to_key, $nav_to_value, $stack);						
						}
					}
					
				}
				if(isset($resource["from"])) {
					$from_name = $resource["from"]["resource"]->get_default_resource_name();
					$resource["from"]["result"] = $resource["from"]["response"]->select($resource["from"]["resource"]->get_name() . ".*")->find_many()->as_array();
					$nav_to_key = $resource["from"]["resource"]->get_primary_key();
					$nav_from = $resource["resource"]->get_navigation_property($from_name);
					$nav_from_key = ($nav_from['isScalar']) ? $nav_from['foreignKeyNames'][0] : $resource["resource"]->get_primary_key();
					$nav_to_value = $row_array[$nav_from_key];
					$row_array[$from_name] = $this->expand_recursive($resource["from"], $nav_to_key, $nav_to_value, $stack);
				}
				
				$result[] = $row_array;
			} else {
				$result[] = array("\$ref" => $ref);
			}
			$ref = 0;
		}
		return $result;
	}


	/* abstract functions to be implemented by the sub class */
	abstract protected function newResource();

	abstract protected function top();
	abstract protected function skip();

	abstract protected function _filter($property, $value, $condition);
	abstract protected function _orderby($property, $order);
	abstract protected function _filterNot($property, $condition, $value);
	abstract protected function _filterOr($left, $right);


	abstract public function get();

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

	public function get() {
		$result = [];
		
		(!isset($this->top)) ?: $this->top();
		(!isset($this->skip)) ?: $this->skip();

		if(!isset($this->select) && !isset($this->expand)) {
			// These two will create joins if there are expanded properties
			if(isset($this->filter)) {			
				foreach($this->filter as $filter) {
					$this->filter($filter);
				}
			}
			$this->count = ($this->inlinecount == "allpages") ? $this->resource->distinct()->count(): null;

			(isset($this->orderby)) ? $this->orderby() : null;
			
			$this->resource = $this->resource->find_many()->as_array();
			$counter = 0;
			$entity_type_name = $this->entities->get_entity_type_name();
			foreach($this->resource as $resource) {
				$result[] = array_merge(array('$id'=> ++$counter, '$type'=>$entity_type_name), $resource->as_array());
			}
		} else {
			$result = $this->expand();
		}
		return $result;
	}

	/* Functions */

	protected function call_function($filter) {
		$function = "function_".$filter[0]["match"];
		if(method_exists($this, $function)) {
			return $this->$function($filter);
		} else {
			throw new \Exception("Function error: Function: {$function} does not exist");			
		}
	}

	protected function function_substringof($filter) {
		$property = $filter[1]["match"][0]["match"][1]["match"];
		$value = '%' . $filter[1]["match"][0]["match"][0]["match"] . '%';
		$condition = $filter[3]["token"];
		$this->resource = ($condition == T_TRUE) ? $this->resource->where_like($property, $value) : $this->resource->where_not_like($property, $value);
		return null;
	}

	protected function function_startswith($filter) {
		$token = $filter[1]["match"][0]["token"];
		switch($token) {
			case T_EXPAND:
				$property = $this->filter_expand($filter[1]["match"][0]);
				$property = "{$property['resource']}.{$property['property']}";
				break;
			case T_RESOURCE:
				$property = $filter[1]["match"][0]["match"];
				break;			
			case T_FUNCTION:
				$property = $this->call_function($filter[1]["match"][0]["match"]);
				break;
			default: 
		}
		$value = $filter[1]["match"][1]["match"][0]["match"] . '%';
		$condition = $filter[3]["token"];
		$this->resource = ($condition == T_TRUE) ? $this->resource->where_like($property, $value) : $this->resource->where_not_like($property, $value);
		return null;
	}

	protected function function_toupper($filter) {
		$function = "upper(";
		if($filter[1]["token"][0]["token"] != T_RESOURCE) {
			$function .= $this->call_function($filter[1]["match"]).")";
		} else {
			$function .= "\"{$filter[1]["match"][0]["match"]}\")";
		}		
		$function .= $filter[2]["match"] . "'{$filter[3]["match"]}'";
		return $function;
	}

	protected function function_length($filter) {
		$function = "length(";
		if($filter[1]["match"][0]["token"] != T_RESOURCE) {
			$function .= $this->call_function($filter[1]["match"]).")";
		} else {
			$function .= "\"{$filter[1]["match"][0]["match"]}\")";
		}		
		$function .= $filter[2]["match"] . $filter[3]["match"];
		return $function;
	}
	
	protected function function_substring($filter) {
		$function = "substring(";
		if($filter[1]["match"][0]["token"] != T_RESOURCE) {
			$function .= $this->call_function($filter[1]["match"]);
		} else {
			$function .= "\"{$filter[1]["match"][0]["match"]}\"";
		}		
		$function .= " from {$filter[1]["match"][1]["match"][0]["match"]}";
		$function .= (isset($filter[1]["match"][1]["match"][1])) ? " for {$filter[1]["match"][1]["match"][1]["match"]})" : ")";
		return $function;	
	}


	protected function function_endswith() {
	
	}
	
	protected function function_indexof() {
	
	}

	protected function function_replace() {
	
	}

		
	protected function function_tolower() {
	
	}
	
	protected function function_trim() {
	
	}
	
	protected function function_concat() {
	
	}
	
	protected function function_day() {
	
	}
	
	protected function function_hour() {
	
	}
	
	protected function function_minute() {
	
	}
	
	protected function function_month() {
	
	}
	
	protected function function_second() {
	
	}
	
	protected function function_year() {
	
	}
	
	protected function function_round() {
	
	}
	
	protected function function_floor() {
	
	}
	
	protected function function_ceiling() {
	
	}

}
