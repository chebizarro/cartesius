<?php

namespace WebApi;

use \PDO;

class Dispatcher {
	protected static $services = [];
	protected static $metadata = [];
	protected static $metadata_path;
	
	public static function configure($config) {
		self::$metadata_path = $config["metadata_path"];
		foreach($config["services"] as $service) {
			$service_name = $service["name"];
			self::$services[$service_name] = self::service_factory($service);
			self::load_metadata($service_name);
		}
	}
	
	protected static function service_factory($service) {
		$serviceclass = "WebApi\\".ucfirst($service["type"]) . "Service";
		return new $serviceclass($service);
	}

	protected static function parser_factory($resource, $query, $service) {
		$parserclass = "WebApi\\".ucfirst(self::$services[$service]->get_type()) . "QueryParser";
		return new $parserclass($resource, $query, self::$services[$service], self::$metadata[$service]);
	}

	protected static function load_metadata($service) {
		$metadata_file = self::$metadata_path."{$service}.metadata.serial";
		if (!file_exists($metadata_file)) {
			self::$metadata[$service] = new MetaData(self::$services[$service]);
			file_put_contents($metadata_file, serialize(self::$metadata[$service]));
		} else {
			self::$metadata[$service] = unserialize(file_get_contents($metadata_file));
		}
	}

	public static function show_metadata($service, $format = "application/json") {		
		if($format == "application/json") {
			return json_encode(self::$metadata[$service], JSON_PRETTY_PRINT);
		} else {
			//other formats? XML?
		}
	}

	public static function query($service, $resource, $query) {
		$queryparser = self::parser_factory($resource, $query, $service);
		
		$queryparser->parse();
		$queryparser->execute();
		
		//return Serialiser::serialise($queryparser);
		//var_dump($queryparser);
		
		//echo json_encode(self::$metadata[$service], JSON_PRETTY_PRINT);
		//var_dump(self::$metadata[$service]);
	}
	
	
	// Below here is be removed to other classes

	/*
	 * Expand functions wich expand navigation properties
	 */

	private static function expand($expand, $data) {
		$stack = [];
		$to_expand = array();

		$expand = str_getcsv($expand);
				
		foreach($expand as $expander) {
			$to_expand = array_merge($to_expand, explode("/", $expander));
		}

		array_walk($to_expand, function(&$value, $key) { 
			$value = self::_class_name_to_table_name($value);
		}); 

		
		$result = self::recurse_expand($data, array_reverse($to_expand), $stack);
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
