<?php

namespace WebApi;

use \PDO;

class Dispatcher extends \Slim\Middleware {

	private $services;
	
	public function __construct() {

	}

	public function addService($service) {
		$name = $service->getName();
		$endpoint = $service->getEndpoint();
		$this->services[$endpoint][$name] = $service;
	}

	public function call() {
		$resourceUris = $this->app->request()->getResourceUri();
		$resourceUriArray = array_filter(explode("/",substr($resourceUris,1)));
		$response = $this->app->response();
				
		try {
			$service = $this->services[$resourceUriArray[0]][$resourceUriArray[1]];
			
			if($resourceUriArray[2] == "Metadata") {
				//$metadata = $service->getMetaData();
				// check format
				//$response->headers->set('Content-Type', 'application/json');
				//$response->setBody(json_encode([$metadata], JSON_PRETTY_PRINT));
				$this->app->resource = $service->getMetaData();
			} else {
				$requestMethod = $this->app->request->getMethod();
				$requestQuery = $this->app->request->params();
				$resource = $service->getResource($resourceUriArray[2]);							
				switch($requestMethod) {
				case "GET":
					$resource->setQuery($requestQuery);
					$this->app->resource = $resource;
				case "POST":
				case "PUT":
				case "DELETE":
				}
			}
		} catch (\Exception $e) {
			echo $e;
		}
		
		$this->next->call();
		
		if (isset($this->app->resource)) {
			// Get output format
			// set the recordcount option and other headers
			$response->setStatus(200);
			$response->headers->set('Content-Type', 'application/json');
			//try {
				$response->setBody(json_encode($this->app->resource->get(), JSON_PRETTY_PRINT));
			//} catch (\Exception $e) {
				
			//}
        }
	}
}
