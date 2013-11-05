<?php

define('ROOT', '/opt/lappstack-5.4.19-0/apps/cartesius/htdocs/cartesius/');

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
	
	//$user = \Account::find_one($_SESSION['userid'], 'cartesius')->as_xml();
	$user = XMLModel::factory("Account", 'cartesius')->find_one($_SESSION['userid'])->as_xml();

	$app->view(new Slim\Extras\Views\XSLT);
    $app->contentType('text/html');
    
    return $app->render('cartesius/template.xsl', array('data' => $user));	
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

$app->get('/modules/modules', 'authenticate', function() use ($app) {
	#require_once(MODULES.'Modules.php');

	$app->view(new Slim\Extras\Views\XSLT);
	$app->contentType('application/javascript');
	$MODULES = XMLModel::factory('Modules','cartesius')->find_many()->as_xml();
	//$a = array('modules' => $MODULES);
	return $app->render('modules.xsl', array('data' => $MODULES));

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

$app->get('/view/:module/:component/:view(/:model)', 'authenticate', function($module, $component, $view, $model=null) use ($app) {
	$app->view(new Slim\Extras\Views\XSLT);
    $app->contentType('text/html');
    
	if(isset($model)) {
		$MOD = Model::factory(ucfirst($model))->find_many();
		$a = array($model => $MOD);
	} else {
		$a = array();
	}
	
	return $app->render($module.'/'.$component.'/'. $view.'.xsl', array('data' => $a));	
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


$app->post('/save/:module', 'authenticate', function($module) use ($app) {
	$request = $app->request();
    $DATA = json_decode($request->getBody());

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
	echo json_encode(WebApiAdapter::show_metadata(DATA));
});


$app->get('/data/:model', function($model) use ($app) {
	$app->contentType('application/json');
	$vars = $app->request->get();
	echo WebApiAdapter::data($model, $vars);
});


$app->get('/phpinfo', function() use ($app) {
	phpinfo();
});

$app->get('/test', function() use ($app) {

	$app->contentType('application/javascript');
	
	//$people = XMLORM::for_table('information_schema')->raw_query("SELECT * FROM information_schema.columns WHERE table_name = 'project'")->use_id_column('ordinal_position')->order_by_asc('ordinal_position')->find_many()->as_xml();
	//$people = \Account::find_many()->as_xml();
	//echo $people->saveXML();
	build_models();
});



$app->run();
