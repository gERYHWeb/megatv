/* global Box, jwplayer */
Box.Application.addModule('broadcast-player', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	// var $ = context.getGlobal('jQuery');
	var player;
	var seekTime;
	var streamURL;
	var posterURL;
	var videoTitle;
	var cookiesService;
	var broadcastID;
	var DATA_KEY = 'player_last_position';
	var positionTimer;
	var playerFlashURL;

	var jwkey = 'iG896rbVQFbHJXibU6B9N6rx0nJgICypyJHsBYkYS+k=';
	jwplayer.key = jwkey;

	function setPlayerCookies() {
		var playerPos = player.getPosition();
		var playerDur = player.getDuration();

		if (playerPos !== 0 || playerPos !== playerDur) {
			cookiesService.set(DATA_KEY, playerPos, {
				expires: 7
			});
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			cookiesService = context.getService('cookies');
			seekTime = context.getConfig('seekTime');
			streamURL = context.getConfig('streamURL');
			posterURL = context.getConfig('posterURL');
			videoTitle = context.getConfig('videoTitle');
			playerFlashURL = context.getConfig('playerFlashURL');
			broadcastID = context.getConfig('broadcastID') || '';
			DATA_KEY += broadcastID;
			player = jwplayer('player');

			if (seekTime === '' || typeof seekTime === 'undefined') {
				seekTime = parseInt(cookiesService.get(DATA_KEY), 10);
			}

			player.setup({
				file: streamURL,
				image: posterURL,
				// width: 896,
				width: "100%",
				aspectratio: "16:9",
				// height: 504,
				title: videoTitle,
				displaydescription: false,
				flashplayer: playerFlashURL,
				hlslabels: {
					290: '240p',
					550: '360p',
					1100: '480p',
					1900: '720p HD'
				}
			}).on('play', function () {
				clearInterval(positionTimer);
				positionTimer = setInterval(setPlayerCookies, 15000);
			}).on('firstFrame', function () {
				if (typeof seekTime !== 'undefined') {
					player.seek(seekTime);
				}
				clearInterval(positionTimer);
				positionTimer = setInterval(setPlayerCookies, 15000);
			}).on('complete', function () {
				clearInterval(positionTimer);
			});
		},
		destroy: function () {
			clearInterval(positionTimer);
			player.remove();
			seekTime = null;
			streamURL = null;
			posterURL = null;
			videoTitle = null;
		}

	};
});
