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

$connections['cartesius'] = 'pgsql:host=127.0.0.1;port=5432;dbname=cartesius;user=postgres;password=postgres';

foreach($connections as $key => $value) {
	XMLORM::configure('error_mode', PDO::ERRMODE_WARNING, $key);
	XMLORM::configure($value, $key);
	XMLORM::configure('return_result_sets', true, $key);
}

WebApiAdapter::configure($connections);
WebApiAdapter::load_models(MODELS);

function build_models() {
	$sql = "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema');";
	$tables = XMLORM::for_table('information_schema', 'cartesius')->raw_query($sql)->use_id_column('table_name')->find_many()->as_array();
	foreach($tables as $key => $value) {
		$model_name = preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')",$key);
		$file_name = MODELS.$model_name.".php";
		if(file_exists($file_name)) {
			require_once($file_name);
		} else {
			file_put_contents($file_name, "<?php\n\n\nclass ".$model_name." extends XMLModel\n{\n\n}\n");
			require_once($file_name);
		}
	}
}

build_models();

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
	#require_once(MODULES.'Modules.php');

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

/*
	echo WebApiAdapter::save_changes($data);
*/


/*
{
	"entities":[
		{
			"id":"K_-1",
			"title":"Test",
			"date":"",
			"review_date":"Thu Nov 21 2013 00:00:00 GMT+0700 (WIB)",
			"summary":null,
			"entityAspect":{
				"entityTypeName":"Project:#XMLPARIS.Model",
				"defaultResourceName":"Project",
				"entityState":"Added",
				"originalValuesMap":{},
				"autoGeneratedKey":{
					"propertyName":"id",
					"autoGeneratedKeyType":"Identity"
				}
			}
		}
	],
	"saveOptions":{}
}
*/

	foreach ($DATA->entities as $entity) {
		$aspect = $entity->entityAspect;
		if($aspect->entityState == "Added") {
			$model = XMLModel::factory($aspect->defaultResourceName)->create();
			$keyVar = $aspect->autoGeneratedKey->propertyName;
			foreach($entity as $key => $value) {
				if (($key !== $keyVar) && ($key !== "entityAspect")) {
					if(strtotime($value) != null) {
						$value = strtotime($value);
					}
					$model->$key = $value;
				}
			}
			$model->save();
		}
		
	}

	$app->contentType('application/json');
	echo json_encode($DATA);
	exit;

});

$app->get('/data/Metadata', function() use ($app) {
	
	$app->contentType('application/javascript');
	
	//$tsql = "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema');";

	$metadata = array("dataServices" => [
					array(	"serviceName" => "/data/",
							"hasServerMetadata" => true,
							"jsonResultsAdapter" => "webApi_default",
							"useJsonp" => false ) ],
							"structuralTypes" => []);

	$resourceEntityTypeMap = [];

	foreach(get_declared_classes() as $model ) {
		if ( is_subclass_of($model, 'XMLModel') ){
			$met = XMLModel::factory($model)->create()->metadata();
			array_push($metadata["structuralTypes"], $met);
			$resourceEntityTypeMap[$met["defaultResourceName"]] = $met["shortName"] . ":#" . $met["namespace"];
		}
	}
	
	$metadata["resourceEntityTypeMap"] = $resourceEntityTypeMap;
	
	echo json_encode($metadata, JSON_PRETTY_PRINT);
	
	/*
	echo WebApiAdapter::meta_data();
	*/

});


$app->get('/data/:model', function($model) use ($app) {

	//$allvars = $app->request->get();
	//print_r($allvars);
	
	//$inlinecount=allpages
	//$filter=(startswith(CompanyName,'S') eq true) and (substringof('er', City) eq true)
	//$orderby=UnitPrice desc,ProductName
	
	$app->contentType('application/json');

/*
	if(mb_substr($model, -3) == "ies") {
		$model = mb_substr($model, 0, (mb_strlen($model)-3)) . "y";
	} else {
		$model = mb_substr($model, 0, (mb_strlen($model)-1));
	}
*/

	$data = XMLModel::factory($model);

	$filter = $app->request->get('$filter');

	if($filter) {
		preg_match('/(.*?)\((.*?)\)\s([^\s]*)\s([^\s]*)/', $filter, $match);

		if(sizeof($match) > 0) {
			preg_match("/(.*),'(.*)'/", $match[2], $condition);
			switch ($match[1])
			{
				case "startswith":
					$condition[2] = $condition[2] . "%";
					break;
				case "endswith":
					$condition[2] = "%" . $condition[2];
					break;				
				case "contains":
				case "substringof":
					$condition[2] = "%" . $condition[2] . "%";
					break;
			}
			if ($match[4] == "true") {
				$data = $data->where_like($condition[1], $condition[2]);
			} else {
				$data = $data->where_not_like($condition[1], $condition[2]);				
			}
		} else {
			preg_match('/(.*)\s(.*)\s(.*)/', $filter, $match);
			$column = $match[1];
			$condition = $match[2];
			$value = $match[3];
			
			switch ($condition)
			{
				case "gt":
					$data = $data->where_gt($column, $value);
					break;
				case "lt":
					$data = $data->where_lt($column, $value);
					break;				
				case "eq":
					$data = $data->where_equal($column, $value);				
					break;
				case "ge":
					$data = $data->where_gte($column, $value);
					break;
				case "le":
					$data = $data->where_lte($column, $value);
					break;
				case "ne":
					$data = $data->where_not_equal($column, $value);
					break;	
			}
		}
	}
	
	$top = $app->request->get('$top');
	if ($top) {
		$data = $data->limit($top);
	}
	
	$skip = $app->request->get('$skip');
	if ($skip) {
		$data = $data->offset($skip);
	} 
/*
	$orderby = $app->request->get('$orderby');
	$orderbyArray = explode(" ", $orderby);
	
	if (sizeof($orderbyArray) > 1) {
		$data = $data->order_by_desc($orderbyArray[0]);	
	} else {
		$data = $data->order_by_asc($orderbyArray[0]);	
	}
*/
	/*
	echo WebApiAdapter::data($model, $vars);
	*/

	echo $data->find_many()->as_json();
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
