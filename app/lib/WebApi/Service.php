<?php

namespace WebApi;

use \PDO;

interface ServiceInterface {

	public function getName();
	
	public function getMetaData();
	
	public function getResource($resourceName);

	public function getType();
	
	public function getEndpoint();
	
	public function setMetaData($metadata);

	////
	
	public function parseNc($string);

	public function loadResources();
	
	public function getDataProperties($resource);

	public function getPrimaryKey($resource);

	public function getNavigationProperties($resource);

	public function matchType($type);
	

}

abstract class Service implements ServiceInterface {
	
	protected $type;
	protected $name;
	protected $endpoint;
	
	///
	protected $nc;
	protected $authenticate;
	protected $exclude;

	
	public function __construct($config) {
		$this->type = isset($config['type']) ? $config['type'] : null;
		$this->name = isset($config['name']) ? $config['name'] : null;
		$this->endpoint = isset($config['endpoint']) ? $config['endpoint'] : 'webapi';
		$this->nc = isset($config['nc']) ? $config['nc'] : NC_NATIVE;
		
		//$this->authenticate = isset($config['authenticate']) ? $config['authenticate'] : null;
		//$this->exclude = isset($config['exclude']) ? $config['exclude'] : null;
		//$this->metadata_path = isset($config['metadata_path']) ? $config['metadata_path'] : '/data/';
		
		$this->loadService();
		$this->loadMetadata($config['metadata']);

	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return get_class($this);
	}
	
	private function loadMetaData($config) {
		$metadata_path = (isset($config["path"])) ? $config["path"] : "/data/";
		$metadata_type = (isset($config["type"])) ? $config["type"] : "auto";
		$metadata_file = "{$metadata_path}{$this->name}.metadata.serial";
		if (!file_exists($metadata_file)) {
			if($config["type"] == "auto") {
				$this->metadata = new MetaData($this);
				file_put_contents($metadata_file, serialize($this->metadata));
			}
		} else {
			$this->metadata = unserialize(file_get_contents($metadata_file));
		}
	}

	public function getMetaData() {
		return $this->metadata;
	}
	
	public function getResource($resourceName) {
		
	}

	public function getEndpoint() {
		return $this->endpoint;
	}

	public function setMetaData($metadata) {
		$this->metadata = $metadata;
	}


	public function parseNc($string) {
		switch ($this->nc) {
			case NC_PASCAL: return preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$string);
			case NC_CAMEL: return lcfirst(preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$string));
			case NC_LOWERCASE:
				return strtolower(preg_replace(
					array('/\\\\/', '/(?<=[a-z])([A-Z])/', '/__/'),
					array('_', '_$1', '_'),
					ltrim($string, '\\')
				));
			case NC_NATIVE:
			default: return $string;
		}
	}
	
	abstract public function loadResources();
	
	abstract protected function loadService();
	
	abstract public function getDataProperties($struct_type);

	abstract public function getPrimaryKey($struct_type);

	abstract public function getNavigationProperties($struct_type);

	abstract public function matchType($type);
	
}
