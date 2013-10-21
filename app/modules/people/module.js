define(function(require) {

    //dependencies
    var Boiler = require('Boiler'), 
        //settings = require('./settings'), 
        PeopleListRouteHandler = require('/component/people/peopleList'), 
        PeopleDetailRouteHandler = require('/component/people/peopleDetails');

    return {
        initialize : function(parentContext) {
            //create a new context which is associated with the parent Context        
        	ko.postbox.publish("MAINMENU_READY", {label:"People",id:"mainmenu.people"});
            ko.postbox.publish("MAINMENU_READY", {label:"List",id:"mainmenu.people.list", parent:"mainmenu.people"});

            var context = new Boiler.Context(parentContext);
            var peopleList = new PeopleListRouteHandler(context);

            var controller = new Boiler.UrlController($(".appcontent"));
            controller.addRoutes({
                "mainmenu.people.list" : new PeopleDetailRouteHandler(context)
            });
            controller.start();

        
        }
    }

});
