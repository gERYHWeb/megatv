/* global Box */
Box.Application.addService('icon-loader', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var moduleEl;
	var contextIcons;

	function icon(name, options) {
		var optionsz = options || {};
		var size    = optionsz.size ? 'is-' + optionsz.size : '';
		var klass   = 'icon ' + name + ' ' + size + '' + (optionsz.class || '');

		var iconz   =	'<svg class="icon__cnt">' +
						'<use xlink:href="#' + name + '" />' +
						'</svg>';

		var html =  '<div class="' + klass + '">' +
						wrapSpinner(iconz, klass) +
					'</div>';

		return html;
	}
	function wrapSpinner(html, klass) {
		if (klass.indexOf('spinner') > -1) {
			return '<div class="icon__spinner">' + html + '</div>';
		} else {
			return html;
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		renderIcons: function (inst) {
			if (inst) {
				moduleEl = inst.getElement();
				contextIcons = moduleEl.querySelectorAll('[data-icon]');
			}
			var icons = inst ? contextIcons : document.querySelectorAll('[data-icon]');

			for (var i = 0; i < icons.length; i++) {
				var currentIcon = icons[i];
				var name        = currentIcon.getAttribute('data-icon');
				var options = {
					class:  currentIcon.className,
					size:   currentIcon.getAttribute('data-size')
				};

				currentIcon.insertAdjacentHTML('beforebegin', icon(name, options));
				currentIcon.parentNode.removeChild(currentIcon);
			}
		},
		renderSprite: function (path) {
			var file = (path !== '' && typeof path !== 'undefined') ? path : '/img/sprites/svg_sprite.svg';
			var revision = 1460392643;
			if (!document.createElementNS || !document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect) {
				document.createElement('svg');
				document.createElement('use');
			}
			var isLocalStorage = 'localStorage' in window && window.localStorage !== null;
			var	request;
			var	data;
			var	insertIT = function () {
				var sprite = '<div style="display: none">' + data + '</div>';
				document.body.insertAdjacentHTML('afterbegin', sprite);
			};
			var insert = function () {
				if (document.body) {
					insertIT();
				} else {
					document.addEventListener('DOMContentLoaded', insertIT);
				}
			};

			if (isLocalStorage && parseInt(localStorage.getItem('inlineSVGrev'), 10) === revision) {
				data = localStorage.getItem('inlineSVGdata');
				if (data) {
					insert();
					return true;
				}
			}
			try {
				request = new XMLHttpRequest();
				request.open('GET', file, true);
				request.onload = function () {
					if (request.status >= 200 && request.status < 400) {
						data = request.responseText;
						insert();
						if (isLocalStorage) {
							localStorage.setItem('inlineSVGdata', data);
							localStorage.setItem('inlineSVGrev', revision);
						}
					}
				};
				request.send();
			} catch (e) {}
		}

	};
});

/* global Box */
Box.Application.addService('kinetic', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	$.Kinetic.prototype.animateScrollTo = function (movingPos) {
		var $scroller = this._getScroller();
		$scroller.addClass('kinetic-moving');
		if (typeof movingPos === 'number') {
			$scroller.stop().animate({
				scrollLeft: movingPos
			}, 800, 'easeInOutCubic');
			this.settings.scrollLeft = movingPos;
			setTimeout(function () {
				$(window).trigger('scroll'); // hack for jquery.lazyLoadXT
				setTimeout(function() {
					$scroller.removeClass('kinetic-moving');
				}, 100);
			}, 800);
		}
	};
	$.Kinetic.prototype.animateScroll = function (movingOptions) {
		var $scroller = this._getScroller();
		var scrollLeft = this.scrollLeft();
		$scroller.addClass('kinetic-moving');
		if (typeof movingOptions.movingDistance === 'number' && typeof movingOptions.direction === 'string') {
			if (movingOptions.direction === 'left') {
				scrollLeft = scrollLeft - movingOptions.movingDistance;
			} else if (movingOptions.direction === 'right') {
				scrollLeft = scrollLeft + movingOptions.movingDistance;
			}
			$scroller.stop().animate({
				scrollLeft: scrollLeft
			}, 800, 'easeInOutCubic');
			this.settings.scrollLeft = scrollLeft;
			setTimeout(function () {
				$(window).trigger('scroll'); // hack for jquery.lazyLoadXT
				$scroller.removeClass('kinetic-moving');
			}, 800);
		}
	};

	function Kinetic(target, options) {
		this.target = target;
		this.options = options;
		$(target).kinetic(options);
	}
	Kinetic.prototype.moveTo = function (movingPos, callback) {
		$(this.target).kinetic('animateScrollTo', movingPos);
		if (typeof callback === 'function') {
			setTimeout(callback, 900);
		}
	};
	Kinetic.prototype.move = function (direction, movingDistance) {
		$(this.target).kinetic('animateScroll', {
			direction: direction,
			movingDistance: movingDistance
		});
	};

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		create: function (target, options) {
			return new Kinetic(target, options);
		}
	};

});


/* global Box */
Box.Application.addService('timeline', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');

	function Timeline(elem) {
		var $elem = $(elem);
		var progress = $elem.data('progress');

		$elem.find('.progress-bg').css({
			width: progress + '%'
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		create: function (elem) {
			return new Timeline(elem);
		}
	};

});


/* global Box */
Box.Application.addService('select', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function Select(target, options) {
		this.element = target;
		$(target).select2(options);
	}
	Select.prototype.open = function (target) {
		$(target || this.element).select2('open');
	};
	Select.prototype.close = function (target) {
		$(target || this.element).select2('close');
	};

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new Select(target, options);
		}

	};

});


