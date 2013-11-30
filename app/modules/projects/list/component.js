define(['plugins/router',
		'durandal/app',
        'config',		
		'services/datacontext'],
	function (router, app, config, datacontext) {

	var results;
	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/projects/list/view',
		
		activate : function () {
			self = this;
						
			var query = new breeze.EntityQuery()
				.from("Project");

			self.listProjectsDataSource = function (widget, options) {
				widget.setDataSource(new kendo.data.extensions.BreezeDataSource({
					entityManager: datacontext.manager,
					endPoint: query,
					mapping: {
						ignore: ['project_author','team']
					},
					onFail: function(error) {
						console.log(error);
						}

					})
				);
			};
			
			self.editProject = function(e) {
				alert(e);
			}

			self.projectGrid = {
				columns: [
					{ field: 'title', title: 'Name'},
					{ field: 'date', title: 'Start Date', format: '{0: dd-MM-yyyy}'},
					{ field: 'review_date', title: 'Review Date', format: '{0: dd-MM-yyyy}'},
					{ field: 'summary', title: 'Summary'},
					
					{ command: [
							{
							name: 'edit',
							click: function(e) {
								var tr = $(e.target).closest("tr");
								var data = this.dataItem(tr);
								router.navigate('#/component/projects/edit?id='+data.id);
								return false;
							}
						} , {
							name: 'View',
							click: function(e) {
								var tr = $(e.target).closest("tr");
								var data = this.dataItem(tr);
								router.navigate('#/component/projects/edit?id='+data.id);
								return false;
							}
						}
					]}
				],
				pageable: { pageSize: 10 },
				sortable: true,
				data: self.listProjectsDataSource
			};

    
		},
	
		attached: function () {
			
		},
		
		compositionComplete: function(view, parent) {
			$(".k-grid-View").find("span").addClass("k-icon k-i-search");
			
		}

	}

});
