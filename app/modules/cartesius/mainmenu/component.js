define(function(require) {

    // Load the dependencies
    var Boiler = require('Boiler');
        require('/lib/jqwidgets/jqwidgets/jqxmenu.js');

	var Component = function(moduleContext) {
		var vm, panel = null;
		return {
			activate : function(parent) {
				if (!panel) {
					//panel = new Boiler.ViewTemplate(parent, template);

					var context = new Boiler.Context(parent);

					var MenuItem = function (item, items) {
						this.label = item.label;
						this.id = item.id;
						this.img = item.img;
						this.subMenuWidth = "75px";
						this.items = items != undefined ? ko.observableArray(items) : ko.observableArray();
						this.visible = ko.observable(true);

						this.addSubItem = function (newitem) {
							this.items.push(new MenuItem(newitem));
						}
					}
					
					var viewModel = {
						menu : ko.observableArray([new MenuItem({label:"Home", id:"/"})])
					};

					var theme = 'metro';

					//$("#mainMenu").jqxMenu({ source: viewModel.menu, height: '32px', theme: theme });
					$("#mainMenu").css('visibility', 'visible');

					ko.applyBindings(viewModel, $("#mainMenu").get(0));
					
					ko.postbox.subscribe("MAINMENU_READY", function(menuitem) {
						//[label, id, img, parent]
						if(menuitem.parent) {
							ko.utils.arrayForEach(viewModel.menu(), function(menu) {
								if(menu.id == menuitem.parent) {
									menu.addSubItem(new MenuItem(menuitem));
								}
							});
						} else {
							viewModel.menu.push(new MenuItem(menuitem));
						}
						
					});
					
					$("#mainMenu").bind('itemclick', function (event) {
						window.location = '#'+event.args.id;
						console.log(event.args.id);
					});

					
				}
				//panel.show();
			},

			deactivate : function() {
				if (panel) {
					panel.hide();
				}
			}
		};
	};

	return Component;

});
