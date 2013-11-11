define(['plugins/router', 'durandal/app'], function (router, app) {

	var results;
	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/people/list/view',
		
		activate : function () {
			self = this;
			
			var query = new breeze.EntityQuery()
				.from("Account");
			
			self.listPeopleDataSource = function (widget, options) {
				widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
					entityManager: app.dataservice,
					endPoint: query,
					defaultSort: "username asc",
					onFail: function(error) {
						console.log(error);
						}

					})
				);
			};
                
		}

	}

});
