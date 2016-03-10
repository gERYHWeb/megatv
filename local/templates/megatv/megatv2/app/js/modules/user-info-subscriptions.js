/* global Box */
Box.Application.addModule('user-info-subscriptions', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	// var $ = context.getGlobal('jQuery');
	var moduleEl;
	var remoteUrl;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			remoteUrl = context.getConfig('url');
		},
		destroy: function () {
			moduleEl = null;
			remoteUrl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'info-subscription-item') {
				event.preventDefault();
				// var subscriptionID = $(event.target).closest('li').data('subscription-id');
				// var status = $(event.target).closest('li').hasClass('status-active') ? 'disable' : 'enable';

				// $.get(remoteUrl, {
				// 	subscriptionID: subscriptionID,
				// 	status: status
				// }, function (data) {
				// 	var element = $(event.target).closest('li');
				// 	if (data.status === 'enable') {
				// 		element.addClass('status-active');
				// 	} else if (data.status === 'disable') {
				// 		element.removeClass('status-active');
				// 	}
				// }, 'json').fail(function () {
				// 	alert('Что-то пошло не так. Повторите попытку позднее!');
				// });
			}
		}

	};
});
