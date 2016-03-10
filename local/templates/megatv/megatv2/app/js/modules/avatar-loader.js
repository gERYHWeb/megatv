/* global Box, alert */
Box.Application.addModule('avatar-loader', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var progressbarService;
	var fileuploadService;
	var progressbarHolder;
	var progressbar;
	var fileInput;
	var remoteUrl;
	var textHolder;
	var imagePath;

	function imageIsLoaded(img) {
		// During the onload event, IE correctly identifies any images that
		// weren’t downloaded as not complete. Others should too. Gecko-based
		// browsers act like NS4 in that they report this incorrectly.
		if (!img.complete) {
			return false;
		}

		// However, they do have two very useful properties: naturalWidth and
		// naturalHeight. These give the true size of the image. If it failed
		// to load, either of these should be zero.

		if (typeof img.naturalWidth !== 'undefined' && img.naturalWidth === 0) {
			return false;
		}

		// No other way of checking: assume it’s ok.
		return true;
	}

	function updateModuleStateToLoaded() {
		progressbar.setProgress(1, 300);
		$(moduleEl).removeClass('is-loading is-empty');
		textHolder.text('Обновить аватар');

		// send message
		Box.Application.broadcast('useravatarupdated', {
			thumbnail: imagePath
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			progressbarService = context.getService('progressbar');
			fileuploadService = context.getService('fileuploader');
			progressbarHolder = $(moduleEl).find('.progressbar-holder').get(0);
			fileInput = $(moduleEl).find('[data-type="file-input"]');
			remoteUrl = context.getConfig('url');
			textHolder = $(moduleEl).find('.load-avatar-text-holder');

			progressbar = progressbarService.create(progressbarHolder, {
				strokeWidth: 0.657894736842,
				color: '#ffba00',
				trailColor: '#dfdfe0'
			});
			fileuploadService.create(fileInput, {
				url: remoteUrl,
				dataType: 'json',
				add: function (event, data) {
					progressbar.setProgress(0);
					$(moduleEl).addClass('is-empty is-loading');
					data.submit().error(function () {
						alert('Что-то пошло не так. Повторите попытку позднее!');
					});
				},
				done: function (event, data) {
					imagePath = decodeURIComponent(data.result.files[0].thumbnailUrl);
					$(progressbarHolder).find('img').remove().end().append($('<img src="' + imagePath + '" width="246" height="246" />'));

					progressbar.animateProgress(0.8, 800, function () {
						if (imageIsLoaded($(progressbarHolder).find('img').get(0))) {
							updateModuleStateToLoaded();
						} else {
							$(progressbarHolder).find('img').on('load', 'img', updateModuleStateToLoaded);
						}
					});
				}
			});
		},
		destroy: function () {
			moduleEl = null;
			progressbarService = null;
			fileuploadService = null;
		}

	};
});
