/* global Box, clearTimeout */
Box.Application.addModule('city-select', function (context) {
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
	var citiesArr;
	var isCitySelected = false;
	var cookieService;
	var DATA_KEY = 'city_select_data';
	var remoteURL;
	var showCityRequestPopover;
	var writedCookies;
	var citySelect;
	var popoverShowTimer;

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
			// temporarily disable select with sending to server
			// now only get city by ip with a Yandex API
			// Hide select with CSS & display only city title

			// services
			selectService = context.getService('select');
			popoverService = context.getService('popover');
			cookieService = context.getService('cookies');
			// elements
			moduleEl = context.getElement();
			select = $(moduleEl).find('select');
			popoverContent = '<p>Мы угадали Ваш город?</p><ul><li><a href="#" data-type="select-trigger">Выбрать другой</a></li><li><a href="#" data-type="popover-trigger">Да, угадали</a></li></ul>';
			// configs
			remoteURL = context.getConfig('url');

			// ymaps.geolocation.city

			window.onload = function () {
				$(moduleEl).append( ymaps.geolocation.city )
			}

			citiesArr = context.getConfig('cities');
			showCityRequestPopover = context.getConfig('showCityRequestPopover') || false;
			writedCookies = cookieService.get(DATA_KEY);

			citySelect = selectService.create(select, {
				data: citiesArr,
				templateSelection: updateSelectionHTML,
				theme: 'city-select'
			});

			select.on('select2:opening', function () {
				popover.hide();
				clearTimeout(popoverShowTimer);
			}).on('select2:select', function (event) {
				cookieService.set(DATA_KEY, event.params.data.text, {
					expires: 365
				});
				isCitySelected = true;

				// send form to server
				$('#city-select-value').val(event.params.data.id);
				$('#city-select-form').submit();
			}).on('select2:close', function () {
				if (!isCitySelected) {
					popoverShowTimer = setTimeout(function () {
						popover.show();
					}, 300);
				}
			});
			popover = popoverService.create(select, {
				content: popoverContent,
				trigger: 'manual',
				placement: 'bottom',
				html: true
			});
			if (typeof cookieService.get(DATA_KEY) === 'string' || showCityRequestPopover === false) {
				isCitySelected = true;
			} else {
				popoverShowTimer = setTimeout(function () {
					popover.show();
				}, 3000);
			}
		},
		destroy: function () {
			moduleEl = null;
			select = null;
			selectService = null;
			popover = null;
			showCityRequestPopover = null;
			popoverService = null;
			popoverContent = null;
			citiesArr = null;
			cookieService = null;
			remoteURL = null;
			writedCookies = null;
			citySelect = null;
			clearTimeout(popoverShowTimer);
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'select-trigger') {
				event.preventDefault();
				popover.hide();
				citySelect.open();
			} else if (elementType === 'popover-trigger') {
				event.preventDefault();
				var selectedCity = select.find('option[value="' + select.val() + '"]').text();
				if (typeof selectedCity === 'string' && typeof writedCookies !== 'string') {
					cookieService.set(DATA_KEY, selectedCity, {
						expires: 365
					});
					clearTimeout(popoverShowTimer);
				}
				popover.hide();
			}
		}

	};
});
