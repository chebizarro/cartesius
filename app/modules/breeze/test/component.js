define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/breeze/test/view'),
        ViewModel = require('/viewmodel/breeze/test');


	require('/lib/breeze/Scripts/breeze.debug.js');

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

				$("#projectInfoNext").jqxButton({ width: '55', theme: theme });
				$("#projectInfoNext").on('click', function () {
						saveChanges();
					});


				var manager = new breeze.EntityManager('/data/');

				
				var query = new breeze.EntityQuery()
					.from("Project");
				
				    if (manager.metadataStore.isEmpty()) {
						return manager.fetchMetadata()
								.then(function (rawMetadata) {
									return executeQuery();
						}).fail(function(e) {
									console.log(e);
								});
					} else {
						return executeQuery();
					}
				
				
				
				function executeQuery() {
				
					manager.executeQuery(query).then(function(data){
						if(data.results.length > 0) {
							ko.applyBindings(data[0], panel.getDomElement());
						} else {
							console.log("No results");
							var newProj = manager.createEntity('Project');
							console.log(newProj);
							ko.applyBindings(newProj, panel.getDomElement());
						}
					}).fail(function(e) {
						console.log(e);
						  
					});
				}
				
				var saveSucceeded = function() {
					console.log("Save succeeded");
				}

				var saveFailed = function() {
					console.log("Save failed");
				}
				
				function saveChanges() {
					if (manager.hasChanges()) {
						manager.saveChanges()
							.then(saveSucceeded)
							.fail(saveFailed);
					} else {
						console.log("Nothing to save");
					}
				};
				
				
				
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
