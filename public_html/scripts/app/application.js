"use strict";// avoid accidental global variable declarations

define(function(require) {
	
	//dependencies
    var Boiler = require("Boiler"), // BoilerplateJS namespace used to access core classes, see above for the definition
        //settings = require("./settings"), //global settings file of the product suite
        modules = require("/modules/modules"); //file where all of your product modules will be listed
		// /modules/modules
	//return an object with the public interface for an 'application' object. Read about module pattern for details.
    return {
        initialize : function() {

            var globalContext = new Boiler.Context();
            //globalContext.addSettings(settings);


            for (var i = 0; i < modules.length; i++) {
                modules[i].initialize(globalContext);
            }

        }
    };
});

