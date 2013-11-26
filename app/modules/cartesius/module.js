define(['plugins/router', 'durandal/app', 'services/datacontext'],
	function (router, app, datacontext) {


    return {
		router: router,
		viewUrl: '/view/cartesius/mainpage/view',
        activate: function () {
						
            router.map([
                { route: '', moduleId: '/component/geo/map', nav: true }
            ]).buildNavigationModel().makeRelative('/').mapUnknownRoutes();
            
            return router.activate();
        },
        
        attached: function () {
			$("#vsplitter").fadeIn().resize();	
		},

        mainmenu: function() {
            
        },
        
		workbench: null
    }

});
