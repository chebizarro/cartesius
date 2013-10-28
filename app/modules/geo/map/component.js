define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/geo/map/view');
        
	var Component = function(moduleContext) {

		var panel = null;

		this.activate = function (parent, params) {

		    if (!panel) {
		    	panel = new Boiler.ViewTemplate(parent, template);
				
				var lon = 8;
				var lat = 0;
				var zoom = 1;
				var map;

				map = new OpenLayers.Map({
					div: "map",
					allOverlays: true,
					controls: []
				});

				var osm = new OpenLayers.Layer.OSM();

				// note that first layer must be visible
				map.addLayer(osm);
				
				map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);

				map.addControl(new OpenLayers.Control.Navigation());

		    }
		    panel.show();
		};
		
		this.deactivate = function () {
		    if (panel) {
		        panel.hide();
		    }
		};
		
		var me = this;
		ko.postbox.publish("MAINFRAME_READY", me);

	};

	return Component;

});
