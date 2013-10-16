define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'), 
        WorkBenchComponent = require('/component/cartesius/workbench'),
        PreferencesComponent = require('/component/cartesius/preferences');
        //LanguageComponent = require('/component/cartesius/language'), 
        //ThemeComponent = require('/component/cartesius/theme'), 

    // Definition of the base Module as an object, this is the return value of this AMD script
    return {
        
        initialize : function(parentContext) {
            var context = new Boiler.Context(parentContext);

			var preferences = new PreferencesComponent(context);
			preferences.initialise();

			var workbench = new WorkBenchComponent(context);
			workbench.activate($(".workbench"));
;
            
			var theme = 'metro';
			$('#mainSplitter').jqxSplitter({ width: '100%', height: '100%', orientation: 'horizontal', theme: theme, panels: [{ size: 34 }, { size: 300}] });
			$('#workbenchSplitter').jqxSplitter({ width: '100%', height: '100%', theme: theme, panels: [{ size: 300 }, { size: 300}] });
            
            ko.postbox.subscribe("MAINFRAME_READY", function(component) {
					component.activate($(".appcontent"));
			});

            
        }
        
    }

});
