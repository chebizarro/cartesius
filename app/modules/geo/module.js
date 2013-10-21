define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
	Layers = require('/component/geo/layers');
	Map = require('/component/geo/map');
    require("/lib/openlayers/OpenLayers.js");


    return {
        
        initialize : function(parentContext) {
            var context = new Boiler.Context(parentContext);
			var layers = new Layers(context);
			//var map = new Map(context);
			
			var controller = new Boiler.UrlController($(".appcontent"));
            controller.addRoutes({
                "/" : new Map(context)
            });
            controller.start();

        }
        
    }

});
