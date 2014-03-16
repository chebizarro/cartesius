<?php

namespace WebApi;

interface MetaDataInterface {
	
	public function getResource($resource);

	public function resourceExists($resource);
	
	public function getServiceName();

}

class MetaData implements MetaDataInterface, \JsonSerializable {

	private $entity_map;
	private $service;

	public $dataServices;	
	public $structuralTypes;
	public $resourceEntityTypeMap;
	
	public function __construct(&$service) {
		$this->service = $service;
		$this->construct_data_services();
		$this->construct_structural_types();
		$this->construct_entity_map();
	}
	
	private function construct_data_services() {
		$this->dataServices[] = array(
			"serviceName" => "/{$this->service->getEndpoint()}/{$this->service->getName()}/",
			"hasServerMetadata" => true,
			"jsonResultsAdapter" => "webApi_default",
			"useJsonp" => false );
	}

	private function construct_structural_types() {		
		$resources = $this->service->loadResources();
		
		if($resources) {
			foreach($resources as $key => $value) {
				$structure = new StructuralType($this->service, $value['resource']);
				$this->structuralTypes[] = $structure;
				$this->entity_map[$structure->defaultResourceName] = $structure;
			}
		}
	}
		
	function jsonSerialize() {
        return $this;
    }

	private function construct_entity_map() {
		$this->resourceEntityTypeMap = [];
		
		foreach ($this->entity_map as $entity_name => $entity) {
			$this->resourceEntityTypeMap[$entity_name] = "{$entity->shortName}:#{$entity->namespace}"; 
		} 
	} 

	public function getResource($resource) {
		return $this->entity_map[$resource];
	}

	public function resourceExists($resource) {
		return (isset($this->entity_map[$resource]))? true : false;
	}
	
	public function parseNc($resource) {
		return $this->service->parseNc($resource);	
	}

	public function getServiceName() {
		return $this->service->getName();
	}

}


class StructuralType {
	
	private $resource;
	private $primary_key;
	private $service;
	
	public $shortName;
	public $namespace;
	public $autoGeneratedKeyType;
	public $defaultResourceName;
	public $dataProperties;

	function __construct($service, $resource) {
		$this->resource = $resource;
		$this->service = $service;
		$dbnamespace = $this->service->parseNc($this->resource);
		$this->namespace = "WebApi.ORM.{$dbnamespace}";
		$this->shortName = $this->service->parseNc($this->resource);
		
		$this->defaultResourceName = $this->shortName;
		$this->autoGeneratedKeyType = "Identity";
		
		$this->construct_data_properties();
		$this->construct_navigation_properties();
	}
	
	
	private function construct_data_properties() {
		$properties = $this->service->getDataProperties($this->resource);
		$pkey = $this->service->getPrimaryKey($this->resource);
		
		$this->primary_key = ($pkey) ? $pkey[0]["name"] : null;
		
		if(sizeof($properties) > 0) {
		
			$this->dataProperties = [];
			
			foreach ($properties as $row) {
				$item = [];
				$item["name"] = $row["name"];
				if ($row["name"] == $this->primary_key) {
					$item["isPartOfKey"] = true;
				}
				$item["isNullable"] = ($row["is_nullable"] == "YES" ? true : false);
				if($row["default_value"] != null && $row["name"] != $pkey) {
					$item["defaultValue"] = $row["default_value"];
				}
				if ($row["max_length"] != null) {
					$item["maxLength"] = $row["max_length"];
				}
				$item["dataType"] = $this->service->matchType($row["data_type"]);
				array_push($this->dataProperties, $item);
			}			
		}
	}
	
	private function construct_navigation_properties() {
		$properties = $this->service->getNavigationProperties($this->resource);

		if(sizeof($properties) > 0) {
			
			$this->navigationProperties = [];

			foreach ($properties as $row) {
				$nav = [];
				
				if($row["resource"] == $this->resource) {
					$nav["name"] = $this->service->parseNc($row["foreign_resource"]);
					$nav["entityTypeName"] = $this->service->parseNc($row["foreign_resource"]).":#" . $this->namespace;
					$nav["nameOnServer"] = $row["foreign_resource"];
					$nav["isScalar"] = true;
					$nav["associationName"] = $row["association_name"];
					$nav["foreignKeyNames"] = [$row["property"]];
					
				} else {
					$nav["name"] = $this->service->parseNc($row["resource"]);
					$nav["entityTypeName"] = $this->service->parseNc($row["resource"]).":#" . $this->namespace;
					$nav["nameOnServer"] = $row["resource"];
					$nav["isScalar"] = false;						
					$nav["associationName"] = $row["association_name"];
					//$nav["invForeignKeyNames"] = [$row["property"]];
				}
				array_push($this->navigationProperties, $nav);
			}				
		}	
		
	}
		
	public function get_navigation_property($property) {
		if(isset($this->navigationProperties)) {
			foreach ($this->navigationProperties as $navproperty) {
				if ($navproperty["name"] == $property) {
					return $navproperty;
				}
			}
		}
		throw new \Exception("Error: Navigation Property: {$property} does not exist");
	}

	public function get_data_property($property) {
		foreach ($this->dataProperties as $dataproperty) {
			if ($dataproperty["name"] == $property) {
				return $dataproperty;
			}
		}
		throw new \Exception("Error: Data Property: {$property} does not exist");
	}
	
	public function data_property_exists($property) {
		foreach ($this->dataProperties as $navproperty) {
			if ($navproperty["name"] == $property) {
				return true;
			}
		}
		return false;	
	}

	public function navigation_property_exists($property) {
		foreach ($this->navigationProperties as $navproperty) {
			if ($navproperty["name"] == $property) {
				return true;
			}
		}
		return false;	
	}
	
	public function get_primary_key() {
		return $this->primary_key;
	}

	public function getName() {
		return $this->resource;
	}

	public function get_default_resource_name() {
		return $this->defaultResourceName;
	}

	public function get_namespace() {
		return $this->namespace;
	}

	public function get_entity_type_name() {
		return $this->defaultResourceName.":#".$this->namespace;
	}

}
