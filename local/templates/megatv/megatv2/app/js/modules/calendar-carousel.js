/* global Box */
Box.Application.addModule('calendar-carousel', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moment = context.getGlobal('moment');
	var moduleEl;
	var carousel;
	var currentDay;
	var slidesHTML = '';
	var prevTrigger;
	var nextTrigger;
	var minDate;
	var maxDate;
	var today;
	var weekPointer;

	function updateNextPrevTriggers() {
		var carouselElements = $(carousel.flickity('getCellElements'));
		if ($(carouselElements[0]).hasClass('is-selected')) {
			prevTrigger.addClass('disabled');
		} else {
			prevTrigger.removeClass('disabled');
		}
		if ($(carouselElements.slice(-1)).hasClass('is-selected')) {
			nextTrigger.addClass('disabled');
		} else {
			nextTrigger.removeClass('disabled');
		}
	}

	function updateDateTriggersState(element) {
		var $parent = element.closest('li');
		var dateGroupIndex = $parent.closest('.date-group').index();
		if ($parent.hasClass('disabled') === false) {
			carousel.find('li').removeClass('current').end().find($parent).addClass('current');
			carousel.flickity('select', dateGroupIndex);

			return true;
		} else {
			return false;
		}
	}

	function getWeekHTML(dayPointer, minDate, maxDate) {
		var weekStart = dayPointer.startOf('week');
		var weekArr = [];
		var weekHTML = '';

		for (var i = 0; i <= 6; i = i + 1) {
			if (i === 0) {
				today = moment(weekStart);
				weekArr[i] = {
					day: moment(today),
					dayNum: moment(today).format('e'),
					dayChar: moment(today).format('dd')
				};
			} else {
				today = moment(weekStart).add(i, 'days');
				weekArr[i] = {
					day: moment(today),
					dayNum: moment(today).format('e'),
					dayChar: moment(today).format('dd')
				};
			}
		}

		weekHTML = '<ul class="date-group">';
		weekArr.forEach(function (currentValue, index) {
			var liClass;
			if (moment(weekArr[index].day).format('DD.MM.YYYY') === moment(currentDay).format('DD.MM.YYYY')) {
				liClass = ' class="current"';
			} else if (moment(weekArr[index].day).isBetween(minDate.subtract(1, 'seconds'), maxDate.add(1, 'seconds')) === false) {
				liClass = ' class="disabled"';
			} else {
				liClass = '';
			}
			weekHTML += '<li' + liClass + ' data-date="' + moment(weekArr[index].day).format('DD.MM.YYYY') + '"><a href="#" data-type="day-trigger"><span class="day-char">' + weekArr[index].dayChar +
											'</span><span class="day-number">' + moment(weekArr[index].day).format('DD') + '</span></a></li>';
		});
		weekHTML += '</ul>';

		return weekHTML;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['datechanged'],

		init: function () {
			moduleEl = context.getElement();
			today = moment().startOf('day');
			currentDay = moment(context.getConfig('currentDate'), 'DD.MM.YYYY');
			minDate = context.getConfig('minDate') ? moment(today).subtract(context.getConfig('minDate'), 'days') : null;
			maxDate = context.getConfig('maxDate') ? moment(today).add(context.getConfig('maxDate'), 'days') : null;
			carousel = $(moduleEl).find('[data-type="dates-carousel"]');
			prevTrigger = $(moduleEl).find('[data-type="prev-trigger"]');
			nextTrigger = $(moduleEl).find('[data-type="next-trigger"]');

			weekPointer = moment(minDate);
			slidesHTML += getWeekHTML(moment(minDate), moment(minDate), moment(maxDate)); // minDate

			while (moment(weekPointer).endOf('week').format() !== moment(maxDate).endOf('week').format()) {
				slidesHTML += getWeekHTML(weekPointer.add(1, 'week'), moment(minDate), moment(maxDate));
			}

			if ($(slidesHTML).find('li.current').closest('.date-group').index() !== -1) {
				carousel.html(slidesHTML).flickity({
					prevNextButtons: false,
					pageDots: false,
					initialIndex: $(slidesHTML).find('li.current').closest('.date-group').index()
				});
			} else {
				console.warn('Промежуток дат задан не корректно');
			}
		},
		destroy: function () {
			moduleEl = null;
			carousel = null;
		},
		onmessage: function (name, data) {
			switch (name) {
				case 'datechanged':
					if (data.newDate !== currentDay) {
						var element = carousel.find('[data-date="' + data.newDate + '"]');
						updateDateTriggersState(element);
					}
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'prev-trigger') {
				event.preventDefault();
				carousel.flickity('previous');
				updateNextPrevTriggers();
			} else if (elementType === 'next-trigger') {
				event.preventDefault();
				carousel.flickity('next');
				updateNextPrevTriggers();
			} else if (elementType === 'day-trigger') {
				event.preventDefault();
				if (updateDateTriggersState($(event.target))) {
					// send message
					Box.Application.broadcast('datechanged', {
						newDate: $(event.target).closest('li').data('date') // DD.MM.YYYY
					});
					window.location.search = 'cur_date=' + moment($(event.target).closest('li').data('date'), 'DD.MM.YYYY').format('DD-MM-YYYY');
				}
			}
		}
	};
});

