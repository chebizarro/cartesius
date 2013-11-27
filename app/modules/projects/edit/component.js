define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {
	
    return {
		cacheViews:false,
		//alwaysTriggerAttach:true,
		
		viewUrl: '/view/projects/edit/view',
		
		activate : function (modulename, project) {
			self = this;
			var loadProject = function(project) { 
				if(project) {
					var query = new breeze.EntityQuery()
						.from("Project")
						.where("id","eq",project.id)
						.expand("ProjectAuthor");
					return datacontext.manager.executeQuery(query).then(function(data){
						console.log(ko.toJS(data.results[0]));
						self.data = data.results[0];
					}).fail(function(e) {
						console.log(e);							  
					});
				} else {
					return self.data = datacontext.manager.createEntity('Project');
				}
			}
			return  loadProject(project);

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

