/* global Box */
Box.Application.addModule('page', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var advertService;
	var adverts;
	var bannersHideTime;
	var fillDiskSpace;
	var progressbarService;
	var modalService;
	var pathToSVGSprite;
	var iconLoaderService;

	function updateBroadcastsStatus(broadcastID) {
		var broadcast = $(moduleEl).find('.item.status-recordable[data-type="broadcast"]').filter('[data-broadcast-id="' + broadcastID + '"]');
		broadcast.removeClass('status-recordable').addClass('status-recording');
		broadcast.find('.icon:not(.icon-recording)').remove().end().find('.item-status-icon').prepend($('<span data-icon="icon-recording" />'));
		broadcast.find('.status-desc').text('В записи');
		iconLoaderService.renderIcons();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['updatebroadcaststatus'],

		behaviors: ['banner-close', 'load-broadcast-player'],

		init: function () {
			moduleEl = context.getElement();
			advertService = context.getService('advertizing');
			progressbarService = context.getService('progressbar');
			modalService = context.getService('modal');
			iconLoaderService = context.getService('icon-loader');
			bannersHideTime = context.getConfig('bannersHideTime');
			pathToSVGSprite = context.getConfig('pathToSVGSprite');
			adverts = $(moduleEl).find('[data-type="advertizing"]');
			fillDiskSpace = $(moduleEl).find('[data-type="fill-disk-space"]');

			// Скрываем баннеры записанные в cookies
			adverts.each(function () {
				if (advertService.isHidden(this)) {
					advertService.hide(this);
				}
			});

			// Render icons on the page
			document.addEventListener('DOMContentLoaded', function () {
				iconLoaderService.renderSprite(pathToSVGSprite);
				iconLoaderService.renderIcons();
			});

			if (fillDiskSpace.length) {
				progressbarService.create(fillDiskSpace.find('.progress-holder').get(0), {
					strokeWidth: 11.1,
					color: '#216ed8',
					trailColor: '#d0d0d0'
				});
			}
		},
		destroy: function () {
			advertService = null;
			moduleEl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'signin-overlay-toggle') {
				event.preventDefault();
				Box.Application.broadcast('togglesigninoverlay');
			} else {
				if ($(event.target).closest('[data-module="signin-overlay"]').length === 0 &&
					$(event.target).closest('.icon-recordit').length === 0 &&
					$(event.target).closest('.icon-recorded').length === 0 &&
					$(event.target).closest('[data-type="signin-handler-link"]').length === 0) {
					Box.Application.broadcast('hidesigninoverlay');
				}
			}
			if (elementType === 'signup-overlay-toggle') {
				event.preventDefault();
				Box.Application.broadcast('togglesignupoverlay');
			} else {
				if ($(event.target).closest('[data-module="signup-overlay"]').length === 0) {
					Box.Application.broadcast('hidesignupoverlay');
				}
			}

			if ($(event.target).closest('[data-module="reset-overlay"]').length === 0 &&
				$(event.target).closest('[data-type="reset-handler-link"]').length === 0 &&
				$(event.target).closest('[data-type="signin-handler-link"]').length === 0) {
				Box.Application.broadcast('hideresetoverlay');
			}
			if ($(event.target).closest('[data-module="success-reset-overlay"]').length === 0 &&
				$(event.target).closest('[data-type="signin-handler-link"]').length === 0) {
				Box.Application.broadcast('hidesuccessresetoverlay');
			}
		},
		onmessage: function (name, data) {
			if (name === 'updatebroadcaststatus' && typeof data.broadcastID !== 'undefined' && data.status === 'recording') {
				updateBroadcastsStatus(data.broadcastID);
			}
		}

	};
});
