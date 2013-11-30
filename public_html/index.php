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
require LIB.'WebApiMetaData.php';
require LIB.'WebApiQueryLexer.php';
require LIB.'GoogleOAuth.php';


$config = array('cartesius' => array(
	'type'=>'pgsql',
	'host' => '127.0.0.1',
	'port' => 5432,
	'resource' => 'cartesius',
	'username' => 'postgres',
	'password' => 'postgres',
	'endpoint' => 'webapi',
	'metadatapath' => DATA,
	'nc' = NC_PASCAL,
	'authenticate' = 'authenticate_function',
	'exclude' = array('account' => array('token')),				
));

\WebApi\WebApiAdapter::configure($config);


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
	echo \WebApi\WebApiAdapter::get_data($connection, $model, $vars);
});



$app->get('/xmltest', function() use ($app) {

	$app->contentType('application/javascript');

	//$people =  \WebApi\ORM\Model::factory("\WebApi\ORM\Cartesius\Account", "cartesius")->where("username","Chris Daley")->where("email","cdaley@greenpeace.org")->find_many();
	//print_r($people->as_array());
	//echo $people->as_xml()->saveXml();
	//build_models();
	//echo \WebApi\ORM\ORM::get_last_query("cartesius");
});


$app->get('/test', function() use ($app) {

 	$app->response->headers->set('Content-Type', 'text/html');
	$res = $app->response();
	$res['X-SendFile'] = ROOT . 'public_html/DocCode/DocCode/index.html';

});


$app->get('/regextest', function() use ($app) {

 	$app->response->headers->set('Content-Type', 'application/javascript');

	$reg = array(
		"orderby" => array(
			"UnitPrice desc",
			"UnitPrice",
			"UnitPrice desc,ProductName",
			"UnitPrice desc,ProductName/Category",
			"Product/UnitPrice,ProductName/Category desc",
			"Product/UnitPrice,ProductName desc,Category",
			"Product,ProductName",
			"Suppliers/CompanyName"
		),

		"expand" => array(
			"ProjectAuthor",
			"Suppliers/CompanyName",
			"ProjectAuthor,Suppliers/CompanyName",
			"ProjectAuthor,Suppliers/CompanyName,Monkey/Island",
			"ProjectAuthor,Suppliers/CompanyName,Monkey"
		),

		"select" => array(
			"ProjectAuthor",
			"Suppliers/CompanyName",
			"ProjectAuthor,Suppliers/CompanyName",
			"ProjectAuthor,Suppliers/CompanyName,Monkey/Island",
			"ProjectAuthor,Suppliers/CompanyName,Monkey"
		),
	
		"filter" => array(
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
			"startswith(Category/CategoryName,'S') eq true",
			"startswith(Category.CategoryName,'S') eq true",
			"not (Freight gt 100)"
		)
	);

	foreach($reg as $key => $value) {
		foreach($value as $query) {
			try {
				echo "\n{$key}: {$query}\n\n";
				print_r(\WebApi\QueryLexer::run($key, $query));
			} catch (\Exception $e) {
				echo $e;
			}
		}
	}

	//echo \WebApi\WebApiAdapter::get_data('northwind', $model, $vars);
});

