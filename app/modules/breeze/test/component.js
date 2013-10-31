define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/breeze/test/view'),
        ViewModel = require('/viewmodel/breeze/test');


	require('/lib/breeze/Scripts/breeze.min.js');

    require('/lib/jqwidgets/jqwidgets/jqxtabs.js');
    require('/lib/jqwidgets/jqwidgets/jqxbuttons.js');
    require('/lib/jqwidgets/jqwidgets/jqxinput.js');
    require('/lib/jqwidgets/jqwidgets/jqxdatetimeinput.js');
    require('/lib/jqwidgets/jqwidgets/jqxdropdownlist.js');
    require('/lib/jqwidgets/jqwidgets/jqxcalendar.js');
        
	var Component = function(moduleContext) {

		var vm, panel = null;

		this.activate = function (parent, params) {
			
			var theme = "metro";
		    if (!panel) {
			
				panel = new Boiler.ViewTemplate(parent, template);

				var manager = new breeze.EntityManager('/data/');

				var query = new breeze.EntityQuery()
					.from("Account");
					
				manager.executeQuery(query).then(function(data){
					ko.applyBindings(data, panel.getDomElement());
				}).fail(function(e) {
					alert(e);  
				});
							
		    }
		    panel.show();
		};
		
		this.deactivate = function () {
		    if (panel) {
		        panel.hide();
		    }
		};

	};

	return Component;

});
