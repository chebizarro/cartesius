<?php

define('ROOT', '/opt/lappstack-5.4.19-0/apps/cartesius.dur/htdocs/cartesius/');

define('APP', ROOT.'app/');
define('TEMPLATES', APP.'templates/');
define('MODELS', APP.'models/');
define('VENDOR', APP.'vendor/');
define('LIB', APP.'lib/');
define('ROUTES', APP.'routes/');
define('MODULES', APP.'modules/');
define('CORE', APP.'core/');
define('DATA', APP.'data/');

require VENDOR.'autoload.php';
require VENDOR.'slim/extras/Slim/Extras/Log/DateTimeFileWriter.php';

require VENDOR.'google-api-php-client/src/Google_Client.php';
require VENDOR.'google-api-php-client/src/contrib/Google_PlusService.php';
require VENDOR.'google-api-php-client/src/contrib/Google_Oauth2Service.php';


require LIB.'XSLT.php';
require LIB.'JSON.php';
require LIB.'WebApiORM.php';
require LIB.'WebApiParis.php';
require LIB.'WebApiAdapter.php';
require LIB.'WebApiQueryLexer.php';
require LIB.'GoogleOAuth.php';


\WebApi\WebApiAdapter::configure(array(
	'connections' => array(
		'cartesius' => 'pgsql:host=127.0.0.1;port=5432;dbname=cartesius;user=postgres;password=postgres',
		'northwind' => 'pgsql:host=127.0.0.1;port=5432;dbname=northwind;user=postgres;password=postgres',
		'todos' => 'pgsql:host=127.0.0.1;port=5432;dbname=todos;user=postgres;password=postgres'
	),
	'modelpath' => MODELS,
	'metadatapath' => DATA,
	'endpoint' => 'webapi'
));


// Start Slim.
$app = new \Slim\Slim(array(
   'debug' => true,
   'templates.path' => MODULES,
   'log.level' => \Slim\Log::ERROR,
   'log.enabled' => true,
   'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter(array(
            'path' => APP.'log',
            'name_format' => 'Y-m-d',
            'message_format' => '%label% - %date% - %message%'
        ))
));

$app->add(new \Slim\Middleware\SessionCookie(array('secret' => 'whynotnoteatpigstogether')));

/*
$app->add(new \Slim\Extras\Middleware\GoogleOAuth(array(
	'name' => 'Cartesius COP',
	'client_id' => '27386843570.apps.googleusercontent.com',
	'client_secret' => 'tmx6GBbL-cenjv_IvmRXTgVH',
	'redirect_uri' => 'http://cartesius.no-ip.info/',
	'developer_key' => 'AIzaSyC49j65TFx51dcMJcdvbLWvP_2Vej2X65s',
	'domain' => 'greenpeace.org',
	'login.url' => '/login',
	'security.urls' => array(
		array('path' => '/'))

))
);
*/

function authenticate(\Slim\Route $route) {

	$app = \Slim\Slim::getInstance();
	/*
    if (isset($_SESSION['access_token'])) {
		
	} else {
		$app->halt(401);
	}
	*/
}

$app->get('/', function() use ($app) {
	$_SESSION['access_token'] = "wordtothemotherfuckingmasses";
	$_SESSION['userid'] = 1;
    $app->contentType('text/html');
    return $app->render('cartesius/view.html', array());	
});

$app->get('/logout', function() use ($app) {
	unset($_SESSION['access_token']);
	$app->redirect('/');
});

$app->get('/login', function() use ($app) {
	$req = $app->request();
	$app->view(new Slim\Extras\Views\XSLT);
    $app->contentType('text/html');

	if($req->get('redirect')) {
		$link = array('link' => htmlentities(urldecode($req->get('redirect'))));
	    return $app->render('cartesius/auth/login.xsl', array('data' =>  $link));
	} else {
		$app->redirect('/');
	}
});

$app->get('/module/:module', 'authenticate', function($module) use ($app) {
 	$app->response->headers->set('Content-Type', 'application/javascript');
	$res = $app->response();
	$res['X-SendFile'] = MODULES. $module . '/' . 'module.js';
});

$app->get('/module/:module/:component', 'authenticate', function($module, $component) use ($app) {
 	$app->response->headers->set('Content-Type', 'application/javascript');
	$res = $app->response();
	$res['X-SendFile'] = MODULES. $module . '/' . $component . '.js';
});

$app->get('/component/:module/:component', 'authenticate', function($module, $component) use ($app) {
 	$app->response->headers->set('Content-Type', 'application/javascript');
	$res = $app->response();
	$res['X-SendFile'] = MODULES. $module . '/' . $component . '/component.js';
});

