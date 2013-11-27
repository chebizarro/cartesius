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

    app.start().then(function() {
        app.setRoot('/module/cartesius', 'entrance');

    });
});
