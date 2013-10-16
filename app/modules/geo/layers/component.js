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
        require("/scripts/jqxlistbox.extn.js");

        
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
				
				$("#layersListbox").jqxListBox(
				{
					width: '99%',
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
						//var items = $("#layersListbox").jqxListBox('getCheckedItems'); 

						if (args.checked) {
							console.log("Checked: " + vm.sltCount());
						}
						else {
							console.log("Unchecked: " + vm.sltCount());
						}
					});

				});	
				
			$("#addLayer").jqxButton({ theme: theme });
            $("#addLayer").click(function () {
                alert("Value: " + vm.sltCount());
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
		ko.postbox.publish("MENU_READY", me);

	};

	return Component;

});
