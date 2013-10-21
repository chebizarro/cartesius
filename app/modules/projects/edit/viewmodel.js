define(function(require) {
    var Boiler = require('Boiler');

    var ViewModel = function (moduleContext) {
        self = this;
        
		// project table
        self.title = ko.observable();
		self.authors = ko.observableArray();
		self.date = ko.observable();
		self.reviewdate = ko.observable();
		self.summary = ko.observable();
 
        $.getJSON('/model/people/peopleList/{}/json', function (result) {
            self.authors(result);
        });
        
        // team table
        self.teams = ko.observableArray([{name:"test", teammember: [{name: "test"}]},{name:"test2", teammember: [{name: "test3"}]}]);
        

    };
    return ViewModel;
});
