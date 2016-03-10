/* global Box, alert */
Box.Application.addModule('subscription-services', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var progressbarService;
	var progressbarHolder;
	var totalSpaceHolder;
	var filledDiskSpace;
	var remoteUrl;
	var progressbar;
	var usedSpaceHolder;

	function updateServiceStatus(serviceID, status) {
		$.get(remoteUrl, {
			serviceID: serviceID,
			status: status
		}, function (data) {
			var element = $(event.target).closest('.item');
			switch (data.status) {
				case 'enabled':
					element.addClass('status-active');
					break;
				case 'disabled':
					element.removeClass('status-active');
					break;
				case 'temporal':
					element.addClass('status-active');
					setTimeout(function () {
						if (typeof data.updatedSpace === 'number') {
							updateDiskSpace(data.updatedSpace);
						}
						element.removeClass('status-active');
					}, 500);
					break;
			}
			if (data.status === 'enable') {
				element.addClass('status-active');
			} else if (data.status === 'disable') {
				element.removeClass('status-active');
			}
		}, 'json').fail(function () {
			alert('Что-то пошло не так. Повторите попытку позднее!');
		});
	}

	function updateDiskSpace(newDiskSpace) {
		var usedDiskSpace = parseInt(usedSpaceHolder.text(), 10);
		totalSpaceHolder.text(newDiskSpace + ' ГБ');
		progressbar.animateProgress(usedDiskSpace / newDiskSpace, 500);
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			progressbarService = context.getService('progressbar');
			filledDiskSpace = context.getConfig('filledDiskSpace');
			remoteUrl = context.getConfig('url');
			totalSpaceHolder = $(moduleEl).find('.total-space strong');
			usedSpaceHolder = $(moduleEl).find('.used-space strong');
			progressbarHolder = $(moduleEl).find('.progressbar-holder').get(0);

			progressbar = progressbarService.create(progressbarHolder, {
				progress: filledDiskSpace,
				strokeWidth: 13.147410358566,
				color: '#216ed8',
				trailColor: '#e9e9e9'
			});
		},
		destroy: function () {
			progressbarService = null;
			moduleEl = null;
			progressbarHolder = null;
			progressbar = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'service-item') {
				var serviceID = $(event.target).closest('.item').data('service-id');
				var status = $(event.target).closest('.item').hasClass('status-active') ? 'disable' : 'enable';

				updateServiceStatus(serviceID, status);
			}
		}

	};
});

