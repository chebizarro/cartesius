define(function () {
    //toastr.options.timeOut = 4000;
    //toastr.options.positionClass = 'toast-bottom-right';

    var imageSettings = {
        imageBasePath: '/images/',
        unknownPersonImageSource: 'unknown_person.jpg'
    };

    var remoteServiceName = '/webapi/cartesius/';

    var appTitle = 'Cartesius';
    
    var startModule = '/component/geo/map';

	ko.kendo.setDataSource = function (widget, fnCall, options) {
		fnCall(widget, options)
	};

    return {
        appTitle: appTitle,
        debugEnabled: ko.observable(true),
        imageSettings: imageSettings,
        remoteServiceName: remoteServiceName,
        startModule: startModule
    };
});
