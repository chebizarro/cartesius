define(function(require) {

    //dependencies
    var Boiler = require('Boiler'), 
    BreezeRouteHandler = require('/component/breeze/test');


    return {
        initialize : function(parentContext) {
            var context = new Boiler.Context(parentContext);

			// Add menu items to the Main Menu
			ko.postbox.publish("MAINMENU_READY", {label:"Breeze",id:"mainmenu.breeze"});
			ko.postbox.publish("MAINMENU_READY", {label:"Test",id:"mainmenu.breeze.test",parent:"mainmenu.breeze" });
			
			// Add routes for the menu items
			var controller = new Boiler.UrlController($(".appcontent"));
            controller.addRoutes({
                "mainmenu.breeze.test" : new BreezeRouteHandler(context)
            });
            controller.start();

        }
    }

});
