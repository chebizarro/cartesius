define(function(require) {
    var Boiler = require('Boiler');

    var ViewModel = function (moduleContext) {
        self = this;
        
		// project table
		self.id  = ko.observable();
        self.title = ko.observable();
		self.date = ko.observable();
		self.review_date = ko.observable();
		self.summary = ko.observable();
		
		self.authors = ko.observableArray([{id:4}]);
		        
		// team_member table
		// id
		// team_id
		// role_id
        self.teams = ko.observableArray();

		// team_role table
		// id
		// name
		// description
		
		// itinerary table
		// id
		// team_id
		// project_id
		
		self.saveProjectInfo = function() {
			//console.log(ko.toJSON(self));
			$.post( "/save/projects", ko.toJSON(self));
			return true;
		};
		
		self.setAuthors = function() {
			
		};
    };
    
    var ProjectAuthor = function() {
		// project_authors table
		self = this;
		self.id = ko.observable();
		self.account_id = ko.observable();
	};
    
    var ProjectTeam = function(team) {
        // team table
		self = this;
		self.id = ko.observable(team.id);
        self.name = ko.observable(team.name);
        self.description = ko.observable(team.description);
        self.project_id= ko.observable(team.project_id);
	};
	
	var ProjectItineraryItem = function(itinerary) {
		// itinerary_item table
		self = this;
		self.id = ko.observable();
		self.itinerary_id = ko.observable();
		self.description = ko.observable();
		self.start = ko.observable();
		self.finish = ko.observable();
		self.start_location = ko.observable();
		self.finish_location = ko.observable();
		self.transportation_id = ko.observable();

	};
	
    return ViewModel;
});
