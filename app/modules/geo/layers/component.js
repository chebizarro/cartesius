define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/geo/layers/view'),
        ViewModel = require('/viewmodel/geo/layers');
        require("/lib/jqwidgets/jqwidgets/jqxlistbox.js");
        require("/lib/jqwidgets/jqwidgets/jqxdata.js");
        require("/lib/jqwidgets/jqwidgets/jqxbuttons.js");
        require("/lib/jqwidgets/jqwidgets/jqxscrollbar.js");
        require("/lib/jqwidgets/jqwidgets/jqxcheckbox.js");
        require("/scripts/jqx.extn.js");

        
	var Component = function(moduleContext) {

		var vm, panel = null;

		this.activate = function (parent, params) {

			ko.postbox.publish("MAINMENU_READY", {label :"Layers", id: "mainmenu.geo.layers", parent: "mainmenu.geo"});

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
				
				ko.jqwidgets.dataBinding = new ko.jqwidgets.dataBinding({
					name: "jqxListBox",
					events: ['checkChange'],
					getProperty: function (object, event, eventName) {
						if (eventName == 'checkChange') {
							// update the selectedItemsCount when the selection is changed.
							return { name: "value", value: object.getCheckedItems().length };
						}
					}
				});
				
				$("#layersListbox").jqxListBox(
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

				$("#layersListbox").jqxListBox({ source: dataAdapter });

			
				$("#layersListbox").on('bindingComplete', function (event) {
					$.each(vm.prefs.visibleLayers, function(index, value) {
						var item = $("#layersListbox").jqxListBox('getItemByValue', value);
						$("#layersListbox").jqxListBox('checkItem', item );
					});
					
					$('#layersNavigationBar').jqxExpander({expanded: vm.prefs.panelOpen });
				
					$('#layersNavigationBar').on('collapsed', function () {
						vm.prefs.panelOpen = false;
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
