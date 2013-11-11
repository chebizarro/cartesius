define(['plugins/router', 'durandal/app'], function (router, app) {

	return {
		viewUrl: '/view/cartesius/workbench/view',
		activate: function () {
			self = this;
			self.modules = app.modules;
			
		},
		attached: function () {
		}
	}

});
