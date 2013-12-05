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
		if(isset($this->filter)) { print_r($this->filter);}

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
			case T_NOT: $this->filter_not($filter); break;
			case T_FUNCTION: $this->filter_function($filter); break;
			default: throw new \Exception("Query error: Filter type {$filter[0]["match"]} does not exist");

		}
	}

	protected function filter_property($filter) {
		$property = $filter[0]["match"];
		if($this->entities->data_property_exists($property)) {
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
					$this->_filter_or($left, $right);
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
			case T_FUNCTION :
				$this->filter($left);
				$this->filter($right);
				break;
		}
	}

	protected function filter_not($filter) {
		$property = $filter[1]["match"][0]["match"];
		$condition = $filter[1]["match"][1]["match"];		
		$value = $filter[1]["match"][2]["match"];
		$this->_filter_not($property, $condition, $value);
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
						$property = $expand["match"];
						if($expandedEntityCollection->navigation_property_exists($property)) {
							$navProperty = $expandedEntityCollection->get_navigation_property($property);
							$this->join($navProperty);
							$expandedEntityCollection = $this->metadata->get_resource($property);							
						} elseif ($expandedEntityCollection->data_property_exists($property)) {
							$navProperty = $expandedEntityCollection->get_data_property($property);
							$property = "{$expandedEntityCollection->defaultResourceName}.{$property}";
							$this->response = $this->_orderby($property, $expand["token"]); 
						} else {
							throw new \Exception("Query Orderby error: Property: {$property} does not exist");
						}
					}
					break;
				case T_ORDERBY:
				case T_ORDERBYDESC:
					$property = $orderby["match"];
					if($entityCollection->data_property_exists($property)) {
						$this->response = $this->_orderby("{$this->resource}.{$property}", $token);
					} else {
						throw new \Exception("Query Orderby error: Property: {$property} does not exist");
					}
					break;
				default: throw new \Exception("Query error: Invalid Orderby clause");
			}
		}
	}


	protected function join($resource) {
		if(!isset($this->joins[$resource["name"]])) {
			if ($this->entities->navigation_property_exists($resource["name"])) {
				$nav_property = $this->entities->get_navigation_property($resource["name"]);
				$this->_join($resource, $nav_property);
				$this->joins[] = $resource["name"];
			} else {
				throw new \Exception("Query error: Property: {$resource["name"]} does not exist");
			}
		}
	}

	/* Expand */

	protected function expand() {

	}


	/*
	 * Functions
	 */

	protected function filter_function($filter) {
		$function = $this->call_function($filter);
		//$this->response = $this->response->raw_query($function);
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
		$token = $filter[1]["match"][0]["match"][1]["token"];
		/*switch($token) {
			case T_EXPAND:
				break;
			case T_RESOURCE:
				$property = $filter[1]["match"][0]["match"][1]["match"];
				break;			
			case T_FUNCTION:
				$property = $this->call_function($filter[1]["match"][0]["match"][1]["match"]);
				break;
		}*/
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
				$entityCollection = $this->entities;
				$expandedEntityCollection = $entityCollection;
					foreach($filter[1]["match"][0]["match"] as $expand) {
						$property = $expand["match"];
						if($expandedEntityCollection->navigation_property_exists($property)) {
							$navProperty = $expandedEntityCollection->get_navigation_property($property);
							$this->join($navProperty);
							$expandedEntityCollection = $this->metadata->get_resource($property);							
						} elseif ($expandedEntityCollection->data_property_exists($property)) {
							$navProperty = $expandedEntityCollection->get_data_property($property);
							$property = "{$expandedEntityCollection->defaultResourceName}.{$property}";
						} else {
							throw new \Exception("Query Orderby error: Property: {$property} does not exist");
						}
					}

				break;
			case T_RESOURCE:
				$property = $filter[1]["match"][0]["match"];
				break;			
			case T_FUNCTION:
				$property = $this->call_function($filter[1]["match"][0]["match"]);
				break;
		}
		
		$property = $filter[1]["match"][0]["match"];
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
			$function .= '"'.$filter[1]["match"][0]["match"].")";
		}		
		$function .= $filter[2]["match"] . $filter[3]["match"];
		return $function;
	}

	protected function function_length($filter) {
		$function = "length(";
		if($filter[1]["match"][0]["token"] != T_RESOURCE) {
			$function .= $this->call_function($filter[1]["match"]).")";
		} else {
			$function .= '"'.$filter[1]["match"][0]["match"].")";
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
	
	protected function function_substring() {
	
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
			case T_ORDERBY: return $this->response->order_by_asc($column);
			case T_ORDERBYDESC: return $this->response->order_by_desc($column);
			default: throw new \Exception('Query error: Invalid Orderby clause');
		}
	}

	protected function _join($property, $nav_property) {
		$this->response = $this->response->join(
			$this->$property['name'],
			array("{$this->resouce}.{$navProperty['foreignKeyNames'][0]}",
			"=",
			"{$property['name']}.{$property['invForeignKeyNames'][0]}")
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
		$this->response = $this->response->find_many()->as_array();
	}


}


