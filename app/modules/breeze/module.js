define(['plugins/router', 'durandal/app'], function (router, app) {

    return {
        mainmenu : function() {
			// Add menu items to the Main Menu
			app.trigger('MAINMENU_READY', {label:"Breeze",id:"/module/breeze"});
			app.trigger("MAINMENU_READY", {label:"Test",id:"/component/breeze/test",parent:"/module/breeze" });
        },
         workbench: null
    }

});
