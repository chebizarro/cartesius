define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'),
        template = require('text!../../view/people/peopleList/view'),
        ViewModel = require('/viewmodel/people/peopleList');
        require("/lib/jqwidgets/jqwidgets/jqxlistbox.js");
        require("/lib/jqwidgets/jqwidgets/jqxdata.js");
        require("/lib/jqwidgets/jqwidgets/jqxbuttons.js");
        require("/lib/jqwidgets/jqwidgets/jqxscrollbar.js");

        
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
                    { name: 'email' },
                    { name: 'username' },
                ],
                id: 'id',
                localdata: vm.people
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#peopleListbox").jqxListBox(
            {
                width: '99%',
                autoHeight: true,
                selectedIndex: -1,
                multipleextended: false,
                source: dataAdapter,
                theme: theme,
                displayMember: 'username',
                valueMember: 'email',
                equalItemsWidth:true
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
		var me = this;
		ko.postbox.publish("WORKBENCH_READY", me);

	};

	return Component;

});
