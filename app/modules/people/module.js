define(['plugins/router', 'durandal/app'], function (router, app) {


    return {
        mainmenu : [{
					text: "People",
					imageUrl: "/images/icons/people.png",
					items: [{
							text: "List",
							url: "#/component/people/list"
							}]
					}],
        
        workbench: {title:"People", path:"/component/people/workbench"}

    }

});
