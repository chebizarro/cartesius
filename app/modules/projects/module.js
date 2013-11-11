define(['plugins/router', 'durandal/app'], function (router, app) {


    return {
		
		workbench: {title:"Projects", path:"/component/projects/workbench"},
		
		mainmenu: [{
					text: "Projects",
					imageUrl: "/images/icons/projects.png",
					items: [{
							text: "List",
							url: "#/component/projects/list"
							},
							{
							text: "New",
							url: "#/component/projects/edit"
							}]
					}]
    }

});
