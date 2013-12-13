define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {
	
	var self = this;

	
    return {
		cacheViews:false,
		//alwaysTriggerAttach:true,
		
		viewUrl: '/view/projects/edit/view',
		
		activate : function (modulename, project) {

			self.datacontext = datacontext;


			self.listPeopleDataSource = function (widget, options) {
				widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
					entityManager: self.datacontext.manager,
					endPoint: "Account",
					mapping: {
						ignore: ['ProjectAuthor','TeamMember'] // category.products is recursive - ignore it
					},

					onFail: function(error) {
						console.log(error);
						}

					})
				);
			};


			var loadProject = function(project) {				
				
				if(project) {
					var query = new breeze.EntityQuery()
						.from("Project")
						.where("id","eq",project.id)
						.expand("ProjectAuthor, Team");
					return datacontext.manager.executeQuery(query)
							.then(function(data){
								//console.log(data);
								self.data = data.results[0];
							})
							.fail(function(e) {
								console.log(e);							  
							});
				} else {
					return self.data = datacontext.manager.createEntity('Project');
				}
			}
			return  loadProject(project);

		},
	
		attached: function () {			
			var projectAuthors = $("#projectAuthors").data("kendoMultiSelect");
			var authors = [];
			
			//console.log(self.data)
			
			ko.utils.arrayForEach(self.data.ProjectAuthor, function(item) {
				authors.push(item.account_id);
				consoler.log(item.account_id());
			});

			projectAuthors.value(authors);

			//projectAuthors.bind("change", multiselect_change);


		}
		
	}

});