/* global Box */
Box.Application.addService('progressbar', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var ProgressBar = context.getGlobal('ProgressBar');
	var circle;
	var progressbar;
	var obj;

	function Progressbar(target, options) {
		this.progress = options.progress || $(target).data('progress');
	}
	Progressbar.prototype.setProgress = function (value) {
		if (value >= 0.95) {
			this.set(1);
		} else {
			this.set(value);
		}
	};
	Progressbar.prototype.animateProgress = function (value, duration, callback) {
		if (typeof callback !== 'undefined') {
			callback = callback;
		} else {
			callback = null;
		}
		if (value >= 0.95) {
			this.animate(1, {
				duration: duration || 300
			}, callback);
		} else {
			this.animate(value, {
				duration: duration || 300
			}, callback);
		}
	};



	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			progressbar = new Progressbar(target, options);
			circle = new ProgressBar.Circle(target, options);
			obj = $.extend(true, progressbar, circle);
			obj.setProgress(progressbar.progress);
			return obj;
		}

	};

});


/* global Box */
Box.Application.addService('popover', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function Popover(target, options) {
		this.element = target;
		$(target).popover(options);
	}
	Popover.prototype.show = function (target) {
		$(target || this.element).popover('show');
	};
	Popover.prototype.hide = function (target) {
		$(target || this.element).popover('hide');
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new Popover(target, options);
		}

	};

});


/* global Box */
/*jshint -W030 */
Box.Application.addService('modal', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	//
	// Based on Bootstrap Modals v.3.3.5
	//

	var Modal = function (element, options) {
		this.options             = options;
		this.$body               = $(document.body);
		this.$element            = $(element);
		this.$dialog             = this.$element.find('.modal-dialog');
		this.$backdrop           = null;
		this.isShown             = null;
		this.originalBodyPad     = null;
		this.scrollbarWidth      = 0;
		this.ignoreBackdropClick = false;
	};

	Modal.TRANSITION_DURATION = 300;
	Modal.BACKDROP_TRANSITION_DURATION = 150;

	Modal.DEFAULTS = {
		backdrop: true,
		keyboard: true,
		show: true,
		backdropClass: 'modal-backdrop',
		container: $(document.body)
	};

	Modal.prototype.toggle = function (_relatedTarget) {
		return this.isShown ? this.hide() : this.show(_relatedTarget);
	};

	Modal.prototype.show = function (_relatedTarget) {
		var that = this;
		var e = $.Event('show.bs.modal', { relatedTarget: _relatedTarget });

		this.$element.trigger(e);

		if (this.isShown || e.isDefaultPrevented()) {
			return;
		}

		this.isShown = true;

		this.checkScrollbar();
		this.setScrollbar();
		this.options.container.addClass('modal-open');

		this.escape();
		this.resize();

		this.$element.on('click.dismiss.bs.modal', '[data-dismiss="modal"]', $.proxy(this.hide, this));

		this.$dialog.on('mousedown.dismiss.bs.modal', function () {
			that.$element.one('mouseup.dismiss.bs.modal', function (e) {
				if ($(e.target).is(that.$element)) {
					that.ignoreBackdropClick = true;
				}
			});
		});

		this.backdrop(function () {
			var transition = $.support.transition && that.$element.hasClass('fade');

			if (!that.$element.parent().length) {
				that.$element.appendTo(that.$body); // don't move modals dom position
			}

			that.$element
				.show()
				.scrollTop(0);

			that.adjustDialog();

			if (transition) {
				that.$element[0].offsetWidth; // force reflow
			}

			that.$element.addClass('in');

			that.enforceFocus();

			var e = $.Event('shown.bs.modal', { relatedTarget: _relatedTarget });

			transition ?
			that.$dialog // wait for modal to slide in
			.one('bsTransitionEnd', function () {
				that.$element.trigger('focus').trigger(e);
			})
			.emulateTransitionEnd(Modal.TRANSITION_DURATION) : that.$element.trigger('focus').trigger(e);
		});
	};

	Modal.prototype.hide = function (e) {
		if (e) {
			e.preventDefault();
		}

		e = $.Event('hide.bs.modal');

		this.$element.trigger(e);

		if (!this.isShown || e.isDefaultPrevented()) {
			return;
		}

		this.isShown = false;

		this.escape();
		this.resize();

		$(document).off('focusin.bs.modal');

		this.$element
			.removeClass('in')
			.off('click.dismiss.bs.modal')
			.off('mouseup.dismiss.bs.modal');

		this.$dialog.off('mousedown.dismiss.bs.modal');

		$.support.transition && this.$element.hasClass('fade') ?
			this.$element
			.one('bsTransitionEnd', $.proxy(this.hideModal, this))
			.emulateTransitionEnd(Modal.TRANSITION_DURATION) :
			this.hideModal();
	};

	Modal.prototype.enforceFocus = function () {
		$(document)
			.off('focusin.bs.modal') // guard against infinite focus loop
			.on('focusin.bs.modal', $.proxy(function (e) {
				if (this.$element[0] !== e.target && !this.$element.has(e.target).length) {
					this.$element.trigger('focus');
				}
			}, this));
	};

	Modal.prototype.escape = function () {
		if (this.isShown && this.options.keyboard) {
			$(document).on('keydown.dismiss.bs.modal', this.$element, $.proxy(function (e) {
				e.which === 27 && this.hide();
			}, this));
		} else if (!this.isShown) {
			$(document).off('keydown.dismiss.bs.modal', this.$element);
		}
	};

	Modal.prototype.resize = function () {
		if (this.isShown) {
			$(window).on('resize.bs.modal', $.proxy(this.handleUpdate, this));
		} else {
			$(window).off('resize.bs.modal');
		}
	};

	Modal.prototype.hideModal = function () {
		var that = this;
		this.$element.hide();
		this.backdrop(function () {
			that.$body.removeClass('modal-open');
			that.resetAdjustments();
			that.resetScrollbar();
			that.$element.trigger('hidden.bs.modal');
		});
	};

	Modal.prototype.removeBackdrop = function () {
		this.$backdrop && this.$backdrop.remove();
		this.$backdrop = null;
	};

	Modal.prototype.backdrop = function (callback) {
		var that = this;
		var animate = this.$element.hasClass('fade') ? 'fade' : '';

		if (this.isShown && this.options.backdrop) {
			var doAnimate = $.support.transition && animate;

			this.$backdrop = $(document.createElement('div'))
				.addClass(this.options.backdropClass + ' ' + animate)
				.appendTo(this.options.container);

			this.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
				if (this.ignoreBackdropClick) {
					this.ignoreBackdropClick = false;
					return;
				}
				if (e.target !== e.currentTarget) {
					return;
				}
				this.options.backdrop === 'static' ? this.$element[0].focus() : this.hide();
			}, this));

		if (doAnimate) {
			this.$backdrop[0].offsetWidth; // force reflow
		}

		this.$backdrop.addClass('in');

		if (!callback) {
			return;
		}

		doAnimate ?
			this.$backdrop
			.one('bsTransitionEnd', callback)
			.emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
			callback();

		} else if (!this.isShown && this.$backdrop) {
			this.$backdrop.removeClass('in');

			var callbackRemove = function () {
				that.removeBackdrop();
				callback && callback();
			};
			$.support.transition && this.$element.hasClass('fade') ?
				this.$backdrop
				.one('bsTransitionEnd', callbackRemove)
				.emulateTransitionEnd(Modal.BACKDROP_TRANSITION_DURATION) :
				callbackRemove();

		} else if (callback) {
			callback();
		}
	};

	// these following methods are used to handle overflowing modals

	Modal.prototype.handleUpdate = function () {
		this.adjustDialog();
	};

	Modal.prototype.adjustDialog = function () {
		var modalIsOverflowing = this.$element[0].scrollHeight > document.documentElement.clientHeight;

		this.$element.css({
			paddingLeft:  !this.bodyIsOverflowing && modalIsOverflowing ? this.scrollbarWidth : '',
			paddingRight: this.bodyIsOverflowing && !modalIsOverflowing ? this.scrollbarWidth : ''
		});
	};

	Modal.prototype.resetAdjustments = function () {
		this.$element.css({
			paddingLeft: '',
			paddingRight: ''
		});
	};

	Modal.prototype.checkScrollbar = function () {
		var fullWindowWidth = window.innerWidth;
		if (!fullWindowWidth) { // workaround for missing window.innerWidth in IE8
			var documentElementRect = document.documentElement.getBoundingClientRect();
			fullWindowWidth = documentElementRect.right - Math.abs(documentElementRect.left);
		}
		this.bodyIsOverflowing = document.body.clientWidth < fullWindowWidth;
		this.scrollbarWidth = this.measureScrollbar();
	};

	Modal.prototype.setScrollbar = function () {
		var bodyPad = parseInt((this.options.container.css('padding-right') || 0), 10);
		this.originalBodyPad = document.body.style.paddingRight || '';
		if (this.bodyIsOverflowing) {
			this.options.container.css('padding-right', bodyPad + this.scrollbarWidth);
		}
	};

	Modal.prototype.resetScrollbar = function () {
		this.options.container.css('padding-right', this.originalBodyPad);
	};

	Modal.prototype.measureScrollbar = function () { // thx walsh
		var scrollDiv = document.createElement('div');
		scrollDiv.className = 'modal-scrollbar-measure';
		this.options.container.append(scrollDiv);
		var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
		this.options.container[0].removeChild(scrollDiv);
		return scrollbarWidth;
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, option) {
			var $this = $(target);
			var data = $this.data('modal');
			var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option === 'object' && option);
			if (!data) {
				$this.data('modal', (data = new Modal(target, options)));
			} else {
				data.options = options;
			}
			return data;
		}

	};

});


