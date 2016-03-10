/* global Box, alert */
Box.Application.addModule('user-subscription-channels', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
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
			if (elementType === 'channel-item') {
				var channelID = $(event.target).closest('.item').data('channel-id');
				var status = $(event.target).closest('.item').hasClass('status-active') ? 'disable' : 'enable';

				$.get(remoteUrl, {
					channelID: channelID,
					status: status
				}, function (data) {
					var element = $(event.target).closest('.item');
					if (data.status === 'enable') {
						element.addClass('status-active');
					} else if (data.status === 'disable') {
						element.removeClass('status-active');
					}
				}, 'json').fail(function () {
					alert('Что-то пошло не так. Повторите попытку позднее!');
				});
			}
		}

	};
});
