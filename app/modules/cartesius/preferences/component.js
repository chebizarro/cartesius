define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
		Model = require('/viewmodel/cartesius/preferences');


	var Component = function(moduleContext) {
		var panel = null;
		return {
			initialise: function() {

				ko.postbox.subscribe("PREFERENCES", function(prefModel) {
					prefKey = prefModel.moduleName + prefModel.componentName;
					if(localStorage.getItem(prefKey)) {
						prefModel.prefs = JSON.parse(localStorage.getItem(prefKey));
					} else {
						$.getJSON('/prefs/'+prefModel.moduleName+'/'+prefModel.componentName, function (result) {
							prefModel.prefs = result;
							localStorage.setItem(prefKey) = JSON.stringify(result);
							ko.postbox.publish(prefModel.moduleName+'.'+prefModel.componentName+'.preferences', prefModel);

						});
						
					}
				});
			},
			
			
			activate : function(parent) {
				if (!panel) {
					panel = new Boiler.ViewTemplate(parent, template);

					var context = new Boiler.Context(parent);
					
				}
				panel.show();
			},

			deactivate : function() {
				if (panel) {
					panel.hide();
				}
			}
		};
	};

	return Component;

});
