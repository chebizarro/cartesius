<?php

/* configuration file */

define('ROOT', '/opt/lappstack-5.4.19-0/apps/cartesius.dur/htdocs/cartesius/');

/* define constants */
define('APP', ROOT.'app/');
define('TEMPLATES', APP.'templates/');
define('MODELS', APP.'models/');
define('VENDOR', APP.'vendor/');
define('LIB', APP.'lib/');
define('ROUTES', APP.'routes/');
define('MODULES', APP.'modules/');
define('CORE', APP.'core/');
define('DATA', APP.'data/');




$slimconfig = array(
   'debug' => true,
   'templates.path' => MODULES
);

$slimcookiesecret = array('secret' => 'whynotnoteatpigstogether');

$slimgoogleauth = array(
	'name' => 'Cartesius COP',
	'client_id' => '27386843570.apps.googleusercontent.com',
	'client_secret' => 'tmx6GBbL-cenjv_IvmRXTgVH',
	'redirect_uri' => 'http://cartesius.no-ip.info/',
	'developer_key' => 'AIzaSyC49j65TFx51dcMJcdvbLWvP_2Vej2X65s',
	'domain' => 'greenpeace.org',
	'login.url' => '/login',
	'security.urls' => array(
		array('path' => '/'))
);

$config = array(
	'services' => array(
		array(
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
		),
		array(
			'type'=>'pgsql',
			'host' => '127.0.0.1',
			'port' => 5432,
			'name' => 'northwind',
			'username' => 'postgres',
			'password' => 'postgres',
			'endpoint' => 'webapi',
			'nc' => NC_PASCAL,
			'authenticate' => null,
			'exclude' => null
		)
	),
	'metadata_path' => DATA,
	'endpoint' => 'webapi'
);
