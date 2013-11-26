define(['plugins/router', 'durandal/app'], function (router, app) {
	
    return {
		viewUrl: '/view/projects/edit/view',
		
		activate : function (modulename, project) {
			self = this;

			self.data = null;


			if(project) {
				
				var query = new breeze.EntityQuery()
					.from("Project")
					.where("id","eq",project.id);
					//.expand("ProjectAuthor");
					
				app.dataservice.executeQuery(query).then(function(data){
					console.log(data);
					self.data = data.results[0];
				}).fail(function(e) {
					console.log(e);							  
				});
			} else {
				self.data = app.dataservice.createEntity('Project');
			}

/*
			self.listPeopleDataSource = function (widget, options) {
				widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
					entityManager: app.dataservice,
					endPoint: "Account",
					onFail: function(error) {
						console.log(error);
						}

					})
				);
			};
*/              
		},
	
		attached: function () {
			

		}	

	}

});

