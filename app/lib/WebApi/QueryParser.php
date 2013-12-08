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
	protected $selected = [];

	public function __construct($resource, $query, $service, $metadata) {
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

		echo "\n" . \ORM::get_last_query() . "\n\n"; 
		echo $resource . "\n";
		print_r($query);

	}
	
	abstract protected function new_response();
	abstract protected function top();
	abstract protected function skip();

	abstract protected function _filter($property, $value, $condition);
	abstract protected function _orderby($property, $order);
	abstract protected function _join($property, $nav_property);
	abstract protected function _filter_not($property, $condition, $value);

	public function parse() {
		// These two will create joins if there are expanded properties
		(isset($this->filter)) ? $this->filter($this->filter) : null;
		(isset($this->orderby)) ? $this->orderby() : null;

	}

	public function execute() {
		
		
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

	protected function filter_property($filter) {
		$property = $filter[0]["match"];
		$condition = $filter[1]["token"];
		$value = $filter[2]["match"];
		if($this->entities->data_property_exists($property)) {
			$property = $this->resource.".".$property;
			$this->response = $this->_filter($property, $value, $condition);
		} else {
			throw new \Exception('Query error: Property does not exist');
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

	protected function join($nav_from, $nav_to) {
		$nav_to_property = $nav_from->get_navigation_property($nav_to->get_default_resource_name());
		$nav_from_property = $nav_to->get_navigation_property($nav_from->get_default_resource_name());
		$nav_from_name = $nav_from->get_name();
		$nav_to_name = $nav_to->get_name();

		if(!isset($this->joins[$nav_to_name])) {
		
			$nav_to_key = ($nav_from_property['isScalar']) ? $nav_from_property['foreignKeyNames'][0] : $nav_from_property['invForeignKeyNames'][0];
			$nav_from_key = ($nav_to_property['isScalar']) ? $nav_to_property['foreignKeyNames'][0] : $nav_to_property['invForeignKeyNames'][0];
		
			$this->response = $this->response->join(
				$nav_to_name,
				array("{$nav_to_name}.{$nav_from_key}",
				"=",
				"{$nav_from_name}.{$nav_to_key}")
			);
			$this->joins[$nav_to_name] = array($nav_from_property, $nav_to_property);
		}
	}
	
	/* Select */

	protected function select() {
		if (isset($this->select)) {
			foreach($this->select as $select) {
				$token = $select["token"];
				switch($token) {
					case T_EXPAND:
						if (isset($this->expand)) {
							array_push($this->expand, $select);
						} else {
							$this->expand[] = $select;
						}
						break;
					case T_RESOURCE:
						if($this->entities->data_property_exists($select["match"])) {
							$this->selected[] = "{$this->resource}.{$select['match']}";
						}
						break;
				}
			}
		}
		if(count($this->selected) == 0) {
			$this->response = $this->response->select("{$this->resource}.*");
		}
	}

	/* Expand */

	protected function expand() {
		$resources = [];
		if(isset($this->expand)) {

			foreach($this->expand as $expanded) {
				$token = $expanded["token"];

				switch($token) {
					case T_RESOURCE:
						$nav_from = $this->entities;
						$nav_to = $this->metadata->get_resource($expanded["match"]);
						$this->join($nav_from, $nav_to);
						$resources[$nav_to->get_default_resource_name()] = array("resource" => $nav_to);
						break;
					case T_EXPAND: 	
						$expandedEntities = $this->entities;
						foreach($expanded["match"] as $expand) {
							if($expandedEntities->navigation_property_exists($expand["match"])) {
								$nav_from = $expandedEntities;
								$nav_to = $this->metadata->get_resource($expand["match"]);
								$this->join($nav_from, $nav_to);
								$expandedEntities = $nav_to;
								$resources[$nav_to->get_default_resource_name()] = array("resource" => $nav_to);
							} else {
								$nav_property = $expandedEntities->get_data_property($expand["match"]);
								if(!isset($resources[$nav_to->get_default_resource_name()]["property"])) {
									$resources[$nav_to->get_default_resource_name()]["property"] = array($nav_property);
								} else {
									array_push($resources[$nav_to->get_default_resource_name()]["property"], $nav_property);
								}
							}
						}
						break;
				}
			}
		}		
		$response = $this->response;
		
		if(count($this->selected > 0)) {
			$idstring = "{$this->resource}.{$this->entities->get_primary_key()}";
			if(!in_array($idstring,$this->selected)) {
				$this->selected[] = $idstring;
			}
			foreach($this->selected as $select) {
				$response = $response->select($select);
			}
		} else {
			$response = $response->select("{$this->resource}.*");
		}
		
		$results[$this->resource] = array(
			"response" => $response->find_many()->as_array(),
			"resource" => $this->entities);
				
		foreach($resources as $resource) {
			$response = $this->response;
			$resource_name = $resource["resource"]->get_name();
			if(isset($resource["property"])) {
				$id = $resource["resource"]->get_primary_key();
				$idcount = 0;
				foreach($resource["property"] as $property) {
					$response = $response->select("{$resource_name}.{$property['name']}");
					$idcount = ($property['name'] == $id) ? 1 : 0;
				}
				if($idcount == 0) {
					$response = $response->select("{$resource_name}.{$id}");
				}
			} else {
				$response = $response->select("{$resource_name}.*");
			}
			$results[$resource_name] = array(
				"response" => $response->find_many()->as_array(),
				"resource" => $resource["resource"]);
		}
		print_r($results);
	}

	protected function expand_recursive($results, $base, $expand, &$stack) {
		$ref = 0;
		$result = [];
		foreach ($results[$base]["response"] as $row) {
			$row_array = $row->as_array();
			$cereal = serialize($row_array);
			foreach($stack as $key => $val) {
				if($val === $cereal) {
					$ref = $key+1;
					break;
				}
			}
			if($ref === 0) {
				$stack[] = $cereal;
				
				$object_name = get_class($row);
				$row_array = array_merge(array('$id'=>count($stack), '$type'=>str_replace("\\",".",$object_name)),$row_array);
				$object_name = self::_class_name_to_table_name($object_name);
				
				if(($key = array_search($object_name, $expand)) !== false) {
					unset($expand[$key]);
				}
				
				if($expand) {
					foreach($expand as $expander) {
						if(method_exists($row, $expander)) {
							$expanded = $row->{$expander}()->find_many();
							if($expanded->count() > 0) {
								$row_array[$expander] = self::recurse_expand($expanded, $expand, $stack);
							}
						}
					}
				}
				$result[] = $row_array;
			} else {
				$result[] = array("\$ref" => $ref);
			}
			$ref = 0;
		}
		return $result;

		
	}

	/*
	 * Functions
	 */

	protected function filter_function($filter) {
		$function = $this->call_function($filter);
		if($function) {
			$this->response = $this->response->where_raw($function);
		}
	}

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
		$this->response = ($condition == T_TRUE) ? $this->response->where_like($property, $value) : $this->response->where_not_like($property, $value);
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
							echo $property;
				break;			
			case T_FUNCTION:
				$property = $this->call_function($filter[1]["match"][0]["match"]);
				break;
			default: 
		}
		$value = $filter[1]["match"][1]["match"][0]["match"] . '%';
		$condition = $filter[3]["token"];
		$this->response = ($condition == T_TRUE) ? $this->response->where_like($property, $value) : $this->response->where_not_like($property, $value);
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

	protected function function_endswith() {
	
	}
	
	protected function function_indexof() {
	
	}

	protected function function_replace() {
	
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
	
	protected function _filter_not($property, $condition, $value) {
		$this->response = $this->response->where_raw("NOT (\"{$property}\" {$condition} '{$value}')");
	}
	
	protected function _filter_or($left, $right) {
		$this->response = $this->response->where_raw(
			"(\"{$left[0]['match']}\"{$left[1]['match']}'{$left[2]['match']}')".
			" OR ".
			"(\"{$right[0]['match']}\"{$right[1]['match']}'{$right[2]['match']}')"
		);
	}
	
	protected function _orderby($property, $order) {
		switch ($order)
		{
			case T_ORDERBY:
				$this->response = $this->response->order_by_asc($property);
				break;
			case T_ORDERBYDESC:
				$this->response = $this->response->order_by_desc($property);
				break;
			default: throw new \Exception('Query error: Invalid Orderby clause');
		}
	}

	protected function _join($property, $nav_property) {
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

		if(!isset($this->select) && !isset($this->expand)) {
			(!isset($this->top)) ?: $this->top();
			(!isset($this->skip)) ?: $this->skip();
			$this->response = $this->response->find_many()->as_array();
		} else {
			$this->select();
			$this->expand();
			//$this->response = $this->response->find_many()->as_array();
		}
		//print_r($this->response);
	}


}


