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
				
		if(isset($this->services[$resourceUriArray[0]])) {
			
			$service = $this->services[$resourceUriArray[0]][$resourceUriArray[1]];
			
			if($resourceUriArray[2] == "Metadata") {
				$metadata = $service->getMetaData();				
				// check format
				$response->headers->set('Content-Type', 'application/javascript');
				$response->setBody(json_encode($metadata, JSON_PRETTY_PRINT));
			} else {
				$requestMethod = $this->app->request->getMethod();
				$requestQuery = $this->app->request->params();
				$resource = $service->getResource($resourceUriArray[2]);								
				switch($requestMethod) {
				case "GET":
					$resource->setQuery($requestQuery);
				case "POST":
				case "PUT":
				case "DELETE":
				}
			}
		}
		$this->next->call();
		
		if ($response->status() != 200 && isset($this->app->data)) {
			// Get output format
			//$response["Content-Type"] = $this->data["content_type"];
            //$response->body($this->app->data->output());
            // set the recordcount option and other headers
        }
	}
}
