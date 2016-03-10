/* global Box, alert */
Box.Application.addModule('user-viewed-broadcasts', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var listItems;
	var kineticService;
	var timelineElements;
	var timelineService;
	var items;
	var remoteUrl;

	function deleteBrodacast(broadcastID) {
		$.get(remoteUrl, {
			broadcastID: broadcastID,
			delete: true
		}, function (data) {
			if (data.status === 'success') {
				location.reload(true);
			}
		}, 'json').fail(function () {
			alert('Что-то пошло не так. Повторите попытку позднее!');
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			kineticService = context.getService('kinetic');
			moduleEl = context.getElement();
			listItems = $(moduleEl).find('.broadcasts-list');
			items = $(moduleEl).find('.item');
			remoteUrl = context.getConfig('url');

			kineticService.create(listItems, {
				y: false,
				triggerHardware: true,
				movingClass: {},
				deceleratingClass: {},
				filterTarget: function (target) {
					if ($(target).closest('.item-status-icon').length || $(target).closest('.details-trigger').length) {
						return false;
					}
				}
			});
			$(moduleEl).on('mouseenter', '.item', function () {
				if (!$(this).hasClass('is-delete-state')) {
					$(this).addClass('hover');
				}
			}).on('mouseleave', '.item', function () {
				if (!$(this).hasClass('is-delete-state')) {
					$(this).removeClass('hover');
				}
			});
		},
		destroy: function () {
			kineticService = null;
			moduleEl = null;
			listItems = null;
			timelineElements = null;
			timelineService = null;
			items = null;
		},
		onclick: function (event, element, elementType) {
			var $item = $(event.target).closest('.item');
			var broadcastID = $item.attr('broadcast-id');
			if (element !== null) {
				event.preventDefault();
			}
			if (elementType === 'delete-trigger') {
				$item.addClass('is-delete-state');
			} else if (elementType === 'cancel-delete-state') {
				$item.removeClass('is-delete-state');
			} else if (elementType === 'delete-broadcast') {
				deleteBrodacast(broadcastID);
			} else if (elementType === 'share-trigger') {
				// send message
				Box.Application.broadcast('broadcastshare', {
					broadcastID: broadcastID
				});
			} else if (elementType === 'player-trigger') {
				// send message
				Box.Application.broadcast('broadcastplayer', {
					broadcastID: broadcastID
				});
			}
		}

	};
});
