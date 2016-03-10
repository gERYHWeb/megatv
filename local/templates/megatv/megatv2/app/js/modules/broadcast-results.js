/* global Box, setTimout */
Box.Application.addModule('broadcast-results', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var DATA_KEY = 'broadcast_results_dates';
	var moduleEl;
	var catItems;
	var kineticService;
	var kineticCanvas;
	var iconLoaderService;
	var kineticTimePointer;
	var pointerPosition;
	var pointerContainer;
	var dayMap = [];
	var canvasScrollPos;
	var arrowCurrentDay;
	var arrowNextDay;
	var sizewait;
	// var scrollwait;
	var fetchLink;
	var fetchResultsURL;
	var pageCount;
	var ajaxType;
	var daysConfig;
	var canvasWidth = 0;
	var itemWidth;
	var rightDayIndex = 0;
	var leftDayIndex = 0;
	var rightDaysPlaceholder;
	var leftDaysPlaceholder;
	var prevScrollPos = 0;

	function setDayGrid() {
		var dayWidth = itemWidth * 24;
		var rightAdding = 29;
		var leftAdding = 31;

		$.each(daysConfig, function (index) {
			if (index === 0) {
				dayMap.push({
					rightFridge: dayWidth + rightAdding,
					leftFridge: 0
				});
				canvasWidth += dayWidth + rightAdding;
			} else if (index === daysConfig.length) {
				dayMap.push({
					rightFridge: dayMap[index - 1].rightFridge + dayWidth + leftAdding,
					leftFridge: dayMap[index - 1].rightFridge
				});
				canvasWidth += dayWidth + leftAdding;
			} else {
				dayMap.push({
					rightFridge: dayMap[index - 1].rightFridge + dayWidth + leftAdding + rightAdding,
					leftFridge: dayMap[index - 1].rightFridge
				});
				canvasWidth += dayWidth + leftAdding + rightAdding;
			}
		});
	}

	function getDayData(dayIndex, direction) {
		var daysData;
		if (sessionStorage.getItem(DATA_KEY)) {
			daysData = JSON.parse(sessionStorage.getItem(DATA_KEY));

			if (typeof daysData.days[dayIndex] === 'object') {
				return daysData.days[dayIndex];
			} else {
				fetchDayData(dayIndex, daysConfig[dayIndex].dayReq, daysData, direction);
			}
		}
	}

	function updateDayGrid() {
		dayMap = [];
		setDayGrid();
	}

	function checkFridge() {
		// var canvasRightPos = $(kineticCanvas.target).scrollLeft() + $(kineticCanvas.target).width();
		// var direction = '';
		canvasScrollPos = $(kineticCanvas.target).scrollLeft() || 0;

		if (canvasScrollPos >= prevScrollPos) { // scroll direction right
			// check right arrow fridge
			// if (canvasScrollPos <= dayMap[rightDayIndex].rightFridge && canvasScrollPos >= dayMap[leftDayIndex].leftFridge) {
			// 	updateArrow(rightDayIndex, 'left');
			// }
			// // check left arrow fridge
			// if (canvasScrollPos >= dayMap[leftDayIndex].leftFridge && canvasRightPos >= dayMap[leftDayIndex].leftFridge) {
			// 	updateArrow(rightDayIndex, 'right');
			// }
			// check right day fridge
			if (typeof dayMap[rightDayIndex] !== 'undefined') {
				if (canvasScrollPos + $(kineticCanvas.target).width() >= dayMap[rightDayIndex].rightFridge - (itemWidth * 2.5) &&
					canvasScrollPos + $(kineticCanvas.target).width() <= dayMap[rightDayIndex + 1].leftFridge + (itemWidth * 2.5)) {
					updateRightDay(rightDayIndex, true);
					// console.log('right day');
				}
			}
			// check left day fridge
			if (typeof dayMap[leftDayIndex + 1] !== 'undefined') {
				if (canvasScrollPos >= dayMap[leftDayIndex + 1].leftFridge + (itemWidth * 2.5)) {
					updateLeftDay(leftDayIndex, true);
					// console.log('left day');
				}
			}
		} else { // scroll direction left
			// check right day fridge
			// console.log(canvasScrollPos + $(kineticCanvas.target).width());
			if (typeof dayMap[rightDayIndex - 1] !== 'undefined') {
				// console.log(canvasScrollPos, rightDayIndex, '< ', dayMap[rightDayIndex].rightFridge, '> ', dayMap[rightDayIndex - 1].rightFridge - (itemWidth * 2.5), '< ', dayMap[rightDayIndex].leftFridge + (itemWidth * 2.5), '>', dayMap[rightDayIndex - 1].leftFridge + (itemWidth * 2.5));

				if (canvasScrollPos <= dayMap[rightDayIndex].rightFridge - (itemWidth * 2.5) &&
					canvasScrollPos >= dayMap[rightDayIndex - 1].rightFridge - (itemWidth * 2.5) &&
					canvasScrollPos <= dayMap[rightDayIndex].leftFridge + (itemWidth * 2.5) &&
					canvasScrollPos >= dayMap[rightDayIndex - 1].leftFridge + (itemWidth * 2.5)) {
					updateRightDay(rightDayIndex, false);
					// console.log('right day' + rightDayIndex);
				}
			}
			// check left day fridge
			if (typeof dayMap[leftDayIndex - 1] !== 'undefined') {
				// console.log(canvasScrollPos, '<',dayMap[leftDayIndex - 1].rightFridge - (itemWidth * 2.5));
				if (canvasScrollPos + $(kineticCanvas.target).width() <= dayMap[leftDayIndex - 1].rightFridge - (itemWidth * 2.5)) {
					updateLeftDay(leftDayIndex, false);
					// console.log('left day' + leftDayIndex);
				}
			}
		}
		prevScrollPos = canvasScrollPos;
	}

	// function updateArrow(dayIndex, arrowType) {
	// 	// var currentDayText = daysConfig[dayIndex];
	// 	// var nextDayText = daysConfig[dayIndex + 1].dayMark || '';

	// 	// switch (arrowType) {
	// 	// 	case 'left':
	// 	// 		if (currentDayText !== '' && currentDayText !== arrowCurrentDay.text()) {
	// 	// 			arrowCurrentDay.text(currentDayText);
	// 	// 		}
	// 	// 		break;
	// 	// 	case 'right':
	// 	// 		if (nextDayText !== '' && nextDayText !== arrowNextDay.text()) {
	// 	// 			arrowNextDay.text(nextDayText);
	// 	// 		}
	// 	// 		break;
	// 	// }
	// 	// console.log('update arrow', dayIndex, arrowType);
	// }

	function updateRightDay(dayIndex, direction) {
		// var dayData;
		// if (typeof daysConfig[dayIndex] === 'object') {
		// 	if (daysConfig[dayIndex].state !== 'loading' && typeof daysConfig[dayIndex].state !== 'undefined') {
		// 		var dayData = getDayData(dayIndex, 'right');
		// 	} else {
		// 		getDayData(dayIndex, 'right');
		// 	}
		// }
		if (direction === true) {
			rightDayIndex += 1;

			if (rightDayIndex > leftDayIndex) {
				addRightDay();
				// console.log('add right day');
			}
			if (rightDayIndex === leftDayIndex) {
				removeLeftDay();
				// console.log('remove left day');
			}
			if (rightDayIndex === 0) {
				removeRightDay();
				// console.log('remove right day');
			}
		} else {
			rightDayIndex -= 1;

			if (rightDayIndex < leftDayIndex) {
				addLeftDay();
				// console.log('add left day');
			}
			if (rightDayIndex === leftDayIndex) {
				removeRightDay();
				// console.log('remove right day');
			}
		}

		// console.log('rightDayIndex: ' + rightDayIndex);
	}

	function updateLeftDay(dayIndex, direction) {
		if (direction === true) {
			leftDayIndex += 1;

			if (rightDayIndex === leftDayIndex) {
				removeLeftDay();
				// console.log('remove left day');
			}
		} else {
			leftDayIndex -= 1;

			if (rightDayIndex === leftDayIndex) {
				removeRightDay();
				// console.log('remove right day');
			}
		}
		// console.log('leftDayIndex: ' + leftDayIndex);
	}

	function addRightDay() {
		var dayData;
		if (daysConfig[rightDayIndex].state !== 'loading' && typeof daysConfig[rightDayIndex].state !== 'undefined') {
			dayData = getDayData(rightDayIndex, 'right');
			rightDaysPlaceholder.before(dayData.html);
			updateDaysPlaceholders(rightDayIndex, leftDayIndex);
			$(window).lazyLoadXT();
			iconLoaderService.renderIcons(context);
		} else {
			getDayData(rightDayIndex, 'right');
		}
	}

	function removeRightDay() {
		$(moduleEl).find('.day').last().remove();
		updateDaysPlaceholders(rightDayIndex, leftDayIndex);
	}

	function addLeftDay() {
		var dayData;
		if (daysConfig[leftDayIndex].state !== 'loading' && typeof daysConfig[leftDayIndex].state !== 'undefined') {
			dayData = getDayData(leftDayIndex, 'right');
			leftDaysPlaceholder.after(dayData.html);
			updateDaysPlaceholders(rightDayIndex, leftDayIndex);
			$(window).lazyLoadXT();
			iconLoaderService.renderIcons(context);
		} else {
			getDayData(leftDayIndex, 'right');
		}
	}

	function removeLeftDay() {
		$(moduleEl).find('.day').first().remove();
		updateDaysPlaceholders(rightDayIndex, leftDayIndex);
	}

	function fetchDayData(dayIndex, dayReq, daysData, direction) {
		daysConfig[dayIndex].state = 'loading';

		$.ajax({
			type: 'post',
			url: fetchResultsURL,
			data: {
				AJAX: 'Y',
				AJAX_TYPE: ajaxType,
				date: dayReq
			},
			success: function (response) {
				var $response = $(response);

				// console.log($response.find('.canvas-wrap').find('.left-days-placeholder, .right-days-placeholder').remove().end().html());

				daysData.days[dayIndex] = {
					html: $response.find('.canvas-wrap').find('.left-days-placeholder, .right-days-placeholder').remove().end().html(),
					mark: daysConfig[dayIndex].dayMark
				};
				daysConfig[dayIndex].state = 'loaded';
				sessionStorage.setItem(DATA_KEY, JSON.stringify(daysData));

				if (direction === 'left') {
					addLeftDay('left');
				} else if (direction === 'right') {
					addRightDay('right');
				}
			},
			error: function () {
				console.warn('Ошибка загрузки дня');
			}
		});
	}

	function updateDaysPlaceholders(currentDayIndex, nextDayIndex) {
		if (currentDayIndex <= nextDayIndex) { // to the right
			rightDaysPlaceholder.css('width', canvasWidth - dayMap[nextDayIndex].rightFridge);
			leftDaysPlaceholder.css('width', dayMap[currentDayIndex].leftFridge);
		} else { // to the left
			rightDaysPlaceholder.css('width', canvasWidth - dayMap[currentDayIndex].rightFridge);
			leftDaysPlaceholder.css('width', dayMap[nextDayIndex].leftFridge);
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		behaviors: ['category-row', 'recording-broadcast', 'play-recorded-broadcasts'],

		init: function () {
			kineticService = context.getService('kinetic');
			iconLoaderService = context.getService('icon-loader');
			moduleEl = context.getElement();
			catItems = $(moduleEl).find('.categories-items');
			kineticTimePointer = catItems.find('.js-time-pointer');
			pointerContainer = kineticTimePointer.closest('.pair-container');
			arrowCurrentDay = $(moduleEl).find('[data-type="prev-button"] .prev-date');
			arrowNextDay = $(moduleEl).find('[data-type="next-button"] .next-date');
			fetchLink = $(moduleEl).find('[data-type="fetch-results-link"]');
			rightDaysPlaceholder = $(moduleEl).find('.right-days-placeholder');
			leftDaysPlaceholder = $(moduleEl).find('.left-days-placeholder');

			fetchResultsURL = context.getConfig('fetchResultsURL');
			pageCount = parseInt(context.getConfig('page'), 10);
			ajaxType = context.getConfig('ajaxType');
			daysConfig = context.getConfig('dates');
			itemWidth = $(moduleEl).find('.day .item:not(.double-item)').innerWidth();

			pointerPosition = pointerContainer.length > 0 ? pointerContainer.position() : kineticTimePointer.position();

			$(moduleEl).find('[data-type="broadcast"]').data('status-flag', false).data('play-flag', false);
			$(moduleEl).data('ajax-flag', true);

			// kinetic
			kineticCanvas = kineticService.create(catItems, {
				y: false,
				cursor: null,
				triggerHardware: true,
				movingClass: {},
				deceleratingClass: {},
				slowdown: 0,
				filterTarget: function (target) {
					if ($(target).closest('.item-status-icon').length) {
						return false;
					}
				},
				stopped: function () {
					var canvas = $(this.el);
					$(window).trigger('scroll');
					setTimeout(function () {
						canvas.removeClass('kinetic-moving');
						checkFridge();
					}, 100);
				},
				moved: function () {
					$(this.$el).addClass('kinetic-moving');
				}
			});

			// set dayGrid
			setDayGrid();

			// scroll to current time position
			if (kineticTimePointer.length > 0) {
				kineticCanvas.moveTo(pointerPosition.left, function () {
					if (pointerPosition.left >= dayMap[0].rightFridge - (itemWidth * 2.5) &&
						pointerPosition.left <= dayMap[1].leftFridge + (itemWidth * 2.5)) {
						updateRightDay(0, true);
						setTimout(function () {
							$(kineticCanvas.target).removeClass('kinetic-moving');
						}, 500);

					}
				});
			}

			// clear storage
			sessionStorage.removeItem(DATA_KEY);

			// save first day
			sessionStorage[DATA_KEY] = JSON.stringify({
				days: [
					{
						html: $(moduleEl).find('.canvas-wrap').clone().find('.left-days-placeholder, .right-days-placeholder').remove().end().html(),
						mark: daysConfig[0].dayMark
					}
				]
			});
			daysConfig[0].state = 'loaded';
			updateDaysPlaceholders(0, 0);

			// updateRightDay(0);

			// addRightDay();


			// update dayGrid
			$(window).on('resize', function () {
				if (typeof sizewait !== 'undefined') {
					clearTimeout(sizewait);
				}
				sizewait = setTimeout(updateDayGrid, 150);
			});
			// .on('scroll', function () { // pages preloading
			// 	if (typeof scrollwait !== 'undefined') {
			// 		clearTimeout(scrollwait);
			// 	}
			// 	scrollwait = setTimeout(loadMoreChannels, 100);
			// });

			// stiky wrapper init
			$(moduleEl).find('.sticky-wrapp').stick_in_parent();
		},
		destroy: function () {
			kineticService = null;
			moduleEl = null;
			catItems = null;
			kineticCanvas = null;
			kineticTimePointer = null;
			pointerContainer = null;
			pointerPosition = null;
			dayMap = null;
			iconLoaderService = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'prev-button') {
				event.preventDefault();
				kineticCanvas.move('left', itemWidth * 2.5);
				setTimeout(checkFridge, 800);
			} else if (elementType === 'next-button') {
				event.preventDefault();
				kineticCanvas.move('right', itemWidth * 2.5);
				setTimeout(checkFridge, 800);
			}
		}

	};
});
