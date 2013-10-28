define(function(require) {
    // Load the dependencies
    var Boiler = require('Boiler');

    var ViewModel = function (moduleContext) {
        var self = this;
        
        this.people = ko.observableArray();
         
        $.getJSON('/model/people/preferences/{}/json', function (result) {
            self.preferences(result);
        });
    };
    return ViewModel;
});
