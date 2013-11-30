define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {

	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/people/workbench/view',
		
		activate : function () {
			self = this;
						
			 self.listPeopleDataSource = function (widget, options) {
				 try {
					widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
							entityManager: datacontext.manager,
							endPoint: new breeze.EntityQuery.from("Account"),
							defaultSort: "username asc",
							mapping: {
								ignore: ['project_author','team_member']
							}

						})
					);
				} catch (e) {
					console.log(e);
				}
			};         
		}

	}

});