/* global Box */
Box.Application.addService('cookies', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var Cookies = context.getGlobal('Cookies');

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		set: function (name, value, options) {
			return Cookies.set(name, value, options);
		},
		get: function (name) {
			return Cookies.get(name);
		},
		getJSON: function (name) {
			return Cookies.getJSON(name);
		},
		remove: function (name) {
			return Cookies.remove(name);
		}

	};
});

/* global Box */
Box.Application.addService('multistate-button', function () {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	function MSButton(target, options) {
		this.options = $.extend({}, this.options, options);
		this.target = target;
		this.states = $.map(this.options.states, function (val) {
			return 'is-' + val;
		});
		if (typeof this.options.initContainerClass === 'string' && this.options.initContainerClass.length !== 0) {
			$(target).addClass(this.options.initContainerClass);
		} else {
			$(target).addClass('is-inited');
		}

	}
	MSButton.prototype.changeState = function (state) {
		$(this.target).removeClass(this.states.join(' ')).addClass('is-' + state)
				.find('[class*="state"]').removeClass('init-state active').end().find('.' + state).addClass('active');
		return this;
	};
	MSButton.prototype.getState = function () {
		var stateClasses = $(this.target).find('.active, .init-state').first().attr('class').split(' ');
		for (var i = stateClasses.length; i--;) {
			if (stateClasses[i] === 'init-state' || stateClasses[i] === 'active') {
				stateClasses.splice(i, 1);
			}
		}
		return stateClasses[0];
	};
	MSButton.prototype.temporalState = function (temporalState, options) {
		var $this = this;
		var temporalOptions = $.extend({
			timer: 2500,
			oldState: $this.getState(),
			afterChange: null
		}, options);

		if (typeof temporalState === 'string') {
			$this.changeState(temporalState);
			setTimeout($.proxy(function() {
				this.changeState(temporalOptions.oldState);
				temporalOptions.afterChange.call(this);
			}, $this), temporalOptions.timer);
		} else {
			console.warn('Не задан класс для временного состояния кнопки');
		}
		return this;
	};
	MSButton.prototype.disable = function () {
		$(this.target).prop('disabled', true);
		return this;
	};
	MSButton.prototype.enable = function () {
		$(this.target).prop('disabled', false);
		return this;
	};


	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		create: function (target, options) {
			return new MSButton(target, options);
		}

	};

});


/* global Box */
Box.Application.addService('advertizing', function (aplication) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = aplication.getGlobal('jQuery');
	var cookieService = aplication.getService('cookies');
	var DATA_KEY = 'advertizing_hidden_banners';

	function checkBannerVisibility(target) {
		var elementID = $(target).attr('id');

		return $.inArray(elementID, cookieService.getJSON(DATA_KEY)) === -1 ? false : true;
	}
	function setHiddenInCookies(target, bannersHideTime) {
		var elementID = $(target).attr('id');
		var array = cookieService.getJSON(DATA_KEY) || [];

		if ($.inArray(elementID, array) === -1) {
			array.push(elementID);
			cookieService.set(DATA_KEY, array, { expires: bannersHideTime });
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		show: function (target) {
			$(target).removeClass('hide');
		},
		hide: function (target, bannersHideTime) {
			$(target).addClass('hide');
			setHiddenInCookies(target, bannersHideTime);
		},
		isHidden: function (target) {
			return checkBannerVisibility(target);
		}

	};

});

