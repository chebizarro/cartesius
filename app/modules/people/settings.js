define(function(require) {
    
    //load dependencies
    var serverPath = require('path!/model/people/');
    
    return {
        urls: {
            empimages: serverPath + "{empid}.png",
            empdetails: serverPath + "{empid}.txt",
            yearlysales: serverPath + "yearly-sales.txt",
            people: serverPath + "peopleList/JSON"
        }
    };
});
