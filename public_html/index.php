<?php

require '../app/config/config.php';
require VENDOR.'autoload.php';
require LIB.'WebApi/WebApi.php';

// Create services

$cart_config = array(
			'type'=>'pgsql',
			'host' => '127.0.0.1',
			'port' => 5432,
			'name' => 'cartesius',
			'username' => 'postgres',
			'password' => 'postgres',
			'nc' => \WebApi\NC_PASCAL
		);

$dispatcher_config = array('endpoint' => 'webpai');

$cartesius = new \WebApi\ORMService($cart_config);
$dispatcher = new \WebApi\Dispatcher($dispatcher_config);
$dispatcher->addService($cartesius);

$app = new \Slim\Slim($slimconfig);
$app->add(new \Slim\Middleware\SessionCookie($slimcookiesecret));
$app->add($dispatcher);


$app->get('/', function() use ($app) {

});



$app->run();
