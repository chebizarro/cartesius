define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler'), 
        WorkBenchComponent = require('/component/cartesius/workbench'),
        PreferencesComponent = require('/component/cartesius/preferences'),
        MainMenuComponent = require('/component/cartesius/mainmenu');

        //LanguageComponent = require('/component/cartesius/language'), 
        //ThemeComponent = require('/component/cartesius/theme'), 

    return {
        
        initialize : function(parentContext) {
            var context = new Boiler.Context(parentContext);

			var preferences = new PreferencesComponent(context);
			preferences.initialise();

			var workbench = new WorkBenchComponent(context);
			workbench.activate($(".workbench"));

			var mainmenu = new MainMenuComponent(context);
			mainmenu.activate();

			var theme = 'metro';
			$('#mainSplitter').jqxSplitter({ width: '100%', height: '100%', orientation: 'horizontal', theme: theme, panels: [{ size: 34 }, { size: 300}] });
			$('#workbenchSplitter').jqxSplitter({ width: '100%', height: '100%', theme: theme, panels: [{ size: 300 }, { size: 300}] });
            
            ko.postbox.subscribe("MAINFRAME_READY", function(component) {
					//component.activate($(".appcontent"));
			});
            
        }
        
    }

});