$app->get('/filtertest', function() use ($app) {
 	$app->response->headers->set('Content-Type', 'application/javascript');
/*
	echo \WebApi\WebApiAdapter::get_data('northwind', 'employees' ,array('$filter' => 'EmployeeID eq 1'));
	echo \WebApi\WebApiAdapter::get_data('todos', 'todos', array('$filter' => 'IsArchived eq false'));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "Freight gt 100"));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "OrderDate ge datetime'1998-04-28T17:00:00.000Z'"));
	echo \WebApi\WebApiAdapter::get_data('northwind','employees',array('$filter' => "Region ne null"));
	echo \WebApi\WebApiAdapter::get_data('todos','todos',array('$filter' => "(IsArchived eq false) and (IsDone eq false)"));
	echo \WebApi\WebApiAdapter::get_data('northwind','suppliers',array('$filter' => "(startswith(CompanyName,'S') eq true) and (substringof('er', City) eq true)"));
	echo \WebApi\WebApiAdapter::get_data('northwind','suppliers',array('$filter' => "(City eq 'London') or (City eq 'Paris')"));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "(Freight gt 100) and (OrderDate gt datetime'1998-03-31T17:00:00.000Z')"));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "(Freight gt 100) or (OrderDate gt datetime'1998-03-31T17:00:00.000Z')"));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "((OrderDate ge datetime'1995-12-31T17:00:00.000Z') and (OrderDate lt datetime'1996-12-31T17:00:00.000Z')) and (Freight gt 100)"));
	echo \WebApi\WebApiAdapter::get_data('northwind','suppliers',array('$filter' => "length(CompanyName) gt 30"));
	echo \WebApi\WebApiAdapter::get_data('northwind','suppliers',array('$filter' => "toupper(substring(CompanyName,1,2)) eq 'OM'"));
	echo \WebApi\WebApiAdapter::get_data('northwind','suppliers',array('$filter' => "substringof('market',CompanyName) eq true"));
	echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$filter' => "startswith(ProductName,'C') eq true"));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "not (Freight gt 100)"));
	echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$expand' => "Suppliers"));
	echo \WebApi\WebApiAdapter::get_data('cartesius','account',array('$expand' => "ProjectAuthor"));
*/	
	echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$filter' => "startswith(Categories/CategoryName,'C') eq true", '$select' => "Categories"));
	echo \WebApi\WebApiAdapter::get_data('northwind','orders',array('$filter' => "Customers/Region eq 'CA'"));
	echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$filter' => "startswith(Suppliers/CompanyName,'S') eq true", '$orderby' => "UnitPrice desc,ProductName"));
	echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$filter' => "startswith(Suppliers/CompanyName,'S') eq true", '$orderby' => "Suppliers/CompanyName"));
	echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$filter' => "startswith(Suppliers/CompanyName,'S') eq true", '$orderby' => "Suppliers/CompanyName"));
	echo \WebApi\WebApiAdapter::get_data('northwind','customers',array('$filter' => "(startswith(CompanyName,'S')eq true) and (substringof('er',City) eq true)", '$select' => "CompanyName,City"));
	echo \WebApi\WebApiAdapter::get_data('northwind','employees',array('$select' => "Photo", '$filter' => "Region ne null"));

});

$app->get('/expandtest', function() use ($app) {
	$app->response->headers->set('Content-Type', 'application/javascript');
	$starttime = microtime(true);
	echo "'\$expand' => 'OrderDetails.Products'\n";
	echo $starttime. "\n";
	
	//echo \WebApi\WebApiAdapter::get_data('cartesius','account',array('$expand' => "ProjectAuthor"));
	//echo \WebApi\WebApiAdapter::get_data('cartesius','project',array('$expand' => "ProjectAuthor"));
	$response = \WebApi\WebApiAdapter::get_data('northwind','orders',array('$expand' => "OrderDetails.Products"));
	//echo \WebApi\WebApiAdapter::get_data('northwind','products',array('$orderby' => 'ProductName','$skip'=>10,'$expand'=>'Categories'));
	
	$endtime = microtime(true);
	echo $endtime. "\n";
	$totaltime = $endtime-$starttime;
	echo "Time: {$totaltime}\n";
	
	echo $response;

});

$app->get('/jointest', function() use ($app) {
//.where("Category.CategoryName", "startswith", "S")

WebApi\ORM\ORM::configure('id_column_overrides', array(
    'categories' => 'CategoryID',
    'products' => 'ProductID',
),
"northwind");

//$data = WebApi\ORM\ORM::for_table("products", "northwind")
$data = WebApi\ORM\Model::factory("WebApi\ORM\Northwind\Products", "northwind");

//print_r($data);

$data = $data->table_alias('p1')->order_by_asc('CategoryID');


echo $data->get_connection();

echo $data->get_class_name();

$data = WebApi\ORM\Model::factory("WebApi\ORM\Northwind\Products", "northwind")
    ->table_alias('p1')
    ->select('p1.*')
    ->select('p2.*', 'p2_*')
    ->join('categories', array('p2.CategoryID', '=', 'p1.CategoryID'), 'p2')
    ->where_like('p2.CategoryName', 'B%')
    ->find_many();
    
//print_r($data);

//$data = WebApi\ORM\Model::factory("WebApi\ORM\Northwind\Products", "northwind");
//$data = $data->find_many();

//$meta = $data->get_metadata();

//print_r($data);
});

$app->run();
