define(function(require) {

    //dependencies
    var Boiler = require('Boiler'), 
        //settings = require('./settings'), 
        PeopleListRouteHandler = require('/component/people/peopleList'), 
        PeopleDetailRouteHandler = require('/component/people/peopleDetails');

    return {
        initialize : function(parentContext) {
            //create a new context which is associated with the parent Context
            var context = new Boiler.Context(parentContext);
            var peopleList = new PeopleListRouteHandler(context);
        
        }
    }

});
