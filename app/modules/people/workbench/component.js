define(['plugins/router', 'durandal/app'], function (router, app) {

	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/people/workbench/view',
		
		activate : function () {
			self = this;
						
			 self.listPeopleDataSource = function (widget, options) {
				 try {
					widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
							entityManager: app.dataservice,
							endPoint: new breeze.EntityQuery.from("Account"),
							defaultSort: "username asc"
						})
					);
				} catch (e) {
					console.log(e);
				}
			};         
		}

	}

});
