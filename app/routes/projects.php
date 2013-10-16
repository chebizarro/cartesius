<?php

$app->get('/projects/new', 'authenticate', function() use ($app) {
	$app->view(new Slim\Extras\Views\XSLT);
    $app->contentType("text/html");
	//$LAYERS = Model::factory('Layer')->find_many();
	//$a = array('layers' => $LAYERS);

	return $app->render('edit.project.xsl', array('data' => array()));	
});
