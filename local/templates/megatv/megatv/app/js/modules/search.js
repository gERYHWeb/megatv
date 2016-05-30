/* global Box */
Box.Application.addModule('search', function (context) {
	'use strict';

	// --------------------------------------------------------------------------
	// Private
	// --------------------------------------------------------------------------
	var $ = context.getGlobal('jQuery');
	var Bloodhound = context.getGlobal('Bloodhound');
	var moduleEl;
	var searchGroup;
	var searchField;
	var bloodhoundObj;
	var remoteUrl;
	var body;

	// --------------------------------------------------------------------------
	// Public
	// --------------------------------------------------------------------------

	return {

		init: function () {
			moduleEl = context.getElement();
			searchField = $(moduleEl).find('[data-type="search-field"]');
			searchGroup = $(moduleEl).find('[data-type="search-group"]');
			remoteUrl = context.getConfig('url');
			body = $('body');

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
		onclick: function (event, element, elementType) {
			if (elementType === 'open') {
				body.addClass('search-opened');
				setTimeout(function() {
					searchField.focus();
				}, 500);
			} else if (elementType === 'close') {
				body.removeClass('search-opened');
			}
		},
		onkeyup: function () {
			if (searchField.val().length < 3) {
				$(moduleEl).removeClass('is-show-results');
			}
		}
	};
});

