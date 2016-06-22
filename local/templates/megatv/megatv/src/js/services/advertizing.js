/* global Box */
Box.Application.addService('advertizing', function (aplication) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = aplication.getGlobal('jQuery');
	var cookieService = aplication.getService('cookies');
	var DATA_KEY = 'advertizing_hidden_banners';

	function checkBannerVisibility(target) {
		var elementID = $(target).attr('id');

		return $.inArray(elementID, cookieService.getJSON(DATA_KEY)) === -1 ? false : true;
	}
	function setHiddenInCookies(target, bannersHideTime) {
		var elementID = $(target).attr('id');
		var array = cookieService.getJSON(DATA_KEY) || [];

		if ($.inArray(elementID, array) === -1) {
			array.push(elementID);
			cookieService.set(DATA_KEY, array, { expires: bannersHideTime });
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		show: function (target) {
			$(target).removeClass('hide');
		},
		hide: function (target, bannersHideTime) {
			$(target).addClass('hide');
			setHiddenInCookies(target, bannersHideTime);
		},
		isHidden: function (target) {
			return checkBannerVisibility(target);
		}

	};

});
