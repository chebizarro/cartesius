<?php

namespace WebApi;

use \PDO;

interface ServiceInterface {

	public function get_endpoint();

	public function get_name();
	
	public function parse_nc($string);

	public function get_resources();
	
	public function get_data_properties($resource);

	public function get_primary_key($resource);

	public function get_navigation_properties($resource);

	public function match_type($type);
	
	public function get_type();

}

abstract class Service implements ServiceInterface {
	
	protected $type; 
	protected $name;
	protected $endpoint;
	protected $nc;
	protected $authenticate;
	protected $exclude;

	
	public function __construct($config) {
		$this->type = isset($config['type']) ? $config['type'] : null;
		$this->metadata_path = isset($config['metadata_path']) ? $config['metadata_path'] : '/data/';
		$this->name = isset($config['name']) ? $config['name'] : null;
		$this->endpoint = isset($config['endpoint']) ? $config['endpoint'] : 'webapi';
		$this->nc = isset($config['nc']) ? $config['nc'] : NC_NATIVE;
		$this->authenticate = isset($config['authenticate']) ? $config['authenticate'] : null;
		$this->exclude = isset($config['exclude']) ? $config['exclude'] : null;
		
		$this->load_service();
	}

	public function get_endpoint() {
		return $this->endpoint;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_type() {
		return $this->type;
	}

	public function parse_nc($string) {
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
	
	abstract public function get_resources();
	
	abstract protected function load_service();
	
	abstract public function get_data_properties($struct_type);

	abstract public function get_primary_key($struct_type);

	abstract public function get_navigation_properties($struct_type);

	abstract public function match_type($type);
	
}


