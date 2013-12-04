<?php

namespace WebApi;

use \PDO;

class ORMService extends Service {
	
	protected $username;
	protected $password;
	protected $host;
	protected $port;
	
	public function __construct($config) {
		$this->username = isset($config['username']) ? $config['username'] : 'root';
		$this->password = isset($config['password']) ? $config['password'] : '';
		$this->host = isset($config['host']) ? $config['host'] : '127.0.0.1';
		$this->port = isset($config['port']) ? $config['port'] : 3306;
		parent::__construct($config);
	}

	protected function load_service() {
		$connection_string = $this->build_connection_string();
		\ORM::configure('error_mode', PDO::ERRMODE_WARNING, $this->name);
		\ORM::configure($connection_string, null, $this->name);
		\ORM::configure('return_result_sets', true, $this->name);
		\ORM::configure('logging', true, $this->name);
		\ORM::configure('caching', true, $this->name);
	}
	
	protected function build_connection_string() {
		$connection_string = "{$this->type}:host={$this->host};port={$this->port};";
		$connection_string .= "dbname={$this->name};username={$this->username};password={$this->password}";
		return $connection_string;
	}

	public function get_resources(){}

	public function get_data_properties($struct_type){}

	public function get_primary_key($struct_type){}

	public function get_navigation_properties($struct_type){}

	public function match_type($type){}
		

}
