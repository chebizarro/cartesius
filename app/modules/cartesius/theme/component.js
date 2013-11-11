define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler');
        //template = require('text!./view.html'),
        //Controller = require('./controller');


	var Component = function(moduleContext) {

		var panel = null;

		return {
			activate : function(parent) {
				if (!panel) {
					//create the theme selection component
					panel = new Boiler.ViewTemplate(parent, template, null);
					//create our controller that will handle user events
					
				var source =
                {
                    datatype: "json",
                    datafields: [
                        { name: 'themeName' },
                        { name: 'themePath' }
                    ],
                    url: "/model/cartesius/theme/json",
                    async: false
                };
                
                var dataAdapter = new $.jqx.dataAdapter(source);
                
                // Create a jqxDropDownList
                $("#themeSelector").jqxDropDownList({
                    selectedIndex: 0, source: dataAdapter, displayMember: "themeName", valueMember: "themePath", width: 200, height: 25, theme: theme
                });
                
                // subscribe to the select event.
                $("#themeSelector").on('select', function (event) {
                    if (event.args) {
                        var item = event.args.item;
                        if (item) {

                        }
                    }
                });

					
					new Controller(moduleContext).init();
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
