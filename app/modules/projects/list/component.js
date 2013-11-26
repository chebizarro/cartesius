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
			
			self.editProject = function(e) {
				alert(e);
			}

			self.projectGrid = {
				columns: [
					{ field: 'title', title: 'Name'},
					{ command: [{ name: 'edit',
							click: function(e) {
								var tr = $(e.target).closest("tr");
								var data = this.dataItem(tr);
								router.navigate('#/component/projects/edit?id='+data.id);
								return false;
							}
						}]
					}
				],
				pageable: { pageSize: 10 },
				sortable: true,
				data: self.listProjectsDataSource
			};
			
                
		},
	
		attached: function () {
			

		}	

	}

});
