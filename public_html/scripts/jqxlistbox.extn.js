(function ($, ko) {
	ko.jqwidgets = ko.jqwidgets || {};
	ko.jqwidgets.dataBinding = function (settings) {
		var me = this;
		var binding = {},
		name = settings.name;
		var updating = false;
		var updatingFromObservable = false;
		binding.init = function (element, valueAccessor, allBindingAccessor, viewModel) {
			var unwrappedValue = ko.utils.unwrapObservable(valueAccessor());
			var modelOptions = ko.toJS(unwrappedValue);
			widget = $.data(element)[name].instance;
			if (settings.events) {
				$.each(settings.events, function () {
					var eventName = this;
					$(element).bind(eventName + '.' + element.id, function (event) {
						if (!updatingFromObservable) {
							updating = true;
							var val = valueAccessor();
							var property = settings.getProperty(widget, event, eventName);
							if (val[property.name] && $.isFunction(val[property.name])) {
								val[property.name](property.value);
							}
							else if (val[property.name]) {
								valueAccessor(property.value);
							}
							updating = false;
						}
					});
				});
			}
		};
		binding.update = function (element, valueAccessor, allBindingAccessor, viewModel) {
			var unwrappedValue = ko.utils.unwrapObservable(valueAccessor());
			var modelOptions = ko.toJS(unwrappedValue);
			widget = $.data(element)[name].instance;
			if (updating)
				return;
			$.each(settings, function (name, value) {
				if (modelOptions[name]) {
					if (!updating) {
						updatingFromObservable = true;
						settings.setProperty(widget, name, widget[name], modelOptions[name]);
						updatingFromObservable = false;
					}
				}
			});
		};
		ko.bindingHandlers[settings.name] = binding;
	};
	ko.jqwidgets.dataBinding = new ko.jqwidgets.dataBinding({
		name: "jqxListBox",
		checkboxes: false,
		events: ['select'],
		selectedItemsCount: 0,
		getProperty: function (object, event, eventName) {
			if (eventName == 'select') {
				// update the selectedItemsCount when the selection is changed.
				return { name: "selectedItemsCount", value: object.getSelectedItems().length };
			}
		},
		setProperty: function (object, key, value, newValue) {
			if (key == 'checkboxes') {
				object.checkboxes = newValue;
				object.refresh();
			}
		}
	});
} (jQuery, ko));
