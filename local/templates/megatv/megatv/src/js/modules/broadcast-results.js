/* global Box, setTimout */
Box.Application.addModule('broadcast-results', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var cookieService;
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
	var canvasScrollPosition = 0;
	var canvasScrollPositionDay = 0;

	function setDayGrid() {
		var dayWidth = itemWidth * 24; // длина одного дня = длина обычной передачи * 24 часа.
		var leftAdding = 31; // ширина названия дня в начале
		var rightAdding = 29; // ширина названия дня в конце

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

		// console.log( daysConfig );
		// console.log( dayMap );
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

	// Сбрасываем конфиг дней
	function updateDayGrid() {
		dayMap = [];
		setDayGrid();
	}

	function saveScrollPosition() {
		// console.log( 'Сохранили положение полотна на значении ' + canvasScrollPos );
		// Сохраняем в куки положение горизонтального скролла, через сутки параметр стирается
		// console.log( daysConfig[0].dayReq );
		cookieService.set('canvasScrollPosition', canvasScrollPos, { expires: 1 });
		cookieService.set('canvasScrollPositionDay', daysConfig[0].dayReq, { expires: 1 });
	}

	function checkFridge() {
		// var canvasRightPos = $(kineticCanvas.target).scrollLeft() + $(kineticCanvas.target).width();
		// var direction = '';
		canvasScrollPos = $(kineticCanvas.target).scrollLeft() || 0;

		// console.log( dayMap );
		// console.log( 'день слева до действий: ' + leftDayIndex );
		// console.log( 'день справа до действий: ' + rightDayIndex );

		// Проверяем направление прокрутки полотна и
		// в зависимости от этого подгружаем слева или справа дни
		if (canvasScrollPos >= prevScrollPos) { // scroll direction right
			// console.log( 'направление прокрутки - вправо' );

			// check right arrow fridge
			// if (canvasScrollPos <= dayMap[rightDayIndex].rightFridge && canvasScrollPos >= dayMap[leftDayIndex].leftFridge) {
			// 	updateArrow(rightDayIndex, 'left');
			// }
			// // check left arrow fridge
			// if (canvasScrollPos >= dayMap[leftDayIndex].leftFridge && canvasRightPos >= dayMap[leftDayIndex].leftFridge) {
			// 	updateArrow(rightDayIndex, 'right');
			// }

			// check right day fridge
			// Из-за того, что мы устанавливаем положение полотна, на котором остановился пользователь
			// положение полотна может быть на несколько дней впереди
			// Получаем индекс дня в зависимости от положения полотна
			// canvasScrollPos / dayWidth
			// getDayData(rightDayIndex, 'right');

			if (typeof dayMap[rightDayIndex] !== 'undefined') {
					// console.log( '----------' );
					// console.log( 'Положение полотна: ' + canvasScrollPos );
					// console.log( 'Ширина видимой части полотна: ' + $(kineticCanvas.target).width() );
					// console.log( 'Расстояние до следующего дня справа: ' + dayMap[rightDayIndex].rightFridge );
					// console.log( 'Длина ' + itemWidth * 2.5 );
					// console.log( '----------' );
				// Проверяем положение полотна
				// Если положение полотна + ширина видимой части полотна >= расстояния до следующего дня (минус ширина 2.5 ширины передачи, чтобы прогрузить следующий день до того, как пользователь увидит этот еще непрогруженный день)
				// console.log( '***' );
				// console.log( 'right day index: ' + rightDayIndex );
				// console.log( canvasScrollPos + $(kineticCanvas.target).width() );
				// console.log( dayMap[rightDayIndex].rightFridge - (itemWidth * 2.5) );
				// console.log( dayMap[rightDayIndex + 1].leftFridge + (itemWidth * 2.5) );
				// console.log( '***' );
				if (canvasScrollPos + $(kineticCanvas.target).width() >= dayMap[rightDayIndex].rightFridge - (itemWidth * 2.5) &&
					canvasScrollPos + $(kineticCanvas.target).width() <= dayMap[rightDayIndex + 1].leftFridge + (itemWidth * 2.5)) {
					// console.log( 'день справа уже близок' );
				updateRightDay(rightDayIndex, true);
			}
		}
			// check left day fridge
			// console.log( 'Проверяем день слева' );
			// console.log( typeof dayMap[leftDayIndex] !== 'undefined' );
			// if (typeof dayMap[leftDayIndex - 1] !== 'undefined') {

			// 	if (canvasScrollPos >= dayMap[rightDayIndex].leftFridge + (itemWidth * 2.5)) {
			// 		console.log( 'день слева уже близок' );
			// 		updateLeftDay(leftDayIndex, true);
			// 	}
			// }
		} else { // scroll direction left

			// console.log( 'направление прокрутки - влево' );

			// check right day fridge
			if (typeof dayMap[rightDayIndex - 1] !== 'undefined') {
				// console.log(canvasScrollPos, rightDayIndex, '< ', dayMap[rightDayIndex].rightFridge, '> ', dayMap[rightDayIndex - 1].rightFridge - (itemWidth * 2.5), '< ', dayMap[rightDayIndex].leftFridge + (itemWidth * 2.5), '>', dayMap[rightDayIndex - 1].leftFridge + (itemWidth * 2.5));

				if (canvasScrollPos <= dayMap[rightDayIndex].rightFridge - (itemWidth * 2.5) &&
					canvasScrollPos >= dayMap[rightDayIndex - 1].rightFridge - (itemWidth * 2.5) &&
					canvasScrollPos <= dayMap[rightDayIndex].leftFridge + (itemWidth * 2.5) &&
					canvasScrollPos >= dayMap[rightDayIndex - 1].leftFridge + (itemWidth * 2.5)) {
					updateRightDay(rightDayIndex, false);
			}
		}
			// check left day fridge
			// if (typeof dayMap[leftDayIndex - 1] !== 'undefined') {
			// 	if (canvasScrollPos + $(kineticCanvas.target).width() <= dayMap[leftDayIndex - 1].rightFridge - (itemWidth * 2.5)) {
			// 		updateLeftDay(leftDayIndex-1, false);
			// 	}
			// }
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
		// console.log('update arrow', dayIndex, arrowType);
	// }

	function updateRightDay(dayIndex, direction) {
		// console.log( 'Обновляем день справа' );
		// var dayData;
		// if (typeof daysConfig[dayIndex] === 'object') {
		// 	if (daysConfig[dayIndex].state !== 'loading' && typeof daysConfig[dayIndex].state !== 'undefined') {
		// 		var dayData = getDayData(dayIndex, 'right');
		// 	} else {
		// 		getDayData(dayIndex, 'right');
		// 	}
		// }

		if (direction === true) {
			// console.log( 'увеличиваем rightDayIndex' );
			rightDayIndex += 1;
			// console.log( 'rightDayIndex: ' + rightDayIndex );

			if (rightDayIndex > leftDayIndex) {
				addRightDay();
				// console.log('add right day');
			}
			// if (rightDayIndex === leftDayIndex) {
			// 	removeLeftDay();
			// 	console.log('remove left day');
			// }
			// if (rightDayIndex === 0) {
			// 	removeRightDay();
			// 	console.log('remove right day');
			// }
		} else {
			// console.log( 'уменьшаем rightDayIndex' );
			// rightDayIndex -= 1;

			// if (rightDayIndex < leftDayIndex) {
			// 	addLeftDay();
			// 	console.log('add left day');
			// }
			// if (rightDayIndex === leftDayIndex) {
			// 	removeRightDay();
			// 	console.log('remove right day');
			// }
		}
	}

	function updateLeftDay(dayIndex, direction) {
		// console.log( 'Обновляем день слева' );

		if (direction === true) {
			// console.log( 'увеличиваем leftDayIndex' );
			leftDayIndex += 1;

			if (rightDayIndex === leftDayIndex) {
				removeLeftDay();
				// console.log('remove left day');
			}
		} else {
			// console.log( 'уменьшаем leftDayIndex' );
			leftDayIndex -= 1;

			if (rightDayIndex === leftDayIndex) {
				removeRightDay();
				// console.log('remove right day');
			}
		}
		// console.log('leftDayIndex: ' + leftDayIndex);
	}

	function addRightDay() {
		// console.log( 'Добавили день справа' );
		var dayData;
		// console.log( daysConfig );
		if (daysConfig[rightDayIndex].state !== 'loading' && typeof daysConfig[rightDayIndex].state !== 'undefined') {
			// console.log( '1' );
			// Получаем данные с сервера для следующего дня
			dayData = getDayData(rightDayIndex, 'right');
			rightDaysPlaceholder.before(dayData.html);
			updateDaysPlaceholders(leftDayIndex, rightDayIndex);
			$(window).lazyLoadXT();
			iconLoaderService.renderIcons(context);

			if ( canvasScrollPosition >= dayMap[rightDayIndex].rightFridge - $(kineticCanvas.target).width()) {
				// console.log( 'Отображаем еще следующий день' );
				updateRightDay(rightDayIndex, true);
			}
		} else {
			// console.log( '2' );
			getDayData(rightDayIndex, 'right');
		}
	}

	function removeRightDay() {
		$(moduleEl).find('.day').last().remove();
		updateDaysPlaceholders(leftDayIndex, rightDayIndex);
	}

	function addLeftDay() {
		// console.log( 'Добавили день слева' );
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
		// console.log( 'Подгружаем данные с сервера' );
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
		// console.log( 'updateDaysPlaceholders currentDayIndex: ' + currentDayIndex );
		// console.log( 'updateDaysPlaceholders nextDayIndex: ' + nextDayIndex );
		if (currentDayIndex <= nextDayIndex) { // to the right
			// console.log( 'Заполняем плейсхолдер справа' );
			rightDaysPlaceholder.css('width', canvasWidth - dayMap[nextDayIndex].rightFridge);
			leftDaysPlaceholder.css('width', dayMap[currentDayIndex].leftFridge);
		} else { // to the left
			// console.log( 'Заполняем плейсхолдер слева' );
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
			cookieService = context.getService('cookies');
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

			// set days only for default timegrid
			// Timegrid for recommendations remove

			// горизонтальное скроллируемое полотно применимо только для главной страницы
			if ( !$(moduleEl).is('.recommended-broadcasts') ) {
				// kinetic
				kineticCanvas = kineticService.create(catItems, {
					y: false,
					cursor: null,
					triggerHardware: true,
					movingClass: {},
					deceleratingClass: {},
					slowdown: 0,
					filterTarget: function (target) {
						// Кикаем фильтр таргета, т.к. неудобно прокручивать ленту, т.к. курсор чаще всего попадает как раз на иконку воспроизведения передачи
						// if ($(target).closest('.item-status-icon').length) {
						// 	return false;
						// }
					},
					stopped: function () {
						var canvas = $(this.el);
						$(window).trigger('scroll');
						setTimeout(function () {
							canvas.removeClass('kinetic-moving');
							checkFridge(); // проверяем положение полотна
							saveScrollPosition(); // сохраняем положение полотна в куки
						}, 100);
					},
					moved: function () {
						$(this.$el).addClass('kinetic-moving');
					}
				});



				// set dayGrid
				setDayGrid();

				// scroll to current time position
				canvasScrollPosition = Number(cookieService.get('canvasScrollPosition'));
				canvasScrollPositionDay = cookieService.get('canvasScrollPositionDay');

				// console.log( dayMap );

				// console.log( canvasScrollPosition );
				// console.log( dayMap[0].rightFridge - $(kineticCanvas.target).width() );
				// console.log( canvasScrollPosition > dayMap[0].rightFridge - $(kineticCanvas.target).width() );
				// if (canvasScrollPosition > dayMap[0].rightFridge - $(kineticCanvas.target).width()) {
				// 	updateRightDay(0, true);
				// }

				// Если в куках хранится данное значение
				if ( !isNaN(canvasScrollPosition) ) {
					// И если текущий день равен дню, сохраненному в куках, то производим действие,
					// иначе очищаем значение сохраненного дня
					// console.log( canvasScrollPositionDay );
					// console.log( daysConfig[0].dayReq );
					// console.log( canvasScrollPositionDay === daysConfig[0].dayReq );
					if (canvasScrollPositionDay === daysConfig[0].dayReq) {
						// то сдвигаем полотно на это значение
						kineticCanvas.moveTo(canvasScrollPosition, function () {
							// console.log( 'Установили положение полотна на значении: ' + canvasScrollPosition );

							if (canvasScrollPosition > dayMap[0].rightFridge - $(kineticCanvas.target).width()) {
								// console.log( 'Отображаем 2ой день' );
								updateRightDay(0, true);
							}
						});
					}
					else {
						cookieService.remove('canvasScrollPositionDay');
					}

				} else {
					// Если пользователь первый раз открывает полотно,
					// то его перекидывает к положению с тайм-поинтером
					if (kineticTimePointer.length > 0) {
						// Сдвигаем полотно до значения тайм-поинтера
						kineticCanvas.moveTo(pointerPosition.left, function () {
							// После сдвига проверяем не отображается ли рядом правый день
							// console.log( 'Положение тайм-поинтера: ' + pointerPosition.left );
							// console.log( dayMap[0].rightFridge );

							if (pointerPosition.left >= dayMap[0].rightFridge - (itemWidth * 2.5) &&
								pointerPosition.left <= dayMap[1].leftFridge + (itemWidth * 2.5)) {
								// Обновляем правый день
								// updateRightDay(0, true);
								setTimeout(function () {
									$(kineticCanvas.target).removeClass('kinetic-moving');
								}, 500);
							}
							// console.log( dayMap );
							// console.log( canvasScrollPos );

							if ( $(kineticCanvas.target).scrollLeft() >= dayMap[0].rightFridge - $(kineticCanvas.target).width()) {
								updateRightDay(0, true);
							}
						});
					}
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




				// checkFridge();

				// addRightDay();

				updateDaysPlaceholders(0, 0);

				// updateRightDay(rightDayIndex, true);


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

				// console.log( daysConfig );
			}
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
				// console.log( $(element) );
				event.preventDefault();
				kineticCanvas.move('left', itemWidth * 2.5);
				setTimeout(checkFridge, 800);
			} else if (elementType === 'next-button') {
				event.preventDefault();
				kineticCanvas.move('right', itemWidth * 2.5);
				setTimeout(checkFridge, 800);
			} else if (elementType === 'category') {
				var category = $(element).closest('.item').data('category');
				context.broadcast('categoryChanged', category);
			}
		}

	};
});