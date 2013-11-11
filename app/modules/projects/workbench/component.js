define(['plugins/router', 'durandal/app'], function (router, app) {

	var results;
	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/projects/workbench/view',
		
		activate : function () {
			self = this;
						
			 self.listProjectsDataSource = function (widget, options) {
				 try {
					widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
							entityManager: app.dataservice,
							endPoint: new breeze.EntityQuery.from("Project")
						})
					);
				} catch (e) {
					console.log(e);
				}
			};         

                
		},
	
		attached: function () {
			

		}	

	}

});
