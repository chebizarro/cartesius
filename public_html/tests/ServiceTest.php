<?php

define('ROOT', '/opt/lappstack-5.4.19-0/apps/cartesius.dur/htdocs/cartesius/');

define('APP', ROOT.'app/');
define('VENDOR', APP.'vendor/');
define('LIB', APP.'lib/');
define('DATA', APP.'data/');

require VENDOR.'autoload.php';
require LIB.'WebApi/WebApi.php';


class ServiceTest extends PHPUnit_Framework_TestCase
{
	protected $service;
	
	protected $config = array(
			'type'=>'pgsql',
			'host' => '127.0.0.1',
			'port' => 5432,
			'name' => 'cartesius',
			'username' => 'postgres',
			'password' => 'postgres',
			'endpoint' => 'webapi',
			'nc' => NC_PASCAL,
			'authenticate' => null,
			'exclude' => array(
				'account' => array('token')
			)
		);

	protected function setUp() {
		$serviceclass = "WebApi\\".ucfirst($this->config["type"]) . "Service";
		$this->service = new $serviceclass($this->config);
	}
	
	public function testEndPoint() {
		$expected = $this->config["endpoint"];
		$actual = $this->service->get_endpoint();
		$this->assertEquals($expected, $actual);
	}

	public function testName() {
		$expected = $this->config["name"];
		$actual = $this->service->get_name();
		$this->assertEquals($expected, $actual);
	}

	public function testResources() {
		$expected = "array";
		$actual = $this->service->get_resources();
		$this->assertInternalType($expected, $actual);
	}
	
	public function testDataProperties() {
		$expected = "array";
		$actual = $this->service->get_data_properties("Account");
		$this->assertInternalType($expected, $actual);
	}

	public function testPrimaryKey() {
		$expected = "array";
		$actual = $this->service->get_primary_key("Account");
		$this->assertInternalType($expected, $actual);
	}

	public function testNavigationProperties() {
		$expected = "array";
		$actual = $this->service->get_navigation_properties("Account");
		$this->assertInternalType($expected, $actual);
	}

	public function testMatchType() {
		$expected = "String";
		$actual = $this->service->match_type("varchar");
		$this->assertEquals($expected, $actual);
	}

	public function testGetType() {
		$expected = "pgsql";
		$actual = $this->service->get_type();
		$this->assertEquals($expected, $actual);
	}

	public function testParseNC() {
		$expected = "PascalCase";
		$actual = $this->service->parse_nc("pascal_case");
		$this->assertEquals($expected, $actual);
	}

}
