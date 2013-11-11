define(['plugins/router', 'durandal/app'], function (router, app) {

	return {
		viewUrl: '/view/geo/map/view',
		
		map: null,
		
		attached: function () {

			map = new L.Map('map');
			var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
			var osmAttrib='Map data © OpenStreetMap contributors';
			var osm = new L.TileLayer(osmUrl, {minZoom: 1, maxZoom: 15, attribution: osmAttrib});		
			map.setView(new L.LatLng(5, 115),4);
			map.addLayer(osm);

		},
		
		compositionComplete: function () {
			map.invalidateSize();
		}
	}
});
