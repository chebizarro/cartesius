<?php

namespace WebApi;

use \PDO;



interface WebApiResourceInterface {


}


abstract class WebApiResource implements WebApiResourceInterface {
	
	private $metadata;
	private $namingconvention;
	
	abstract public function __construct();
	
	abstract protected function filter();
	abstract protected function expand();
	abstract protected function select();
	abstract protected function orderby();
	abstract protected function top();
	abstract protected function skip();
	
	abstract public function parse();
	
	
}

class WebApiORMResource extends WebApiResource {
	
	
}

class pgsql extends WebApiORMResource {
	
	
}
