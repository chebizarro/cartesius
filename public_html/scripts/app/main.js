requirejs.config({
    paths: {
        'text': '../../lib/require/text',
        'durandal':'../../lib/durandal/js',
        'plugins' : '../../lib/durandal/js/plugins',
        'transitions' : '../../lib/durandal/js/transitions',
        'knockout': '../../lib/knockout/knockout-2.3.0',
        'bootstrap': '../../lib/bootstrap/js/bootstrap',
        'jquery': '../../lib/jquery/jquery-1.9.1',
        'breeze': '../../lib/breeze/Scripts/breeze.min.js',
        'jqxwidgets': '../../lib/jqwidgets/jqwidgets/jqx-all'
    },
    shim: {
        'bootstrap': {
            deps: ['jquery'],
            exports: 'jQuery'
       },
		"knockout": { deps: ["jquery"], exports: 'jQuery' },
		"breeze": { deps: ["jquery"], exports: 'jQuery' },
		"jqxwidgets": { deps: ["jquery", "knockout"] }
    }
});

define('jquery', function () { return jQuery; });
define('knockout', ko);


define(['durandal/system', 'durandal/app', 'durandal/viewLocator'],  function (system, app, viewLocator) {
    //>>excludeStart("build", true);
    system.debug(true);
    //>>excludeEnd("build");

    app.title = 'Cartesius';

    app.configurePlugins({
        router:true,
        dialog: true,
        widget: true
    });

	app.modules = ko.observableArray();
	
	app.theme = ko.observable('metro');
	
	app.dataservice = new breeze.EntityManager('/webapi/cartesius/');
	
	ko.kendo.setDataSource = function (widget, fnCall, options) {
		fnCall(widget, options)
	};

	
	if (app.dataservice.metadataStore.isEmpty()) {
		app.dataservice.fetchMetadata()
					  .then(function (rawMetadata) {
				executeQuery();
			}).fail(function(e) {
						console.log(e);
					});
		} else {
			executeQuery();
		}

	
	function executeQuery() {
		var query = new breeze.EntityQuery()
			.from("Modules");
		app.dataservice.executeQuery(query).then(function(data){
			ko.utils.arrayForEach(data.results, function(item) {
				require(['/module/' + item.path() + '/module'], function(mod) {
						app.modules.push(mod);
					});				
			});
		}).fail(function(e) {
			console.log(e);							  
		});
	}


    app.start().then(function() {
        //Replace 'viewmodels' in the moduleId with 'views' to locate the view.
        //Look for partial views in a 'views' folder in the root.
        //viewLocator.useConvention();

        //Show the app by setting the root view model for our application with a transition.
                
        app.setRoot('/module/cartesius', 'entrance');

    });
});
