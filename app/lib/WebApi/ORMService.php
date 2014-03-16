<?php

namespace WebApi;

use \PDO;

class ORMService extends Service
{
	protected $driver;
	protected $username;
	protected $password;
	protected $host;
	protected $port;
	
	
	public function __construct($config)
	{
		$this->driver = isset($config['resource']['driver']) ? $config['resource']['driver'] : 'mysql';
		$this->username = isset($config['resource']['username']) ? $config['resource']['username'] : 'root';
		$this->password = isset($config['resource']['password']) ? $config['resource']['password'] : '';
		$this->host = isset($config['resource']['host']) ? $config['resource']['host'] : '127.0.0.1';
		$this->port = isset($config['resource']['port']) ? $config['resource']['port'] : 3306;
		parent::__construct($config);
	}

	protected function loadService()
	{
		$connection_string = $this->buildConnectionString();
		\ORM::configure('error_mode', PDO::ERRMODE_WARNING, $this->name);
		\ORM::configure($connection_string, null, $this->name);
		\ORM::configure('return_result_sets', true, $this->name);
		\ORM::configure('logging', true, $this->name);
		\ORM::configure('caching', true, $this->name);
		
		$tables = $this->loadResources();
		
		$primary_keys = [];
		
		foreach($tables as $table)
		{
			$tablename = $table["resource"];
			$pkey = $this->getPrimaryKey($tablename);
			if($pkey)
			{
				$primary_keys[$tablename] = $pkey[0]["name"];	
			}
		}
		
		\ORM::configure('id_column_overrides', $primary_keys, $this->name);
	}
	
	protected function buildConnectionString()
	{
		$user = ($this->driver == "pgsql") ? "user" : "username";
		$connection_string = "{$this->driver}:host={$this->host};port={$this->port};";
		$connection_string .= "dbname={$this->name};{$user}={$this->username};password={$this->password}";
		return $connection_string;
	}

	public function loadResources()
	{
		$sql = "SELECT table_name AS resource 
				FROM information_schema.tables
				WHERE table_type = 'BASE TABLE' 
				AND table_schema NOT IN ('pg_catalog', 'information_schema');";
		$resource = \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		return $resource;
	}

	public function getDataProperties($resource)
	{
		$sql = "SELECT column_name AS name,
				is_nullable,
				column_default AS default_value,
				character_maximum_length AS max_length,
				udt_name AS data_type 
				FROM information_schema.columns
				WHERE table_name = '{$resource}'
				ORDER BY ordinal_position";
				
		return \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getPrimaryKey($resource)
	{
		$sql = "SELECT tc.constraint_name AS pkey, kcu.column_name AS name
				FROM information_schema.table_constraints tc
				LEFT JOIN information_schema.key_column_usage kcu
				ON tc.constraint_catalog = kcu.constraint_catalog
				AND tc.constraint_name = kcu.constraint_name
				WHERE tc.table_name = '{$resource}'
				AND tc.constraint_type = 'PRIMARY KEY'";	

		return \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getNavigationProperties($resource)
	{
		$sql = "SELECT
				tc.table_name AS resource,
				kcu.column_name AS property,
				ccu.table_name AS foreign_resource,
				ccu.column_name AS foreign_property,
				tc.constraint_name AS association_name
				FROM
				information_schema.table_constraints AS tc
				JOIN information_schema.key_column_usage
				AS kcu ON tc.constraint_name = kcu.constraint_name
				JOIN information_schema.constraint_column_usage 
				AS ccu ON ccu.constraint_name = tc.constraint_name
				WHERE constraint_type = 'FOREIGN KEY'
				AND (ccu.table_name = '{$resource}' OR tc.table_name = '{$resource}')";
				
		return \ORM::get_db($this->name)->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function matchType($type)
	{
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
	
	public function getResource($resourceName) {
		try
		{
			return new ORMResource($resourceName, $this->metadata);
		}
		catch (\Exception $e)
		{
			echo $e;
		}
	}

}
