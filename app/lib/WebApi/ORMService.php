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
		
		$tables = $this->get_resources();
		
		$primary_keys = [];
		
		foreach($tables as $table) {
			$tablename = $table["resource"];
			$pkey = $this->get_primary_key($tablename);
			if($pkey) {
				$primary_keys[$tablename] = $pkey[0]["name"];	
			}
		}
		
		\ORM::configure('id_column_overrides', $primary_keys, $this->name);
	}
	
	protected function build_connection_string() {
		$connection_string = "{$this->type}:host={$this->host};port={$this->port};";
		$connection_string .= "dbname={$this->name};username={$this->username};password={$this->password}";
		return $connection_string;
	}

	public function get_resources(){}

	public function get_data_properties($resource){}

	public function get_primary_key($resource){}

	public function get_navigation_properties($resource){}

	public function match_type($type){}
		

}
