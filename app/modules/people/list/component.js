define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {

	var results;
	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/people/list/view',
		
		activate : function () {
			self = this;
			
			self.listPeopleDataSource = function (widget, options) {
				widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
					entityManager: datacontext.manager,
					endPoint: "Account",
					defaultSort: "username asc",
					mapping: {
						ignore: ['project_author','team_member']
					},
					onFail: function(error) {
						console.log(error);
						}

					})
				);
			};
                
		}

	}

});
