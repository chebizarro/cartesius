define(function(require) {
    var Boiler = require('Boiler');

    var ViewModel = function (moduleContext) {
                
        this.title = ko.observable();
		this.authors = ko.observableArray();
		this.date = ko.observable();
		this.reviewdate = ko.observable();
		this.summary = ko.observable();
        
        self = this;
        
        $.getJSON('/model/people/peopleList/{}/json', function (result) {
            self.authors(result);
        });

    };
    return ViewModel;
});
