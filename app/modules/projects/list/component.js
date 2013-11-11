define(['plugins/router', 'durandal/app'], function (router, app) {

	var results;
	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/projects/list/view',
		
		activate : function () {
			self = this;
						
			self.listProjectsDataSource = function (widget, options) {
				widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
					entityManager: app.dataservice,
					endPoint: "Project",
					onFail: function(error) {
						console.log(error);
						}

					})
				);
			};
                
		},
	
		attached: function () {
			

		}	

	}

});
