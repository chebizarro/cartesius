define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/cartesius/workbench/view');
    
    require('/lib/jqwidgets/jqwidgets/jqxexpander.js');

	var Component = function(moduleContext) {
		var panel = null;
		return {
			activate : function(parent) {
				if (!panel) {
					panel = new Boiler.ViewTemplate(parent, template);

					var context = new Boiler.Context(parent);
					
					ko.postbox.subscribe("MENU_READY", function(component) {
						component.activate($(".workbench"));
						var theme = 'metro';
						$(".navigationBar").jqxExpander({ width: '100%', height:'auto', theme: theme });
					});

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