/* global Box */
Box.Application.addBehavior('banner-close', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var advertService;
	var bannersHideTime;
	var banner;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {
		init: function () {
			advertService = context.getService('advertizing');
			bannersHideTime = context.getConfig('bannersHideTime');
		},
		destroy: function () {
			advertService = null;
			bannersHideTime = null;
			banner = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'hide-banner-link') {
				event.preventDefault();
				banner = $(element).closest('[data-type="advertizing"]');
				advertService.hide(banner, bannersHideTime);
			}
		}
	};

});

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

/* global Box */
Box.Application.addBehavior('play-recorded-broadcasts', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------

	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var authentication;
	var modalService;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			authentication = Box.Application.getModuleConfig(pageModule, 'authentication');
			modalService = context.getService('modal');
		},
		destroy: function () {
			moduleEl = null;
			pageModule = null;
			authentication = null;
			modalService = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'broadcast' && $(event.target).closest('.icon-recorded').length > 0) {
				event.preventDefault();
				if (authentication === true) {
					if ($(element).data('play-flag') === false) {
						var broadcast = $(moduleEl).find($(event.target).closest('.item'));
						var broadcastID = broadcast.data('broadcast-id');
						if (broadcastID !== '' && typeof broadcastID !== 'undefined' && broadcast.hasClass('status-recorded')) {
							// send message
							Box.Application.broadcast('playbroadcast', {
								broadcastID: broadcastID,
								record: false,
								element: element
							});
						}
					}
				} else {
					Box.Application.broadcast('showsigninoverlay');
				}
			}
		}
	};

});

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


/* global Box, clearTimeout */
Box.Application.addModule('city-select', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var select;
	var popover;
	var selectService;
	var popoverService;
	var popoverContent;
	var citiesArr;
	var isCitySelected = false;
	var cookieService;
	var DATA_KEY = 'city_select_data';
	var remoteURL;
	var showCityRequestPopover;
	var writedCookies;
	var citySelect;
	var popoverShowTimer;

	function updateSelectionHTML(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $('<span>' + state.text + '</span>');
		return $state;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			// temporarily disable select with sending to server
			// now only get city by ip with a Yandex API
			// Hide select with CSS & display only city title

			// services
			selectService = context.getService('select');
			popoverService = context.getService('popover');
			cookieService = context.getService('cookies');
			// elements
			moduleEl = context.getElement();
			select = $(moduleEl).find('select');
			popoverContent = '<p>Мы угадали Ваш город?</p><ul><li><a href="#" data-type="select-trigger">Выбрать другой</a></li><li><a href="#" data-type="popover-trigger">Да, угадали</a></li></ul>';
			// configs
			remoteURL = context.getConfig('url');

			// ymaps.geolocation.city

			window.onload = function () {
				$(moduleEl).append( ymaps.geolocation.city )
			}

			citiesArr = context.getConfig('cities');
			showCityRequestPopover = context.getConfig('showCityRequestPopover') || false;
			writedCookies = cookieService.get(DATA_KEY);

			citySelect = selectService.create(select, {
				data: citiesArr,
				templateSelection: updateSelectionHTML,
				theme: 'city-select'
			});

			select.on('select2:opening', function () {
				popover.hide();
				clearTimeout(popoverShowTimer);
			}).on('select2:select', function (event) {
				cookieService.set(DATA_KEY, event.params.data.text, {
					expires: 365
				});
				isCitySelected = true;

				// send form to server
				$('#city-select-value').val(event.params.data.id);
				$('#city-select-form').submit();
			}).on('select2:close', function () {
				if (!isCitySelected) {
					popoverShowTimer = setTimeout(function () {
						popover.show();
					}, 300);
				}
			});
			popover = popoverService.create(select, {
				content: popoverContent,
				trigger: 'manual',
				placement: 'bottom',
				html: true
			});
			if (typeof cookieService.get(DATA_KEY) === 'string' || showCityRequestPopover === false) {
				isCitySelected = true;
			} else {
				popoverShowTimer = setTimeout(function () {
					popover.show();
				}, 3000);
			}
		},
		destroy: function () {
			moduleEl = null;
			select = null;
			selectService = null;
			popover = null;
			showCityRequestPopover = null;
			popoverService = null;
			popoverContent = null;
			citiesArr = null;
			cookieService = null;
			remoteURL = null;
			writedCookies = null;
			citySelect = null;
			clearTimeout(popoverShowTimer);
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'select-trigger') {
				event.preventDefault();
				popover.hide();
				citySelect.open();
			} else if (elementType === 'popover-trigger') {
				event.preventDefault();
				var selectedCity = select.find('option[value="' + select.val() + '"]').text();
				if (typeof selectedCity === 'string' && typeof writedCookies !== 'string') {
					cookieService.set(DATA_KEY, selectedCity, {
						expires: 365
					});
					clearTimeout(popoverShowTimer);
				}
				popover.hide();
			}
		}

	};
});

/* global Box, clearTimeout */
Box.Application.addModule('lang-select', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var select;
	var popover;
	var selectService;
	var popoverService;
	var popoverContent;
	var languagesArr;
	var isLangSelected = false;
	var remoteURL;
	var langSelect;

	function updateSelectionHTML(state) {
		if (!state.id) {
			return state.text;
		}
		var $state = $('<span>' + state.text + '</span>');
		return $state;
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {

			// services
			selectService = context.getService('select');

			// elements
			moduleEl = context.getElement();
			select = $(moduleEl).find('select');

			// configs
			remoteURL = context.getConfig('url');
			languagesArr = context.getConfig('languages');

			langSelect = selectService.create(select, {
				data: languagesArr,
				templateSelection: updateSelectionHTML,
				theme: 'lang-select'
			});

			select.on('select2:select', function (event) {
				isLangSelected = true;

				// send form to server
				$('#lang-select-value').val(event.params.data.id);
				$('#lang-select-form').submit();
			});
		},
		destroy: function () {
			moduleEl = null;
			select = null;
			selectService = null;
			popover = null;
			popoverService = null;
			popoverContent = null;
			languagesArr = null;
			remoteURL = null;
			langSelect = null;
		}
	};
});

