define(['plugins/router', 'durandal/app'], function (router, app) {

    return {
		router: router,
		viewUrl: '/view/cartesius/mainmenu/view',
        activate: function () {
				
		},
		attached: function () {
			
			$("#mainmenu").kendoMenu();
			var menu = $("#mainmenu").data("kendoMenu");
						
			ko.utils.arrayForEach(app.modules(), function(item) {
				if(item.mainmenu) {
					menu.append(item.mainmenu);
				}
			});
		},
	}
});
