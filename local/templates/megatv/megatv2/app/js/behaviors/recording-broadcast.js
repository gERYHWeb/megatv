/* global Box, alert */
Box.Application.addBehavior('recording-broadcast', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var recordingURL;
	var pageModule;
	var authentication;

	function addRecordingNotify(broadcast) {
		var notifyHTML = '';
		notifyHTML += '<div class="recording-notify">' +
						'<div class="recording-notify-text-wrap">' +
							'<span data-icon="icon-recording-progress"></span>' +
								'<p>Ваша любимая передача<br> поставлена на запись</p>' +
							'</div>' +
						'</div>';

		broadcast.find('.item-header').before(notifyHTML);
	}
	function removeRecordingNotify(broadcast) {
		broadcast.find('.recording-notify').remove();
	}
	function addExtendDriveNotify(broadcast) {
		var notifyHTML = '';
		notifyHTML += '<div class="extend-drive-notify">' +
							'<div class="extend-drive-notify-text-wrap">' +
								'<span data-icon="icon-storage"></span>' +
								'<p>У Вас закончилось свободное место на облачном хранилище МЕГА.ДИСК</p>' +
								'<p><a href="/personal/services/">Заказать дополнительную емкость</a></p>' +
							'</div>' +
						'</div>';

		broadcast.find('.item-header').before(notifyHTML);
		broadcast.addClass('extend-drive-required');
	}
	function removeExtendDriveNotify(broadcast) {
		broadcast.removeClass('extend-drive-required');
		broadcast.find('.extend-drive-notify').remove();
	}

	function updateRemoteBroadcastStatus(broadcast, broadcastID, element) {
		$(element).data('status-flag', true);

		$.get(recordingURL, {
			broadcastID: broadcastID
		}, function (data) {
			if (data.status === 'success') {
				addRecordingNotify(broadcast);
				broadcast.removeClass('status-recordable').addClass('recording-in-progress status-recording');
				broadcast.find('.icon-recordit').remove().end().find('.item-status-icon').prepend($('<span data-icon="icon-recording" />'));
				broadcast.find('.status-desc').text('В записи');
				Box.Application.renderIcons(context);

				setTimeout(function () {
					removeRecordingNotify(broadcast);
					broadcast.removeClass('recording-in-progress');
					$(element).data('status-flag', false);
				}, 2000);

				// send message
				Box.Application.broadcast('updatebroadcaststatus', {
					status: 'recording',
					broadcastID: broadcastID
				});
			} else if (data.status === 'require-space') {
				addExtendDriveNotify(broadcast);

				setTimeout(function () {
					removeExtendDriveNotify(broadcast);
					$(element).data('status-flag', false);
				}, 7000);
			} else {
				alert('Что-то пошло не так. Повторите попытку позднее!');
				$(element).data('status-flag', false);
			}
		}, 'json').fail(function () {
			alert('Что-то пошло не так. Повторите попытку позднее!');
			$(element).data('status-flag', false);
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			recordingURL = context.getConfig('recordingURL');
			pageModule = $('[data-module="page"]').get(0);
			authentication = Box.Application.getModuleConfig(pageModule, 'authentication');
		},
		destroy: function () {
			moduleEl = null;
			recordingURL = null;
			pageModule = null;
			authentication = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'broadcast' && $(event.target).closest('.icon-recordit').length > 0) {
				event.preventDefault();
				if (authentication === true) {
					if ($(element).data('status-flag') === false) {
						var broadcast = $(moduleEl).find($(event.target).closest('.item'));
						var broadcastID = broadcast.data('broadcast-id');
						if (broadcastID !== '' && typeof broadcastID !== 'undefined') {
							if (broadcast.hasClass('status-recordable') && !broadcast.hasClass('status-recording')) {
								updateRemoteBroadcastStatus(broadcast, broadcastID, element);
							}
						}
					}
				} else {
					Box.Application.broadcast('showsigninoverlay');
				}
			}
		}
	};

});