/* global Box */
Box.Application.addModule('search-form', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var Bloodhound = context.getGlobal('Bloodhound');
	var moduleEl;
	var searchField;
	var bloodhoundObj;
	var remoteUrl;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			searchField = $(moduleEl).find('[data-type="search-field"]');
			remoteUrl = context.getConfig('url');

			bloodhoundObj = new Bloodhound({
				datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				remote: {
					url: remoteUrl,
					wildcard: '%QUERY'
				}
			});

			searchField.typeahead({
				minLength: 3
			}, {
				name: 'search-form',
				source: bloodhoundObj,
				templates: {
					suggestion: function (data) {
						var resultHTML = '';

						resultHTML += '<a class="search-result" href="' + data.link + '">';

						if (data.thumbnail === null) {
							resultHTML += '<span class="image-holder is-empty"></span>';
						} else {
							resultHTML += '<span class="image-holder"><img alt="' + data.title + '" width="60" height="60" src="' + data.thumbnail + '"></span>';
						}

						resultHTML += '<span class="info-col"><span class="publish-date">' + data.date + '</span><h5 class="result-title">' + data.title + '</h5></span></a>';

						return resultHTML;
					}
				}
			});
			$(moduleEl).on('typeahead:render typeahead:open', searchField, function () {
				var serchResults = searchField.closest('.form-group').find('.search-result').length;
				if (searchField.val() !== '' && serchResults > 0) {
					$(this).addClass('is-show-results');
				}
				if (serchResults < 1) {
					$(this).removeClass('is-show-results');
				}
			}).on('typeahead:close', searchField, function () {
				$(this).removeClass('is-show-results');
			});
		},
		destroy: function () {
			moduleEl = null;
			searchField = null;
			bloodhoundObj = null;
			remoteUrl = null;
		},
		onkeyup: function () {
			if (searchField.val().length < 3) {
				$(moduleEl).removeClass('is-show-results');
			}
		}
	};
});


/* global Box */
Box.Application.addModule('recomended-broadcasts', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var listItems;
	var kineticService;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		behaviors: ['recording-broadcast', 'play-recorded-broadcasts'],

		init: function () {
			kineticService = context.getService('kinetic');
			moduleEl = context.getElement();
			listItems = $(moduleEl).find('.block-body');
			$(moduleEl).find('[data-type="broadcast"]').data('status-flag', false).data('play-flag', false);
			kineticService.create(listItems, {
				y: false,
				triggerHardware: true,
				movingClass: {},
				deceleratingClass: {},
				filterTarget: function (target) {
					if ($(target).closest('.item-status-icon').length) {
						return false;
					}
				}
			});
		},
		destroy: function () {
			kineticService = null;
			moduleEl = null;
			listItems = null;
		}

	};
});


