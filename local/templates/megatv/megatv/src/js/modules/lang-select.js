/* global Box, clearTimeout */
Box.Application.addModule('lang-select', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var select;
	var popover;
	var selectService;
	var popoverService;
	var popoverContent;
	var languagesArr;
	var isLangSelected = false;
	var remoteURL;
	var langSelect;

	function updateSelectionHTML(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $('<span>' + state.text + '</span>');
		return $state;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {

			// services
			selectService = context.getService('select');

			// elements
			moduleEl = context.getElement();
			select = $(moduleEl).find('select');

			// configs
			remoteURL = context.getConfig('url');
			languagesArr = context.getConfig('languages');

			langSelect = selectService.create(select, {
				data: languagesArr,
				templateSelection: updateSelectionHTML,
				theme: 'lang-select'
			});

			select.on('select2:select', function (event) {
				isLangSelected = true;

				// send form to server
				$('#lang-select-value').val(event.params.data.id);
				$('#lang-select-form').submit();
			});
		},
		destroy: function () {
			moduleEl = null;
			select = null;
			selectService = null;
			popover = null;
			popoverService = null;
			popoverContent = null;
			languagesArr = null;
			remoteURL = null;
			langSelect = null;
		}
	};
});
