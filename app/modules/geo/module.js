define(['plugins/router', 'durandal/app'], function (router, app) {

    return {
        
        mainmenu : [{
            text: "Layers",
			imageUrl: "/images/icons/layers.png"
        }],
                
        workbench: {title:"Layers",path:"/component/geo/layers"}
    }

});
