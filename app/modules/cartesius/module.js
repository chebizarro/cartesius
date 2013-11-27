define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {

		var shell = {
			activate: activate,
			router: router,
			viewUrl: '/view/cartesius/mainpage/view',
			attached: attached,
			mainmenu: mainmenu,
			workbench: null
		};
        return shell;

        function activate() {
			app.title = config.appTitle;
			return datacontext.primeData()
				.then(boot)
				.fail(failedInitialization);
        }

        function boot() {
            router.map([
                { route: '', moduleId: config.startModule, nav: true }
            ]).buildNavigationModel().makeRelative('/').mapUnknownRoutes();
            
            return router.activate();
        }

        function failedInitialization(error) {
            var msg = 'App initialization failed: ' + error.message;
            console.log(msg);
            //logger.logError(msg, error, system.getModuleId(shell), true);
        }

        
        function attached() {
			$("#vsplitter").fadeIn().resize();	
		}

        function mainmenu() {   
        }
        
    }

);
