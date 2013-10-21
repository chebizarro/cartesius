define(function(require) {

    //dependencies
    var Boiler = require('Boiler'), 
        //settings = require('./settings'), 
        ProjectsListRouteHandler = require('/component/projects/list'),
        ProjectsEditRouteHandler = require('/component/projects/edit');

    return {
        initialize : function(parentContext) {
            var context = new Boiler.Context(parentContext);

			// Add menu items to the Main Menu
			ko.postbox.publish("MAINMENU_READY", {label:"Projects",id:"mainmenu.projects"});
			ko.postbox.publish("MAINMENU_READY", {label:"New",id:"mainmenu.projects.new",parent:"mainmenu.projects" });
			
			// Add routes for the menu items
			var controller = new Boiler.UrlController($(".appcontent"));
            controller.addRoutes({
                "mainmenu.projects.new" : new ProjectsEditRouteHandler(context)
            });
            controller.start();

            var projectsList = new ProjectsListRouteHandler(context);

        }
    }

});
