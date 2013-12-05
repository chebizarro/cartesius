<?php

define('ROOT', '/opt/lappstack-5.4.19-0/apps/cartesius.dur/htdocs/cartesius/');

define('APP', ROOT.'app/');
define('VENDOR', APP.'vendor/');
define('LIB', APP.'lib/');
define('DATA', APP.'data/');

require VENDOR.'autoload.php';
require LIB.'WebApi/WebApi.php';

class StructuralTypeTest extends PHPUnit_Framework_TestCase
{
	protected $structuralType;

	protected function setUp() {
		$mockservice = $this->getMockBuilder("WebApi\\PgsqlService")->disableOriginalConstructor()->getMock();

		$mockpropertiesdata = array();
		$mockpropertiesdata[] = array("name" => "id", "is_nullable" => false, "default_value" => 0, "max_length" => 8, "data_type" => "int8"); 
		$mockpropertiesdata[] = array("name" => "name", "is_nullable" => false, "default_value" => null,"max_length" => 32, "data_type" => "varchar"); 
		$mockpropertiesdata[] = array("name" => "description", "is_nullable" => true, "default_value" => null, "max_length" => 256, "data_type" => "varchar"); 
		$mockpropertiesdata[] = array("name" => "related", "is_nullable" => false, "default_value" => null, "max_length" => 32, "data_type" => "int8"); 
		
		$mockservice->expects($this->exactly(1))->method('get_data_properties')->with($this->stringContains('Test'))->will($this->returnValue($mockpropertiesdata));

		$mockpkeydata = array();
		$mockpkeydata[] = array("pkey"=>"PRIMARY KEY", "name"=>"id");
		$mockservice->expects($this->exactly(1))->method('get_primary_key')->with($this->stringContains('Test'))->will($this->returnValue($mockpkeydata));

		$mockfkeydata1 = array();
		$mockfkeydata1[] = array("resource" => "testOne", "property" => "related", "foreign_resource" => "testTwo", "foreign_property" => "id", "association_name" => "fkey_test_two_id");

		$mockfkeydata2 = array();
		$mockfkeydata2[] = array("resource" => "testTwo", "property" => "id", "foreign_resource" => "testOne", "foreign_property" => "related", "association_name" => "fkey_test_two_id");

		$mockservice->expects($this->exactly(1))->method('get_navigation_properties')->with($this->stringContains('Test'))->will($this->onConsecutiveCalls($mockfkeydata1, $mockfkeydata2));

        $map = array(
          array('String', 'varchar'),
          array('Int64', 'int8')
        );

		$mockservice->expects($this->any())->method('match_type')->will($this->returnValueMap($map));

		$mockservice->expects($this->any())->method('get_type')->will($this->returnValue("pgsql"));

        $nc_map = array(
          array('testOne', 'TestOne'),
          array('testTwo', 'TestTwo')
        );

		$mockservice->expects($this->any())->method('parse_nc')->will($this->returnValueMap($nc_map));
		
		$this->structuralType = new WebApi\StructuralType($mockservice, "testOne");
	}

	public function testPrimaryKey() {
		$expected = "id";
		$actual = $this->structuralType->get_primary_key();
		$this->assertEquals($expected, $actual);
	}

	public function testNavigationPropertyExists() {
		$expected = true;
		$actual = $this->structuralType->navigation_property_exists("TestTwo");
		$this->assertEquals($expected, $actual);
	}

	public function testGetNavigationProperty() {
		$expected = "array";
		$actual = $this->structuralType->get_navigation_property("TestTwo");
		$this->assertInternalType($expected, $actual);
	}

	public function testDataPropertyExists() {
		$expected = true;
		$actual = $this->structuralType->data_property_exists("name");
		$this->assertEquals($expected, $actual);
	}

	public function testGetDataProperty() {
		$expected = "array";
		$actual = $this->structuralType->get_data_property("name");
		$this->assertInternalType($expected, $actual);
	}

	
}
