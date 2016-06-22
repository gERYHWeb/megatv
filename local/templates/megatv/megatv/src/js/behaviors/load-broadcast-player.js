/* global Box, alert, jwplayer */
Box.Application.addBehavior('load-broadcast-player', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var playerURL;
	var modalService;
	var modal;
	var playerModalContainer;
	var playerLastPositionURL;
	var player;
	var playerPos;
	var playerDur;
	var percentPos;
	var progressPosition;
	var broadcastID;
	var record;
	var iconLoaderService;

	function loadPlayer(broadcastID, element) {
		addPlayerModal();

		$.ajax({
			type: 'GET',
			dataType: 'html',
			url: playerURL,
			data: {
				broadcastID: broadcastID,
				record: record
			},
			error: function () {
				alert('Что-то пошло не так. Повторите попытку позднее!');
				$(element).data('play-flag', false);
			},
			success: function (data) {
				$(element).data('play-flag', false);
				playerModalContainer = $('.player-modal');
				playerModalContainer.find('.modal-content').html(data);
				iconLoaderService.renderIcons();
				Box.Application.startAll(playerModalContainer.get(0));
				modal = modalService.create(playerModalContainer, {
					backdropClass: 'modal-backdrop player-backdrop'
				});
				modal.show();
			}
		});
	}
	function addPlayerModal() {
		var modalHTML = $('<div class="modal player-modal fade" id="player-modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>');
		if ($('#player-modal').length === 0) {
			$('body').prepend(modalHTML);
		}
	}
	function savePlayerPosition() {
		player = jwplayer('player');
		playerPos = player.getPosition();
		playerDur = player.getDuration();
		percentPos = Math.round((playerPos / playerDur) * 100);

		if (percentPos === 0) {
			percentPos = 0;
		} else if (percentPos <= 85 && percentPos > 0) {
			progressPosition = Math.floor(percentPos / 25);
		} else {
			progressPosition = 4;
		}

		if (playerPos !== 0) {
			$.get(playerLastPositionURL, {
				broadcastID: broadcastID,
				record: record,
				progressInSeconds: playerPos,
				progressPosition: progressPosition
			}, 'json').fail(function () {
				alert('Что-то пошло не так. Повторите попытку позднее!');
			});
		}
	}
	function play() {
		jwplayer('player').play();
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['playbroadcast'],

		init: function () {
			playerURL = context.getConfig('playerURL');
			playerLastPositionURL = context.getConfig('playerLastPositionURL');
			modalService = context.getService('modal');
			iconLoaderService = context.getService('icon-loader');

			$(document).on('hidden.bs.modal', playerModalContainer, function (event) {
				var modal = $(event.target).closest('.modal');

				if (modal.hasClass('player-modal')) {
					// remove class 'player-modal-open' with core class 'modal-open'
					$('body').removeClass('player-modal-open');
					savePlayerPosition();
					Box.Application.stopAll(playerModalContainer.get(0));
				}
			});

			// When modal show
			$(document).on('shown.bs.modal', function (event) {
				var modal = $(event.target).closest('.modal');
				// and this modal is player-modal
				if (modal.hasClass('player-modal')) {
					// add class for body, that remove padding-right for fullscreen player
					$('body').addClass('player-modal-open');
				}

				// Autoplay when show modal
				play();
			});
		},
		destroy: function () {
			playerURL = null;
			modal = null;
		},
		onmessage: function (name, data) {
			switch (name) {
				case 'playbroadcast':
					if (typeof data.broadcastID !== 'undefined') {
						broadcastID = data.broadcastID;
						record = data.record || false;
						loadPlayer(broadcastID, record, data.element);
					}
					break;
			}
		}
	};

});
