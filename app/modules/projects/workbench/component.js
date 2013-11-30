define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {

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
							entityManager: datacontext.manager,
							endPoint: new breeze.EntityQuery.from("Project"),
							mapping: {
								ignore: ['project_author','team']
							}
						})
					);
				} catch (e) {
					console.log(e);
				} finally {
					return true;
				}
			};   

                
		},
	
		attached: function () {
			

		}	

	}

});