/* global Box */
Box.Application.addModule('channel-card', function (context) {
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

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		behaviors: ['recording-broadcast', 'play-recorded-broadcasts'],

		init: function () {
			kineticService = context.getService('kinetic');
			timelineService = context.getService('timeline');
			moduleEl = context.getElement();
			listItems = $(moduleEl).find('.channel-broadcasts');
			timelineElements = $(moduleEl).find('.timeline');
			items = $(moduleEl).find('.item');

			$(moduleEl).find('[data-type="broadcast"]').data('status-flag', false).data('play-flag', false);

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
			timelineElements.each(function () {
				timelineService.create(this);
			});
			$(moduleEl).on('mouseenter', '.item', function () {
				if (!$(this).hasClass('show-descr')) {
					$(this).addClass('hover');
				}
			}).on('mouseleave', '.item', function () {
				$(this).removeClass('hover');
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
			if (elementType === 'descr-trigger') {
				if (!$item.hasClass('show-descr')) {
					$item.removeClass('hover').addClass('show-descr');
				} else {
					$item.removeClass('show-descr').addClass('hover');
				}
			}
		}

	};
});

/* global Box */
Box.Application.addModule('user-navigation', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var avatarHolder;
	var recordingCount;

	function updateRecordingCount(element) {
		var countValue = parseInt(element.text(), 10) + 1;
		element.text(countValue);
	}
	function updateUserAvatar(element, thumbnail) {
		if (element.hasClass('is-empty')) {
			element.find('img').remove().end().append($('<img src="' + thumbnail + '" width="50" height="50" />')).removeClass('is-empty');
		} else {
			element.find('img').attr('src', thumbnail);
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['useravatarupdated', 'updatebroadcaststatus'],

		init: function () {
			moduleEl = context.getElement();
			avatarHolder = $(moduleEl).find('[data-type="avatar-holder"]');
			recordingCount = $(moduleEl).find('[data-type="recording-count"]');
		},
		destroy: function () {
			moduleEl = null;
			avatarHolder = null;
			recordingCount = null;
		},
		onmessage: function (name, data) {
			switch (name) {
				case 'useravatarupdated':
					updateUserAvatar(avatarHolder, data.thumbnail);
					break;
				case 'updatebroadcaststatus':
					updateRecordingCount(recordingCount);
					break;
			}
		}

	};
});

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
				width: 896,
				height: 504,
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

/* global Box, alert */
Box.Application.addModule('broadcast-comments', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var form;
	var formBlock;
	var formCollapseTrigger;
	var commentsBlock;
	var commentsList;
	var formSubmit;
	var formTextarea;
	var submitFlag = false;

	function changeCollapseTriggerState(state) {
		switch (state) {
			case 'active':
				formCollapseTrigger.html('<span>Скрыть</span>');
			break;
			case 'passive':
				formCollapseTrigger.html('<span data-icon="icon-paper-airplane"></span><span>Оставить отзыв</span>');
				Box.Application.renderIcons(context);
			break;
		}
	}

	function showCollapseTrigger() {
		formCollapseTrigger.removeClass('hidden');
	}

	function showCommentsBlock() {
		commentsBlock.collapse('show');
	}

	function addComment(data) {
		var commentHTML = '';
		if (typeof data !== 'undefined') {
			commentHTML += '<li>' +
							'<div class="user-avatar">' +
								'<img src="' + data.user_avatar + '" alt="' + data.username + '" width="50" height="50">' +
							'</div>' +
							'<div class="comment-holder">' +
								'<div class="comment-title">' + data.username + ' | ' + data.publish_date + '</div>' +
								'<div class="comment-text">' + data.comment_text + '</div>' +
							'</div>' +
							'</li>';
			commentsList.prepend(commentHTML);
		} else {
			console.warn('Объект с данными для нового комментария пуст');
			return;
		}
	}

	function sendComment(dataObj) {
		$(moduleEl).find('.form-group').removeClass('has-error');

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: dataObj,
			error: function () {
				alert('Что-то пошло не так. Повторите попытку позднее!');
				formSubmit.removeClass('is-submit-progress').trigger('blur');
			},
			success: function (data) {
				if (data.status === 'success') {
					form.find('.form-control').val('');
					addComment(data);
					if (formCollapseTrigger.hasClass('hidden') === true) {
						changeCollapseTriggerState('active');
						showCollapseTrigger();
						formCollapseTrigger.addClass('active');
					}
					if (commentsBlock.hasClass('in') === false || commentsBlock.hasClass('collapsing') === false) {
						showCommentsBlock();
					}
					formSubmit.removeClass('is-submit-progress');
					submitFlag = false;
				} else if (data.status === 'fail') {
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter(form.find('input[name="' + key + '"], textarea[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
					formSubmit.removeClass('is-submit-progress');
					submitFlag = false;
				} else {
					alert('Что-то пошло не так. Повторите попытку позднее!');
					formSubmit.removeClass('is-submit-progress');
					submitFlag = false;
				}
			}
		});
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			formBlock = $(moduleEl).find('.broadcast-user-comments-form');
			formCollapseTrigger = $(moduleEl).find('.comment-form-trigger-link');
			form = $(moduleEl).find('form');
			commentsBlock = $(moduleEl).find('.broadcast-user-comments');
			commentsList = commentsBlock.find('.comments-list');
			formSubmit = form.find('button:submit');
			formTextarea = form.find('textarea');
		},
		destroy: function () {
			moduleEl = null;
			form = null;
			formBlock = null;
			formCollapseTrigger = null;
			commentsBlock = null;
			commentsList = null;
			formSubmit = null;
			formTextarea = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'comment-form-trigger') {
				if ($(element).hasClass('active')) {
					formBlock.collapse('hide');
					$(element).removeClass('active');
					changeCollapseTriggerState('passive');
				} else {
					formBlock.collapse('show');
					$(element).addClass('active');
					changeCollapseTriggerState('active');
				}
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			var textareaValue = formTextarea.val();
			if (submitFlag === false && textareaValue.length > 0) {
				formSubmit.addClass('is-submit-progress');
				sendComment(form.serialize());
			}
		}

	};
});

/* global Box */
Box.Application.addModule('signin-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var passwordVisualizer;
	var adaptiveField;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var form;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
		adaptiveField.trigger('focus');
		hideOtherOverlays();
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}
	function toggleOverlay() {
		$(moduleEl).toggleClass('is-visible');

		if ($(moduleEl).hasClass('is-visible') === true) {
			adaptiveField.trigger('focus');
			hideOtherOverlays();
		}
	}
	function sendForm() {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: form.serialize(),
			beforeSend: function () {
				removeFormErrors();
				msSubmitButton.disable();
			},
			error: function () {
				msSubmitButton.temporalState('fail-network-state', {
					afterChange: function () {
						this.enable();
					}
				});
			},
			success: function (data) {
				if (data.status === 'error') {
					msSubmitButton.temporalState('fail-data-state', {
						afterChange: function () {
							this.enable();
						}
					});
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					msSubmitButton.changeState('done-state').enable();
					window.location.href = form.data('redirect');
				}
			}
		});
	}
	function removeFormErrors() {
		form.find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}
	function hideOtherOverlays() {
		Box.Application.broadcast('hideresetoverlay');
		Box.Application.broadcast('hidesignupoverlay');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsigninoverlay', 'hidesigninoverlay', 'togglesigninoverlay'],

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			form = $(moduleEl).find('form');
			msbuttonService = context.getService('multistate-button');
			adaptiveField = $(moduleEl).find('[data-type="adaptive-field"]');
			passwordVisualizer = $(moduleEl).find('[data-type="password-visualizer"]');
			submitButton = $(moduleEl).find('button:submit[data-type="multistate-button"]');
			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			moduleEl = null;
			passwordVisualizer = null;
			adaptiveField = null;
			submitButton = null;
			msbuttonService = null;
			msSubmitButton = null;
			form = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsigninoverlay':
					showOverlay();
				break;
				case 'hidesigninoverlay':
					hideOverlay();
				break;
				case 'togglesigninoverlay':
					toggleOverlay();
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'password-visibility-toggle') {
				event.preventDefault();
				passwordVisualizer.toggleClass('is-visible');
				$(element).toggleClass('active');
			} else if (elementType === 'social-signin-link') {
				msSubmitButton.changeState('done-state');
			} else if (elementType === 'reset-handler-link') {
				event.preventDefault();
				hideOverlay();
				Box.Application.broadcast('showresetoverlay');
			}
		},
		onkeyup: function (event, element, elementType) {
			if (elementType === 'password-field') {
				$(moduleEl).find('[data-type="password-visualizer"]').val($(element).val());
			}
			if (elementType === 'password-visualizer') {
				$(moduleEl).find('[data-type="password-field"]').val($(element).val());
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}
	};
});


