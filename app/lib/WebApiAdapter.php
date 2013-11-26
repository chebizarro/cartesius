<?php

namespace WebApi;

use \PDO;

class ODataQueryProcessor {
	
	
}

class WebApiAdapter {

	protected static $connections;
	protected static $modelpath;
	protected static $metadatapath;
	protected static $endpoint;
	protected static $metadata;
	
	// counter for the number of times a table is joined
	protected static $joincount = 1;
	
	public static function configure($config) {
		self::$connections = isset($config['connections']) ? $config['connections'] : null;
		self::$modelpath = isset($config['modelpath']) ? $config['modelpath'] : '/models/';
		self::$metadatapath = isset($config['metadatapath']) ? $config['metadatapath'] : '/data/';
		self::$endpoint = isset($config['endpoint']) ? $config['endpoint'] : 'webapi';
		self::$metadata = [];

		foreach(self::$connections as $key => $value) {
			ORM\ORM::configure('error_mode', PDO::ERRMODE_WARNING, $key);
			ORM\ORM::configure($value, null, $key);
			ORM\ORM::configure('return_result_sets', true, $key);
			ORM\ORM::configure('logging', true, $key);
			ORM\ORM::configure('caching', true, $key);
			
			self::load_metadata($key);
			self::load_models($key);
		}		
	}

	/*
	 * Metadata functions
	 */

	protected static function load_metadata($connection) {
		$metadatafile = self::$metadatapath."{$connection}.metadata.serial";
		if (!file_exists($metadatafile)) {
			self::$metadata[$connection] = new WebApiMetaData($connection, self::$endpoint);
			file_put_contents($metadatafile, serialize(self::$metadata[$connection]));
		} else {
			self::$metadata[$connection] = unserialize(file_get_contents($metadatafile));
		}
	}

	public static function show_metadata($connection, $format = "JSON") {		
		if($format == "JSON") {
			return json_encode(self::$metadata[$connection], JSON_PRETTY_PRINT);
		} else {
			//other formats? XML?
		}
	}

	/*
	 * Load the models - to be replaced with class autoloader or discarded altogether
	 */

	protected static function load_models($connection) {
		$dir = self::$modelpath.$connection.'/';
		
		foreach(self::$metadata[$connection]->resourceEntityTypeMap as $model_name => $model_value) {
			$file_name = "{$dir}{$model_name}.php";
			if(!file_exists($file_name)) {
				if(!is_dir($dir)) {
					mkdir($dir);
				}
				file_put_contents($file_name, self::$metadata[$connection]->get_model($model_name));
			}
			require_once($file_name);
		}	
	}
	
	/*
	 * Utility functions for converting class and table names
	 */
	
	protected static function _base_class_name($class_name) {
		return end(explode("\\", $class_name));
	} 
	
	protected static function _class_name_to_table_name($class_name) {
		$class_name = end(explode("\\", $class_name));
		return strtolower(preg_replace(
			array('/\\\\/', '/(?<=[a-z])([A-Z])/', '/__/'),
			array('_', '_$1', '_'),
			ltrim($class_name, '\\')
		));
	}
	
