<?php

namespace WebApi;

use \PDO;


interface WebApiResourceInterface {


}

abstract class WebApiResource implements WebApiResourceInterface {
	
	private $type; 
	private $name;
	private $endpoint;
	private $nc;
	private $authenticate;
	private $exclude;

	
	public function __construct($config) {
		$this->type = isset($config['type']) ? $config['type'] : null;
		$this->metadata_path = isset($config['metadata_path']) ? $config['metadata_path'] : '/data/';
		$this->name = isset($config['name']) ? $config['name'] : null;
		$this->endpoint = isset($config['endpoint']) ? $config['endpoint'] : 'webapi';
		$this->nc = isset($config['nc']) ? $config['nc'] : NC_NATIVE;
		$this->authenticate = isset($config['authenticate']) ? $config['authenticate'] : null;
		$this->exclude = isset($config['exclude']) ? $config['exclude'] : null;
		
		$this->load_resource();
	}

	public function get_endpoint() {
		return $this->endpoint;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_nc() {
		return $this->nc;
	}
	
	abstract public function get_structural_types();
	
	abstract protected function load_resource();
	
	abstract public function get_structural_types();

	abstract public function get_data_properties($struct_type);

	abstract public function get_primary_key($struct_type);

	abstract public function get_navigation_properties($struct_type);

	abstract public function match_type($type);


		
	abstract protected function filter();
	abstract protected function expand();
	abstract protected function select();
	abstract protected function orderby();
	abstract protected function top();
	abstract protected function skip();
	abstract public function parse();
	
	
	
}

class WebApiORMResource extends WebApiResource {
	
	private $username;
	private $password;
	private $host;
	private $port;
	
	public function __construct($config) {
		$this->username = isset($config['username']) ? $config['username'] : 'root';
		$this->password = isset($config['password']) ? $config['password'] : '';
		$this->host = isset($config['host']) ? $config['host'] : '127.0.0.1';
		$this->port = isset($config['port']) ? $config['port'] : null;
		parent::__construct($config);
	}

	protected function load_resource() {
		$connection_string = $this->build_connection_string();
		\ORM::configure('error_mode', PDO::ERRMODE_WARNING, $this->name);
		\ORM::configure($connection_string, null, $this->name);
		\ORM::configure('return_result_sets', true, $this->name);
		\ORM::configure('logging', true, $this->name);
		\ORM::configure('caching', true, $this->name);
	}
	
	protected function build_connection_string() {
		$this->port = isset($this->port) ? : 3306;
		$connection_string = "{$this->type}:host={$this->host};{$this->port};";
		$connection_string .= "dbname={$this->name};username={$this->username};password={$this->password}";
		return $connection_string;
	}

	public function get_structural_types();

	public function get_data_properties($struct_type);

	public function get_primary_key($struct_type);

	public function get_navigation_properties($struct_type);

	public function match_type($type);

}

class WebApiResourcePgsql extends WebApiORMResource {

	protected function build_connection_string() {
		$this->port = isset($this->port) ? : 5432;
		$connection_string = "{$this->type}:host={$this->host};{$this->port};";
		$connection_string .= "dbname={$this->name};user={$this->username};password={$this->password}";
		return $connection_string;
	}
	
	public function get_structural_types() {
		$sql = "SELECT table_name AS structural_type 
				FROM information_schema.tables
				WHERE table_type = 'BASE TABLE' 
				AND table_schema NOT IN ('pg_catalog', 'information_schema');";
		$struct_types = \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		return $struct_types;
	}

	public function get_data_properties($struct_type) {
		$sql = "SELECT column_name AS name,
				is_nullable,
				column_default AS default_value,
				character_maximum_length AS max_length,
				udt_name AS data_type 
				FROM information_schema.columns
				WHERE table_name = '{$struct_type}'
				ORDER BY ordinal_position";
				
		return \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_primary_key($struct_type) {
		$sql = "SELECT tc.constraint_name AS pkey, kcu.column_name AS name
				FROM information_schema.table_constraints tc
				LEFT JOIN information_schema.key_column_usage kcu
				ON tc.constraint_catalog = kcu.constraint_catalog
				AND tc.constraint_name = kcu.constraint_name
				WHERE tc.table_name = '{$struct_type}'
				AND tc.constraint_type = 'PRIMARY KEY'";	

		return \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
	}

	public function get_navigation_properties($struct_type) {
		$sql = "SELECT
				tc.table_name, kcu.column_name,
				ccu.table_name AS foreign_table_name,
				ccu.column_name AS foreign_column_name,
				tc.constraint_name
				FROM
				information_schema.table_constraints AS tc
				JOIN information_schema.key_column_usage
				AS kcu ON tc.constraint_name = kcu.constraint_name
				JOIN information_schema.constraint_column_usage 
				AS ccu ON ccu.constraint_name = tc.constraint_name
				WHERE constraint_type = 'FOREIGN KEY'
				AND (ccu.table_name = '{$struct_type}' OR tc.table_name = '{$struct_type}')";
				
		return \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function match_type($type) {
		switch ($type)
		{
			case "int": return "Byte";			
			case "int2": return "Int16";
			case "int4": return "Int32";
			case "int8": return "Int64";
			case "varchar":
			case "text":
			case "char":
			case "bpchar": return "String";
			case "bool": return "Boolean";
			case "date":
			case "timetz":
			case "timestamp": return "DateTime";
			case "timestamptz": return "DateTime";
			case "decimal":
			case "float4":
			case "float8": return "Decimal";
			case "bytea": return "Binary";
			case "null": return "Null";
			default: return $type;
		}
	}


}
