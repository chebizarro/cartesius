define(function(require) {
    // Load the dependencies
    var Boiler = require('Boiler');

    var ViewModel = function (moduleContext) {
        var self = this;
        
        this.people = ko.observableArray();
         
        $.getJSON('/model/people/peopleList/{}/json', function (result) {
            self.people(result);
        });
    };
    return ViewModel;
});