	protected static function _table_name_to_class_name($table_name) {
		return preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$table_name);
	}

	/*
	 * Utility functions for metadata
	 */

	protected static function check_type($column, $struct_type) {
		$type = $struct_type->get_data_property($column);
		return ($type) ? $type["dataType"] : null;
	} 

	private static function _get_structural_type($data) {
		$base_model = self::_base_class_name($data->get_class_name());
		$metadata = self::$metadata[$data->get_connection()];
		return $metadata->get_structural_type($base_model);
	}

	/*
	 * Functions for retrieving data
	 */


	public static function get_data($connection, $model, $vars) {
		// functions can be called within the request URL path or query inside the $filter or $orderby parameter
		
		//$value - the raw value of a primitive type's property
		
		// Resource(x)? 
		
		//$filter
		//$expand
		//$select
		//$orderby
		//$top
		//$skip
		//$inlinecount
		//$format
				
		$model_name = __NAMESPACE__."\ORM\\".self::_table_name_to_class_name($connection)."\\".self::_table_name_to_class_name($model);

		$data = ORM\Model::factory($model_name, $connection);

		$data = $data->table_alias('p1');

		$data = isset($vars['$select']) ? self::select($vars['$select'], $data) : self::select_all($data);
		
		if(isset($vars['$filter'])) {
			$filter = $vars['$filter'];
			try {
				$query = QueryLexer::run($filter);
				$data = self::filter($query, $data);
			} catch (\Exception $e) {
				echo $e;
			}
		}

		$data = isset($vars['$top']) ? self::top($vars['$top'], $data) : $data;
		$data = isset($vars['$skip']) ? self::skip($vars['$skip'], $data) : $data;
		$data = isset($vars['$orderby']) ? self::orderby($vars['$orderby'], $data) : $data;
		
		$data = $data->find_many();
		
		//print_r($vars);
		//echo ORM\ORM::get_last_query() . "\n";
		
		$data = isset($vars['$expand']) ? json_encode(self::expand($vars['$expand'], $data), JSON_PRETTY_PRINT) : $data->as_json();
		
		return $data;
	}

	/*
	 * Select fields or navigation properties
	 */
	
	protected static function select($select, $data) {
		$is_key = 0;
		$struct_type = self::_get_structural_type($data);			
		$columns = str_getcsv($select);
		$pkey = $struct_type->get_primary_key();
		
		foreach($columns as $column_name) {
			$column_name = self::filter_join($column_name, $data);

			// This is db implementation specific
			if (self::check_type($column_name, $struct_type) == "Binary") {
				$data = $data->select_expr("encode(\"{$column_name}\", 'base64')", $column_name);
			} else {
				$data = $data->select($column_name);
			}
			
			$is_key = ($column_name == $pkey) ? 1 : 0;
		}		
		$data = ($is_key == 0) ? $data->select($pkey) : $data;
		return $data;
	}

	private static function select_all($data) {
		$struct_type = self::_get_structural_type($data);
		$columns = $struct_type->get_data_properties();
		
		//$data = $data->select("p1.*");
		
		// This is db implementation specific
		foreach($columns as $column) {
			if (self::check_type($column["name"], $struct_type) == "Binary") {
				$data = $data->select_expr("encode(\"{$column["name"]}\", 'base64')", $column["name"]);
			}
		}
		return $data;
	} 

	/*
	 * Filter router
	 */
	
	private static function filter($query, $data) {
		$token = $query[0]["token"];
		switch ($token)
		{
			case T_RESOURCE: return self::filter_column($query, $data);
			case T_BLOCK: return self::filter_block($query, $data);
			case T_LENGTH: return self::filter_length($query, $data);
			case T_SUBSTRING_OF: return self::filter_substring_of($query, $data);
			case T_STARTS_WITH: return self::filter_starts_with($query, $data);
			case T_NOT: return self::filter_not($query, $data);
			//case T_TO_UPPER: return self::filter_function(T_TO_UPPER,$query, $data);
			//case T_SUBSTRING: return self::filter_function(T_SUBSTRING,$query, $data);
			default: return $data;	
		}
	}

	/*
	 * Filter functions depending on the tokens returned by the parser
	 */

	private static function filter_block($query, $data) {
		$token = $query[0]["match"][0]["token"];
	
		$operator = $query[1]["token"];
		$left = $query[0]["match"];
		$right = $query[2]["match"];
		switch ($token) {
			case T_RESOURCE :
				if($operator == T_AND) {
					$data = self::filter($left, $data);
					$data = self::filter($right, $data);
				} elseif ($operator == T_OR) {
					$data = $data->where_raw(
						'("'.$left[0]['match'].'"'.$left[1]['match']."'".$left[2]['match']."'".
						" OR ".
						'"'.$right[0]['match'].'"'.$right[1]['match']."'".$right[2]['match']."')"
					);
				}
				return $data;
			case T_BLOCK :
				$operator = $query[1]["token"];
				if(!is_array($operator)) {
					if($operator == T_AND || $operator == T_OR) {
						$data = self::filter($left, $data);
						$data = self::filter($right, $data);
					}
				} else {
					
				}
				return $data;
			case T_STARTS_WITH :
				if($operator == T_AND) {
					$data = self::filter($left, $data);
					$data = self::filter($right, $data);
					return $data;
				}
		}
		return $data;
	}

	private static function filter_column($query, $data) {
		$column_name = $query[0]["match"];
		$condition = $query[1]["token"];
		$value = $query[2]["match"];
		switch ($condition)
		{
			case T_GT: return $data->where_gt($column_name, $value);
			case T_LT: return $data->where_lt($column_name, $value);
			case T_EQ: return $data->where_equal($column_name, $value);
			case T_GE: return $data->where_gte($column_name, $value);
			case T_LE: return $data->where_lte($column_name, $value);
			case T_NE: return $data->where_not_equal($column_name, $value);
			default : return $data;
		}
	}

	private static function filter_not($query, $data) {
		$column_name = '"'.$query[1]["match"][0]["match"].'"';
		$condition = $query[1]["match"][1]["match"];		
		$value = "'".$query[1]["match"][2]["match"]."'";
		return $data->where_raw('NOT ('.$column_name.$condition.$value.')');
	}

	/*
	 * Joins additional tables if they are expanded properties
	 */

	private static function filter_join($column, &$data) {
		$strpos = (strpos($column, ".") > 0) ? strpos($column, ".") : strpos($column, "/");

		$struct_type = self::_get_structural_type($data);

		if($strpos>0) {
			$jointable = self::_class_name_to_table_name(substr($column, 0, $strpos));
			$column = substr($column, $strpos+1);
		} else {
			$jointable = self::_class_name_to_table_name($column);
			$column = "*";
		}
		
		$nav_property = $struct_type->get_navigation_property($jointable);
			
		if($nav_property) {
			$p = "p" . ++self::$joincount;
			
			if($nav_property["isScalar"]) {
				$fcolumn = $nav_property["invForeignKeyNames"][0];
				$pkey = $struct_type->get_primary_key();
			} else {
				$fcolumn = $nav_property["foreignKeyNames"][0];
				$pkey = $nav_property["foreignKeyNames"][1];
			}
			$column = $p.".".$column;
			$data = $data->join($jointable , array("{$p}.{$fcolumn}", "=", "p1.{$pkey}"), $p);
		}
				
		return $column;
	}

	/*
	 * Database functions for use by the filters
	 */

	private static function filter_function($function, $query, $data) {
		switch ($function) {
			case T_TO_UPPER:
				$string = self::filter_to_upper($query);
				break;
		}
		return $data->where_raw($string);
	}

	private static function filter_substring_of($query, $data) {
		$column_name = $query[1]["match"][0]["match"][1]["match"];
		$value = '%' . $query[1]["match"][0]["match"][0]["match"] . '%';
		$condition = $query[3]["token"];		
		return ($condition == T_TRUE) ? $data->where_like($column_name, $value) : $data->where_not_like($column_name, $value);
	}

	private static function filter_starts_with($query, $data) {
		$column_name = self::filter_join($query[1]["match"][0]["match"], $data);		
		$value = $query[1]["match"][1]["match"][0]["match"] . '%';
		$condition = $query[3]["token"];		
		return ($condition == T_TRUE) ? $data->where_like($column_name, $value) : $data->where_not_like($column_name, $value);
	}

	private static function filter_to_upper($query) {
		$function = "upper(";
		if($query[1]["token"][0]["token"] != T_RESOURCE) {
			$function .= self::filter($query[1]["match"]).")";
		} else {
			$function .= '"'.$query[1]["match"][0]["match"].")";
		}		
		$function .= $query[2]["match"] . $query[3]["match"];
		return $function;
	}

	private static function filter_length($query, $data) {
		$column_name = $query[1]["match"][0]["match"];
		return $data->where_raw('length("'.$column_name.'")'.$query[2]["match"].trim($query[3]["match"]));
	}

	/*
	 * Functions for orderby
	 */

	private static function orderby($orderby, $data) {		
		if(strpos($orderby, ",")){
			$orderbyArray = explode(",", $orderby);
			foreach($orderbyArray as $order) {
				$data = self::orderby($order, $data);
			}
		} else {
			if(strpos($orderby, " ")) {
				$orderbyArray = explode(" ", $orderby);
				$orderbyArray[0] = self::filter_join($orderbyArray[0], $data);
				if($orderbyArray[1] == "desc") {
					$data = $data->order_by_desc($orderbyArray[0]);
				} else {
					$data = $data->order_by_asc($orderbyArray[0]);
				}
				return $data;
			} else {
				//$orderby = self::filter_join($orderby, $data);		
				$data = $data->order_by_asc($orderby);
			}
		}		
		return $data;
	}

	/* 
	 * Top and Skip functions (limit & offset)
	 */

	private static function top($top, $data) {
		return $data->limit(intval($top));
	}

	private static function skip($skip, $data) {
		return $data->offset(intval($skip));
	}

	/*
	 * Expand functions wich expand navigation properties
	 */

	private static function expand($expand, $data) {
		$stack = [];
		$expand = str_getcsv($expand);
		foreach($expand as $expander) {
			$strpos = (strpos($expander, ".") > 0) ? strpos($expander, ".") : strpos($expander, "/");
			if($strpos>0) {
				$to_expand[] = self::_class_name_to_table_name(substr($expander, 0, $strpos));
				$to_expand[] = self::_class_name_to_table_name(substr($expander, $strpos+1));
			} else {
				$to_expand[] = self::_class_name_to_table_name($expander);
			}
		}
		$result = self::recurse_expand($data, $to_expand, $stack);
		return $result;
	}
	
	private static function recurse_expand($object, $expand, &$stack) {
		$ref = 0;
		$result = [];
		foreach ($object as $row) {
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
				//$object_name = end(explode("\\", $object_name));
				//(in_array($object_name, $expand)) ? null : $expand[] = $object_name; 
				//print_r($expand);
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
	 * Save data functions
	 */

	public static function save_changes($connection, $data) {
		
	/*
	{
		"entities":[
			{
				"id":"K_-1",
				"title":"Test",
				"date":"",
				"review_date":"Thu Nov 21 2013 00:00:00 GMT+0700 (WIB)",
				"summary":null,
				"entityAspect":{
					"entityTypeName":"Project:#XMLPARIS.Model",
					"defaultResourceName":"Project",
					"entityState":"Added",
					"originalValuesMap":{},
					"autoGeneratedKey":{
						"propertyName":"id",
						"autoGeneratedKeyType":"Identity"
					}
				}
			}
		],
		"saveOptions":{}
	}
	*/

		foreach ($data->entities as $entity) {
			$aspect = $entity->entityAspect;
			if($aspect->entityState == "Added") {
				$model = ORM\Model::factory($aspect->defaultResourceName, self::$models[$aspect->defaultResourceName])->create();
				$keyVar = $aspect->autoGeneratedKey->propertyName;
				foreach($entity as $key => $value) {
					if (($key !== $keyVar) && ($key !== "entityAspect")) {
						if(strtotime($value) != null) {
							$value = strtotime($value);
						}
						$model->$key = $value;
					}
				}
				$model->save();
			}
		}
	}

}
