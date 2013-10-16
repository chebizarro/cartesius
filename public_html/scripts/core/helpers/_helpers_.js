define(function(require) {

	/**
	 *Namespace variable defining helper classes mainly used by the core classes in 'Boiler' namespace.
	
	 @type Script
	 @namespace Boiler.Helpers
	 @module BoilerCoreClasses
	 @main BoilerCoreClasses
	**/
	return {
		Localizer : require("./localizer"),
		Localizer : require("./xsl-localizer"),
		Logger : require("./logger"),
		Mediator : require("./mediator"),
		Router : require("./router"),
		Settings : require("./settings"),
		Styler : require("./styler")
	};
}); 
