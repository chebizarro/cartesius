<?php

require '../app/config/config.php';
require VENDOR.'autoload.php';
require LIB.'WebApi/WebApi.php';

// Create services

$cart_config = array(
			'name' => 'cartesius',
			'endpoint' => 'webapi',
			'type'=>'ORMService',

			'metadata' => array(
				'type' => 'auto',
				'path' => DATA
			),

			'resource' => array(
				'driver'=>'pgsql',
				'host' => '127.0.0.1',
				'port' => 5432,
				'username' => 'postgres',
				'password' => 'postgres'
			),
			'nc' => \WebApi\NC_PASCAL
		);

$cartesius = \WebApi\ServiceFactory::create($cart_config);
$dispatcher = new \WebApi\Dispatcher();
$dispatcher->addService($cartesius);

$app = new \Slim\Slim($slimconfig);
$app->add(new \Slim\Middleware\SessionCookie($slimcookiesecret));
$app->add($dispatcher);


$app->get('/webapi/cartesius/Metadata', function() use ($app) {
	$env = $app->environment;
	//echo $env['HTTP_ACCEPT'];
});



$app->run();
