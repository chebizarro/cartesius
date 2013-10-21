define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler');
        template = require('text!../../view/people/peopleDetails/view');
        //ViewModel = require('./viewmodel'); 
        //styleText = require('text!./style.css');


	var Component = function(moduleContext) {

		var vm, panel = null;

		this.activate = function (parent, params) {
		    if (!panel) {
		        //vm = new ViewModel(moduleContext, params.id);
		        panel = new Boiler.ViewTemplate(parent, template);
		        //ko.applyBindings(vm, panel.getDomElement());
		    }

		    //vm.setEmployee(params.id);
		    panel.show();
		};

		this.deactivate = function() {
			if (panel) {
				panel.hide();
			}
		};
	};

	return Component;

});
