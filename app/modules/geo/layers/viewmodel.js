define(function(require) {
    // Load the dependencies
    var Boiler = require('Boiler');

    var ViewModel = function (moduleContext) {
        var self = this;
        
        this.layers = ko.observableArray();
        
        this.index = ko.observable(0);
		this.showBoxes = ko.observable(true);
		this.checked = ko.observableArray();

        $.getJSON('/model/geo/layers/{}/json', function (result) {
            self.layers(result);
        });
        
        this.prefs = {
			panelOpen: true,
			baseLayer: 1,
			visibleLayers: [1],
			openFolders: [1]
		};

                
		if(localStorage.getItem('geo.layers')) {
			//this.prefs([]);
			this.prefs = JSON.parse(localStorage.getItem('geo.layers'));
		} else {
			$.getJSON('/prefs/geo/layers', function (result) {
				//self.prefs([]);
				self.prefs = result;
				localStorage.setItem('geo.layers') = JSON.stringify(result);
			})
			.fail(function( jqxhr, textStatus, error ) {
				var err = textStatus + ", " + error;
				console.log( "Request Failed: " + err );
				if(jqxhr.status == 404) {
					localStorage.setItem('geo.layers', JSON.stringify(self.prefs));
				}
			});
		}
        
    };
    return ViewModel;
});
