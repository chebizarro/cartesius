define(['plugins/router', 'durandal/app'], function (router, app) {

	var results;
	var dataAdapter;
	var source;
	
    return {
		viewUrl: '/view/geo/layers/view',
		
		activate : function () {
			/*
			var query = new breeze.EntityQuery()
				.from("Layer");
			
			app.dataservice.executeQuery(query).then(function(data){
				source =
				{
					datatype: "observablearray",
					datafields: [
						{ name: 'title' },
						{ name: 'description' },
					],
					id: 'id',
					localdata: ko.toJS(data.results)
				};
				
				dataAdapter = new $.jqx.dataAdapter(source);
				
				$("#layersListbox").jqxListBox(
				{
					width: '99%',
					autoHeight: true,
					selectedIndex: -1,
					multipleextended: false,
					source: dataAdapter,
					theme: app.theme(),
					displayMember: "username",
					valueMember: "email",
					equalItemsWidth:true
				});

			})
			.fail(function(e) {
				console.log(e);							  
			});
            */
		},
	
		attached: function () {
			

		}	

	}

});

