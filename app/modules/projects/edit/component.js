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
				
				$('#editProjectTabs').jqxTabs({ position: 'top', theme: theme, disabled: false });
				
				// Tab 0 Project Info
				//Set up author source
				var source =
				{
					datatype: 'json',
					datafields: [
						{ name: 'email' },
						{ name: 'username' },
						{ name: 'id' }
					],
					id: 'id',
					url: '/model/people/peopleList/{}/json',
                    async: false
					//localdata: vm.authors_select
				};
				var dataAdapter = new $.jqx.dataAdapter(source);

				$("#projectAuthors").jqxDropDownList({
					source: dataAdapter,
					theme: theme,
					autoDropDownHeight: true,
					displayMember: 'username',
					valueMember: 'id',
					checkboxes: true
				});
				
				//$("#projectAuthors").on('bindingComplete', function (event) {
					ko.utils.arrayForEach(vm.authors(), function(author) {
						console.log(author);
						var item = $("#projectAuthors").jqxDropDownList('getItemByValue', author.id);
						if(item) {
							$("#projectAuthors").jqxDropDownList('checkItem', item );
						}
					});
				//});
				
				$("#projectAuthors").on('checkChange', function (event) {
                    if (event.args) {
                        var item = event.args.item;
                        if (item) {
							if (item.checked) {
								vm.authors.push({id : item.value});
							} else {
								vm.authors.remove(function(index) { return index.id == item.value });
							}
                        }
                    }
                });

				
				$("#projectInfoNext").jqxButton({ width: '55', theme: theme });
				$("#projectInfoNext").on('click', function () {
					if(vm.saveProjectInfo()) {
						// yay!
					}
					
				});

				// Tab 1 Team info

				
				// Apply bindings
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
