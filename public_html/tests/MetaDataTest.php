<?php

define('ROOT', '/opt/lappstack-5.4.19-0/apps/cartesius.dur/htdocs/cartesius/');

define('APP', ROOT.'app/');
define('VENDOR', APP.'vendor/');
define('LIB', APP.'lib/');
define('DATA', APP.'data/');

require VENDOR.'autoload.php';
require LIB.'WebApi/WebApi.php';


class MetaDataTest extends PHPUnit_Framework_TestCase
{
	protected $metadata;

	protected function setUp() {
		$mockservice = $this->getMockBuilder("WebApi\\PgsqlService")->disableOriginalConstructor()->getMock();
		$mockservice->expects($this->exactly(1))->method('get_endpoint')->will($this->returnValue('webapi'));
		$mockservice->expects($this->exactly(1))->method('get_name')->will($this->returnValue('cartesius'));
		
		$mocktabledata = array();
		$mocktabledata[] = array("resource"=>"testOne");
		$mocktabledata[] = array("resource"=>"testTwo");
		
		$mockservice->expects($this->exactly(1))->method('get_resources')->will($this->returnValue($mocktabledata));

		$mockpropertiesdata = array();
		$mockpropertiesdata[] = array("name" => "id", "is_nullable" => false, "default_value" => 0, "max_length" => 8, "data_type" => "int8"); 
		$mockpropertiesdata[] = array("name" => "name", "is_nullable" => false, "default_value" => null,"max_length" => 32, "data_type" => "varchar"); 
		$mockpropertiesdata[] = array("name" => "description", "is_nullable" => true, "default_value" => null, "max_length" => 256, "data_type" => "varchar"); 
		$mockpropertiesdata[] = array("name" => "related", "is_nullable" => false, "default_value" => null, "max_length" => 32, "data_type" => "int8"); 
		
		$mockservice->expects($this->exactly(2))->method('get_data_properties')->with($this->stringContains('Test'))->will($this->returnValue($mockpropertiesdata));

		$mockpkeydata = array();
		$mockpkeydata[] = array("pkey"=>"PRIMARY KEY", "name"=>"id");
		$mockservice->expects($this->exactly(2))->method('get_primary_key')->with($this->stringContains('Test'))->will($this->returnValue($mockpkeydata));

		$mockfkeydata1 = array();
		$mockfkeydata1[] = array("resource" => "testOne", "property" => "related", "foreign_resource" => "testTwo", "foreign_property" => "id", "association_name" => "fkey_test_two_id");

		$mockfkeydata2 = array();
		$mockfkeydata2[] = array("resource" => "testTwo", "property" => "id", "foreign_resource" => "testOne", "foreign_property" => "related", "association_name" => "fkey_test_two_id");

		$mockservice->expects($this->exactly(2))->method('get_navigation_properties')->with($this->stringContains('Test'))->will($this->onConsecutiveCalls($mockfkeydata1, $mockfkeydata2));

        $map = array(
          array('varchar', 'String'),
          array('int8', 'Int64')
        );

		$mockservice->expects($this->any())->method('match_type')->will($this->returnValueMap($map));

		$mockservice->expects($this->any())->method('get_type')->will($this->returnValue("pgsql"));

        $nc_map = array(
          array('testOne', 'TestOne'),
          array('testTwo', 'TestTwo')
        );

		$mockservice->expects($this->any())->method('parse_nc')->will($this->returnValueMap($nc_map));
		
		$this->metadata = new WebApi\MetaData($mockservice);
				
	}

	public function testResourceExists() {
		$expected = true;
		$actual = $this->metadata->resource_exists("TestTwo");
		$this->assertEquals($expected, $actual);
	}

	public function testGetResource() {
		$expected = "WebApi\\StructuralType";
		$actual = $this->metadata->get_resource("TestTwo");
		$this->assertInstanceOf($expected, $actual);
	}
	
}
