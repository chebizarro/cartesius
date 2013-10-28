define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/projects/list/view'),
        ViewModel = require('/viewmodel/projects/list');
        require("/lib/jqwidgets/jqwidgets/jqxlistbox.js");
        require("/lib/jqwidgets/jqwidgets/jqxdata.js");
        require("/lib/jqwidgets/jqwidgets/jqxbuttons.js");
        require("/lib/jqwidgets/jqwidgets/jqxscrollbar.js");
        require("/lib/jqwidgets/jqwidgets/jqxcheckbox.js");
        require("/scripts/jqx.extn.js");

        
	var Component = function(moduleContext) {

		var vm, panel = null;

		this.activate = function (parent, params) {
			var theme = "metro";
		    if (!panel) {
		        vm = new ViewModel(moduleContext);
		        panel = new Boiler.ViewTemplate(parent, template);

				var source =
				{
					datatype: "observablearray",
					datafields: [
						{ name: 'id' },
						{ name: 'title' },
						{ name: 'description' },
						{ name: 'alias'},
						{ name: 'last_update' },
						{ name: 'owner_id' },
					],
					id: 'id',
					localdata: vm.layers
				};
				
				
				$("#projectsListbox").jqxListBox(
				{
					width: '99%',
					checkboxes: true,
					autoHeight: true,
					theme: theme,
					displayMember: 'title',
					valueMember: 'id',
					multiple: true
				});
				
				ko.applyBindings(vm, panel.getDomElement());

				var dataAdapter = new $.jqx.dataAdapter(source);

				$("#projectsListbox").jqxListBox({ source: dataAdapter });

			
				$("#projectsListbox").on('bindingComplete', function (event) {
					$.each(vm.prefs.visibleLayers, function(index, value) {
						var item = $("#projectsListbox").jqxListBox('getItemByValue', value);
						$("#projectsListbox").jqxListBox('checkItem', item );
					});
										
					
					$("#layersListbox").on('checkChange', function (event) {
						var args = event.args;

						if (args.checked) {
							console.log(vm.checked());
						}
						else {
							//console.log("Unchecked: " + vm.sltCount());
						}
					});

				});	
							
			
		    }
		    panel.show();
		};
		
		this.deactivate = function () {
		    if (panel) {
		        panel.hide();
		    }
		};
		var me = this;
		ko.postbox.publish("WORKBENCH_READY", me);

	};

	return Component;

});
