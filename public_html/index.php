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
require LIB.'XMLORM.php';
require LIB.'XMLPARIS.php';
require LIB.'WebApiAdapter.php';
require LIB.'GoogleOAuth.php';

$connections = array('cartesius' =>'pgsql:host=127.0.0.1;port=5432;dbname=cartesius;user=postgres;password=postgres');

WebApiAdapter::configure($connections);
WebApiAdapter::load_models(MODELS);

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


$app->get('/data/Lookups', function() use ($app) {

});

$app->post('/data/SaveChanges', function() use ($app) {
	$request = $app->request();
    $DATA = json_decode($request->getBody());
	$app->contentType('application/json');
	echo WebApiAdapter::save_changes($DATA);
	exit;
});

$app->get('/data/Metadata', function() use ($app) {
	$app->contentType('application/json');
	echo json_encode(WebApiAdapter::show_metadata(DATA),JSON_PRETTY_PRINT);
});


$app->get('/data/:model', function($model) use ($app) {
	$app->contentType('application/json');
	$vars = $app->request->get();
	echo WebApiAdapter::data($model, $vars);
});


$app->run();
