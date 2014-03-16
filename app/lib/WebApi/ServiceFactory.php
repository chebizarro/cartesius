<?php

namespace WebApi;

class ServiceFactory
{

	public static function create($config) {
		$serviceclass = "\\WebApi\\" . $config["type"];
		if (class_exists($serviceclass)) {
			$service = new $serviceclass($config);
			return $service;			
		}
		else {
			throw new \Exception("Invalid service ". $serviceclass. " type given.");
		}
	}
}
