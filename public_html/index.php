<?php

define('ROOT', '/mnt/hgfs/web/cartesius/');

define('APP', ROOT.'app/');
define('TEMPLATES', APP.'templates/');
define('MODELS', APP.'models/');
define('VENDOR', APP.'vendor/');
define('LIB', APP.'lib/');
define('ROUTES', APP.'routes/');
define('MODULES', APP.'modules/');

require VENDOR.'autoload.php';
require VENDOR.'slim/extras/Slim/Extras/Log/DateTimeFileWriter.php';

require VENDOR.'google-api-php-client/src/Google_Client.php';
require VENDOR.'google-api-php-client/src/contrib/Google_PlusService.php';
require VENDOR.'google-api-php-client/src/contrib/Google_Oauth2Service.php';


require LIB.'XSLT.php';
require LIB.'JSON.php';
require LIB.'XMLORM.php';
require LIB.'XMLPARIS.php';
require LIB.'GoogleOAuth.php';


XMLORM::configure('error_mode', PDO::ERRMODE_WARNING);
XMLORM::configure('pgsql:host=localhost;port=5432;dbname=cartesius;user=cartesius;password=cartesius');
XMLORM::configure('return_result_sets', true);

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
    if (isset($_SESSION['access_token'])) {
		
	} else {
		$app->halt(401);
	}
}

$app->get('/', function() use ($app) {
	$_SESSION['access_token'] = "wordtothemotherfuckingmasses";
	$_SESSION['userid'] = 4;
	
	$user = \Account::find_one($_SESSION['userid'])->as_xml();
	
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
	require_once(MODULES.'Modules.php');

	$app->view(new Slim\Extras\Views\XSLT);
	$app->contentType('application/javascript');
	$MODULES = XMLModel::factory('Modules')->find_many()->as_xml();
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
		require_once(MODULES.$module.'/'.$component.'/'.ucfirst($model).'.php');
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
	
	require_once(MODULES.'cartesius/preferences/model.php');

	$USERPREFS = \Preferences::where('key', $module .'.' . $component)->where('user_id', $_SESSION['userid'])->find_one();
	
	if($USERPREFS == null) {
		$app->response()->status(404);
	} else {
				
	}	
	//echo json_encode($PREFS);
	//exit;

});


$app->get('/model/:module/:component(/:params(/:format))', 'authenticate', function($module, $component, $params = null, $format = 'json') use ($app) {
	require_once(MODULES. $module . '/' . $component . '/model.php');
	
	$MODEL = new $component();
	$DATA = $MODEL->getData($format);
	$app->contentType('application/json');
	echo json_encode($DATA);
	exit;
});

$app->get('/test', function() use ($app) {

	/*
	class Modules extends XMLModel {
		public function components() {
			return $this->has_many('Components','module_id');
		}
	}

	class Components extends XMLModel {
	}

	$app->contentType('application/javascript');


	$MODULES = Model::factory('Modules')->find_array();
	print_r($MODULES);

	echo '\n\n+++++++++++++++++++\n\n';

	$MODULES = Model::factory('Modules')->find_one(1)->as_array();
	print_r($MODULES);

	//echo '\n\n+++++++++++++++++++\n\n';

	//$COMPONENTS = $MODULES->components()->find_many();
	//print_r($COMPONENTS);

	echo '\n\n+++++++++++++++++++\n\n';	

	$MODULES = ORM::for_table('components')->join('modules', array('modules.id', '=', 'components.module_id'))->find_many();
	print_r($MODULES);

	echo '\n\n+++++++++++++++++++\n\n';
*/
	//$MODULES = XMLORM::for_table('components')->as_xml();
	//print_r($MODULES);

	//$MODULES = XMLORM::for_table('components')->find_many()->as_xml();

	$app->contentType('application/xml');
	
	//require_once MODULES . 'geo/layers/model.php';
	
	//$MODULES = \Layer::find_many()->as_json();
	
	//echo $MODULES;
		
	//$app->view(new Slim\Extras\Views\JSON);
    
    //return $app->render('', array("data" => $MODULES));	

	//$people = XMLORM::for_table('account')->table_schema();
	//$people = XMLORM::for_table('account')->find_one();
	//XMLORM::configure('id_column_overrides', array(
    //'information_schema.columns' => 'dtd_identifier'
	//))
	
	$people = XMLORM::for_table('information_schema')->raw_query("SELECT * FROM information_schema.columns WHERE table_name = 'account' ORDER BY ordinal_position ASC")->use_id_column('ordinal_position')->order_by_asc('ordinal_position')->find_many()->as_xml();
	
	echo $people->saveXML();
	//print_r($people);

});



// run
$app->run();
