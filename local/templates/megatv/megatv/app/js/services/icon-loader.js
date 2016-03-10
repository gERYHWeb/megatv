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
			var revision = 1447075965;
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