/* global Box */
Box.Application.addModule('signup-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var adaptiveField;
	var msbuttonService;
	var submitButton;
	var msSubmitButton;
	var submitCodeButton;
	var msSubmitCodeButton;
	var form;
	var steps;
	var codeField;
	var adaptiveFieldHidden;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
		adaptiveField.trigger('focus');
		hideOtherOverlays();
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}
	function toggleOverlay() {
		$(moduleEl).toggleClass('is-visible');

		if ($(moduleEl).hasClass('is-visible') === true) {
			adaptiveField.trigger('focus');
			hideOtherOverlays();
		}
	}
	function sendForm() {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: form.serialize(),
			beforeSend: function () {
				removeFormErrors();
				if (form.data('type') !== 'signup-code-form') {
					if (adaptiveField.val() !== '') {
						adaptiveFieldHidden.val(adaptiveField.val());
					}
					msSubmitButton.disable();
				} else {
					msSubmitCodeButton.disable();
				}
			},
			error: function () {
				if (form.data('type') !== 'signup-code-form') {
					msSubmitButton.temporalState('fail-network-state', {
						afterChange: function () {
							this.enable();
						}
					});
				} else {
					msSubmitCodeButton.temporalState('fail-network-state', {
						afterChange: function () {
							this.enable();
						}
					});
				}
			},
			success: function (data) {
				if (data.status === 'error') {
					if (form.data('type') !== 'signup-code-form') {
						msSubmitButton.temporalState('fail-data-state', {
							afterChange: function () {
								this.enable();
							}
						});
					} else {
						msSubmitCodeButton.temporalState('fail-data-state', {
							afterChange: function () {
								this.enable();
							}
						});
					}
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else if (data.status === 'need_confirm') {
					if (form.data('type') !== 'signup-code-form') {
						msSubmitButton.changeState('default-state').enable();
						showNextStep();
					}
				} else if (data.status === 'success') {
					if (form.data('type') !== 'signup-code-form') {
						msSubmitButton.changeState('default-state').enable();
						showNextStep();
					} else {
						msSubmitCodeButton.changeState('default-state').enable();
						hideOverlay();
						Box.Application.broadcast('showsuccesssignupoverlay');
					}
				}
			}
		});
	}
	function removeFormErrors() {
		form.find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}
	function hideOtherOverlays() {
		Box.Application.broadcast('hideresetoverlay');
		Box.Application.broadcast('hidesigninoverlay');
	}
	function showNextStep() {
		var activeItem = steps.find('.step.active');
		activeItem.removeClass('in');
		setTimeout(function () {
			activeItem.removeClass('active');
			activeItem.next('.step').addClass('active in');
			codeField.trigger('focus');
			reinit();
		}, 300);
	}
	function reinit() {
		form = steps.find('.step.active').find('form');
	}
	function resetOverlay() {
		adaptiveFieldHidden.val('');
		steps.find('.step').removeClass('in active').first().addClass('in active');
		form = steps.find('.step.active form');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsignupoverlay', 'hidesignupoverlay', 'togglesignupoverlay'],

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			steps = $(moduleEl).find('.steps');
			form = steps.find('.step.active').find('form');
			msbuttonService = context.getService('multistate-button');
			adaptiveField = $(moduleEl).find('[data-type="adaptive-field"]');
			adaptiveFieldHidden = $(moduleEl).find('[data-type="adaptive-field-hidden"]');
			codeField = $(moduleEl).find('[data-type="code-field"]');
			submitButton = $(moduleEl).find('form:not([data-type="signup-code-form"]) button:submit[data-type="multistate-button"]');
			submitCodeButton = $(moduleEl).find('form[data-type="signup-code-form"] button:submit[data-type="multistate-button"]');
			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'fail-data-state', 'fail-network-state']
			});
			msSubmitCodeButton = msbuttonService.create(submitCodeButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			moduleEl = null;
			adaptiveField = null;
			submitButton = null;
			msbuttonService = null;
			msSubmitButton = null;
			form = null;
			steps = null;
			codeField = null;
			adaptiveFieldHidden = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsignupoverlay':
					showOverlay();
				break;
				case 'hidesignupoverlay':
					hideOverlay();
				break;
				case 'togglesignupoverlay':
					toggleOverlay();
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'social-signin-link') {
				msSubmitButton.changeState('done-state');
			} else if (elementType === 'reset-handler-link') {
				event.preventDefault();
				hideOverlay();
				Box.Application.broadcast('showresetoverlay');
			} else if (elementType === 'signin-handler-link') {
				event.preventDefault();
				hideOverlay();
				Box.Application.broadcast('showsigninoverlay');
				setTimeout(resetOverlay, 300);
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}
	};
});