$app->get('/view/:module/:component/:view', 'authenticate', function($module, $component, $view) use ($app) {
    $app->contentType('text/html');
    $a = array();
	//$view = preg_replace('/\.[^.]+$/','',$view);
	return $app->render($module.'/'.$component.'/'. $view, array('data' => $a));	
});

$app->get('/viewmodel/:module/:component', 'authenticate', function($module, $component) use ($app) {
 	$app->response->headers->set('Content-Type', 'application/javascript');
	$res = $app->response();
	$res['X-SendFile'] = MODULES. $module . '/' . $component . '/viewmodel.js';
});

$app->get('/prefs/:module/:component', 'authenticate', function($module, $component) use ($app) {
	$app->response->headers->set('Content-Type', 'application/json');
	
	$USERPREFS = \Preferences::where('key', $module .'.' . $component)->where('user_id', $_SESSION['userid'])->find_one();
	
	if($USERPREFS == null) {
		$app->response()->status(404);
	} else {
				
	}	
	//echo json_encode($PREFS);
	//exit;

});


$app->get('/model/:module/:component(/:params(/:format))', 'authenticate', function($module, $component, $params = null, $format = 'json') use ($app) {
	
	$MODEL = new $component();
	$DATA = $MODEL->getData($format);
	$app->contentType('application/json');
	echo json_encode($DATA);
	exit;
});


/// WebApi Calls ///


$app->get('/webapi/:connection/Lookups', function($connection) use ($app) {

});

$app->post('/webapi/:connection/purge', function($connection) use ($app) {
	//implement?
	$app->contentType('application/text');
	echo "purge";
});

$app->post('/webapi/:connection/reset', function($connection) use ($app) {
	//implement?
	$app->contentType('application/text');
	echo "reset";
});

$app->get('/webapi/:connection/Metadata', function($connection) use ($app) {
	$metadata = \WebApi\WebApiAdapter::show_metadata($connection);

	if($metadata) {
		$app->contentType('application/json');	
		echo $metadata;
	} else {
		$app->halt(404);
	}
});

$app->post('/webapi/:connection/SaveChanges', function($connection) use ($app) {
	$request = $app->request();
    $DATA = json_decode($request->getBody());
	$app->contentType('application/json');
	echo \WebApi\WebApiAdapter::save_changes($connection, $DATA);
	exit;
});



$app->get('/webapi/:connection/:model', function($connection, $model) use ($app) {
	$app->contentType('application/json');
	$vars = $app->request->get();
	echo \WebApi\WebApiAdapter::data($connection, $model, $vars);
});



$app->get('/xmltest', function() use ($app) {

	$app->contentType('application/javascript');

	$people =  \Model::factory("Account", "cartesius")->find_many();
	print_r($people->as_array());
		//echo $people->as_xml()->saveXml();
	//build_models();
});


$app->get('/test', function() use ($app) {

 	$app->response->headers->set('Content-Type', 'text/html');
	$res = $app->response();
	$res['X-SendFile'] = ROOT . 'public_html/DocCode/DocCode/index.html';

});


$app->get('/regextest', function() use ($app) {

 	$app->response->headers->set('Content-Type', 'application/javascript');

	$reg = Array(
		"EmployeeID eq 1",
		"IsArchived eq false",
		"Freight gt 100m",
		"OrderDate ge datetime'1997-12-31T17:00:00.000Z'",
		"Region ne null",
		"(IsArchived eq false) and (IsDone eq false)",
		"(startswith(CompanyName,'S') eq true) and (substringof('er', City) eq true)",
		"(City eq 'London') or (City eq 'Paris')",
		"(Freight gt 100) and (OrderDate gt datetime'1998-03-31T17:00:00.000Z')",
		"(Freight gt 100) or (OrderDate gt datetime'1998-03-31T17:00:00.000Z')",
		"((OrderDate ge datetime'1995-12-31T17:00:00.000Z') and (OrderDate lt datetime'1996-12-31T17:00:00.000Z')) and (Freight gt 100)",
		"length(CompanyName) gt 30",
		"toupper(substring(CompanyName,1,2)) eq 'OM'",
		"substringof('market',CompanyName) eq true",
		"startswith(ProductName,'C') eq true",
		"not (Freight gt 100)"
	);

	foreach($reg as $key) {
		try {
			print_r(\WebApi\QueryLexer::run($key));
		} catch (\Exception $e) {
			echo $e;
		}
	}

});


$app->run();
