function getLocation(map) {
     if (navigator.geolocation) {
        navigator.geolocation.watchPosition( function (p) {
          coord = [p.coords.longitude, p.coords.latitude];
          map.geomap("option", "center", coord);
          map.geomap("empty", false);
          var accuracyBuffer = p.coords.accuracy / map.geomap("option", "pixelSize");
          map.geomap("append", { type: "Point", coordinates: coord }, { color: "#cc0", width: accuracyBuffer, height: accuracyBuffer, borderRadius: accuracyBuffer }, false);
          map.geomap("append", { type: "Point", coordinates: coord });
        }, function (error) {
          
        }, {
          enableHighAccuracy: true,
          maximumAge: 10000
        } );
      }	
}

function OpenLayersMap() {
	
	var lon = 8;
	var lat = 0;
	var zoom = 1;
	var map,layer;

	map = new OpenLayers.Map({
        div: "map",
        allOverlays: true,
        controls: []
    });

    var osm = new OpenLayers.Layer.OSM();

    // note that first layer must be visible
    map.addLayer(osm);
	
	layer = new OpenLayers.Layer.WMS( "export_airports",
			"http://172.16.111.128/cgi-bin/mapserv", {map:'/mnt/hgfs/web/cartesius/app/data/mapfile/airports.map', layers: 'export_airports'} );
	//map.addLayer(layer);

	map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);

	map.addControl(new OpenLayers.Control.Navigation());

}