/* global Box */
Box.Application.addModule('reset-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;
	var pageModule;
	var adaptiveField;
	var codeField;
	var msbuttonService;
	var submitButton;
	var submitCodeButton;
	var msSubmitButton;
	var msSubmitCodeButton;
	var form;
	var steps;
	var passwordVisualizer;
	var passwordField;
	var adaptiveFieldHidden;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
		adaptiveField.trigger('focus');
		hideOtherOverlays();
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}
	function sendForm() {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: form.attr('action'),
			data: form.serialize(),
			beforeSend: function () {
				removeFormErrors();
				if (form.data('type') !== 'reset-code-form') {
					if (adaptiveField.val() !== '') {
						adaptiveFieldHidden.val(adaptiveField.val());
					}
					msSubmitButton.disable();
				} else {
					msSubmitCodeButton.disable();
				}
			},
			error: function () {
				if (form.data('type') !== 'reset-code-form') {
					msSubmitButton.temporalState('fail-network-state', {
						afterChange: function () {
							this.enable();
						}
					});
				} else {
					msSubmitCodeButton.temporalState('fail-network-state', {
						afterChange: function () {
							this.enable();
						}
					});
				}
			},
			success: function (data) {
				if (data.status === 'error') {
					if (form.data('type') !== 'reset-code-form') {
						msSubmitButton.temporalState('fail-data-state', {
							afterChange: function () {
								this.enable();
							}
						});
					} else {
						msSubmitCodeButton.temporalState('fail-data-state', {
							afterChange: function () {
								this.enable();
							}
						});
					}
					var errors = data.errors;
					$.each(errors, function (key, value) {
						$('<span class="form-control-message">' + value + '</span>')
							.insertAfter($(moduleEl).find('input[name="' + key + '"]').closest('.form-group').addClass('has-error').find('.form-control'));
					});
				} else {
					if (form.data('type') !== 'reset-code-form') {
						msSubmitButton.changeState('default-state').enable();
						showNextStep();
					} else {
						msSubmitCodeButton.changeState('default-state').enable();
						hideOverlay();
						Box.Application.broadcast('showsuccessresetoverlay');
					}
				}
			}
		});
	}
	function removeFormErrors() {
		form.find('.form-group').removeClass('has-error').end().find('.form-control-message').remove();
	}
	function hideOtherOverlays() {
		Box.Application.broadcast('hidesigninoverlay');
		Box.Application.broadcast('hidesignupoverlay');
	}
	function showNextStep() {
		var activeItem = steps.find('.step.active');
		activeItem.removeClass('in');
		setTimeout(function () {
			activeItem.removeClass('active');
			activeItem.next('.step').addClass('active in');
			codeField.trigger('focus');
			reinit();
		}, 300);
	}
	function reinit() {
		form = steps.find('.step.active').find('form');
	}
	function resetOverlay() {
		adaptiveFieldHidden.val('');
		steps.find('.step').removeClass('in active').first().addClass('in active');
		form = steps.find('.step.active form');

		if (form.data('type') === 'reset-code-form') {
			adaptiveField.val('');
		}
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showresetoverlay', 'hideresetoverlay'],

		init: function () {
			moduleEl = context.getElement();
			pageModule = $('[data-module="page"]').get(0);
			steps = $(moduleEl).find('.steps');
			form = steps.find('.step.active').find('form');
			msbuttonService = context.getService('multistate-button');
			adaptiveField = $(moduleEl).find('[data-type="adaptive-field"]');
			adaptiveFieldHidden = $(moduleEl).find('[data-type="adaptive-field-hidden"]');
			codeField = $(moduleEl).find('[data-type="code-field"]');
			passwordVisualizer = $(moduleEl).find('[data-type="password-visualizer"]');
			passwordField = $(moduleEl).find('[data-type="password-field"]');
			submitButton = $(moduleEl).find('form:not([data-type="reset-code-form"]) button:submit[data-type="multistate-button"]');
			submitCodeButton = $(moduleEl).find('form[data-type="reset-code-form"] button:submit[data-type="multistate-button"]');
			msSubmitButton = msbuttonService.create(submitButton, {
				states: ['default-state', 'fail-data-state', 'fail-network-state']
			});
			msSubmitCodeButton = msbuttonService.create(submitCodeButton, {
				states: ['default-state', 'done-state', 'fail-data-state', 'fail-network-state']
			});
		},
		destroy: function () {
			moduleEl = null;
			adaptiveField = null;
			submitButton = null;
			msbuttonService = null;
			msSubmitButton = null;
			msSubmitCodeButton = null;
			form = null;
			passwordVisualizer = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showresetoverlay':
					showOverlay();
				break;
				case 'hideresetoverlay':
					hideOverlay();
				break;
			}
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'password-visibility-toggle') {
				event.preventDefault();
				passwordVisualizer.toggleClass('is-visible');
				$(element).toggleClass('active');
			} else if (elementType === 'signin-handler-link') {
				event.preventDefault();
				hideOverlay();
				resetOverlay();
				Box.Application.broadcast('showsigninoverlay');
			} else if (elementType === 'reset-code-handler-link') {
				event.preventDefault();
				showNextStep();
			} else if  (elementType === 'reset-handler-link') {
				event.preventDefault();
				resetOverlay();
				adaptiveField.trigger('focus');
			}
		},
		onkeyup: function (event, element, elementType) {
			if (elementType === 'password-field') {
				passwordVisualizer.val($(element).val());
			}
			if (elementType === 'password-visualizer') {
				passwordField.val($(element).val());
			}
		},
		onsubmit: function (event) {
			event.preventDefault();
			sendForm();
		}
	};
});


/* global Box */
Box.Application.addModule('success-signup-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsuccesssignupoverlay'],

		init: function () {
			moduleEl = context.getElement();
		},
		destroy: function () {
			moduleEl = null;
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsuccesssignupoverlay':
					showOverlay();
					setTimeout(function () {
						window.location = '/';
					}, 3000);
				break;
			}
		}
	};
});


/* global Box */
Box.Application.addModule('success-reset-overlay', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var moduleEl;

	function showOverlay() {
		$(moduleEl).addClass('is-visible');
	}
	function hideOverlay() {
		$(moduleEl).removeClass('is-visible');
	}

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		messages: ['showsuccessresetoverlay', 'hidesuccessresetoverlay'],

		init: function () {
			moduleEl = context.getElement();
		},
		destroy: function () {
			moduleEl = null;
		},
		onclick: function (event, element, elementType) {
			if (elementType === 'signin-handler-link') {
				event.preventDefault();
				hideOverlay();
				Box.Application.broadcast('showsigninoverlay');
			}
		},
		onmessage: function (name) {
			switch (name) {
				case 'showsuccessresetoverlay':
					showOverlay();
				break;
				case 'hidesuccessresetoverlay':
					hideOverlay();
				break;
			}
		}
	};
});


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

/* global Box */
'use strict';

jQuery.extend(jQuery.lazyLoadXT, {
  edgeX: 800,
  throttle: 50
});

// add easing for animation
jQuery.easing.jswing = jQuery.easing.swing;

jQuery.extend(jQuery.easing, {
	def: 'easeOutQuad',
	swing: function (x, t, b, c, d) {
		return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
	},
	easeOutQuad: function (x, t, b, c, d) {
		return -c *(t/=d)*(t-2) + b;
	},
	easeInOutCubic: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) {
			return c/2*t*t*t + b;
		}
		return c/2*((t-=2)*t*t + 2) + b;
	}
});


Box.Application.init({ debug: true });

$(function () {
	$('.recommend-form-wrap select').select2({
		theme: 'default'
	});
});
