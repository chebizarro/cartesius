define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/projects/edit/view'),
        ViewModel = require('/viewmodel/projects/edit');

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
				
		        vm = new ViewModel(moduleContext);
		        
		        panel = new Boiler.ViewTemplate(parent, template);
				
				$('#editProjectTabs').jqxTabs({ position: 'top', theme: theme });
				
				var source =
				{
					datatype: "observablearray",
					datafields: [
						{ name: 'email' },
						{ name: 'username' },
					],
					id: 'id',
					localdata: vm.authors
				};
				var dataAdapter = new $.jqx.dataAdapter(source);

				$("#projectAuthors").jqxDropDownList({
					source: dataAdapter,
					theme: theme,
					autoDropDownHeight: true,
					displayMember: 'username',
					valueMember: 'email',
					checkboxes: true
				});
				
				ko.applyBindings(vm, panel.getDomElement());

			
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
