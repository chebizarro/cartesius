define(function () {
    //toastr.options.timeOut = 4000;
    //toastr.options.positionClass = 'toast-bottom-right';

    var imageSettings = {
        imageBasePath: '/images/',
        unknownPersonImageSource: 'unknown_person.jpg'
    };

    var remoteServiceName = '/webapi/cartesius/';

    var appTitle = 'Cartesius';

    var routes = [{
        url: 'sessions',
        moduleId: 'viewmodels/sessions',
        name: 'Sessions',
        visible: true,
        caption: 'Sessions',
        settings: { caption: '<i class="icon-book"></i> Sessions' }
        }, {
        url: 'speakers',
        moduleId: 'viewmodels/speakers',
        name: 'Speakers',
        caption: 'Speakers',
        visible: true,
        settings: { caption: '<i class="icon-user"></i> Speakers' }
        }, {
        url: 'sessiondetail/:id',
        moduleId: 'viewmodels/sessiondetail',
        name: 'Edit Session',
        caption: 'Edit Session',
        visible: false
    }, {
        url: 'sessionadd',
        moduleId: 'viewmodels/sessionadd',
        name: 'Add Session',
        visible: false,
        caption: 'Add Session',
        settings: { admin: true, caption: '<i class="icon-plus"></i> Add Session' }
    }];
    
    var startModule = '/module/geo';

	ko.kendo.setDataSource = function (widget, fnCall, options) {
		fnCall(widget, options)
	};

    return {
        appTitle: appTitle,
        debugEnabled: ko.observable(true),
        imageSettings: imageSettings,
        remoteServiceName: remoteServiceName,
        routes: routes,
        startModule: startModule
    };
});
