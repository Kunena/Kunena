/**
 * Kunena Component
 * @package Kunena.Template.Crypsis
 *
 * @copyright     Copyright (C) 2008 - 2019 Kunena Team. All rights reserved.
 * @license https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link https://www.kunena.org
 **/

jQuery(document).ready(function ($) {
	var kurlusers = $('#kurl_users');

	/* Provide autocomplete user list in search form and in user list */
	if (kurlusers.length > 0) {
		var users_url = kurlusers.val();

		$('#kusersearch').atwho({
			at: "",
			displayTpl: '<li data-value="${name}"><img src="${photo}" width="20px" /> ${name} <small>(${name})</small></li>',
			limit: 5,
			callbacks: {
				remoteFilter: function (query, callback) {
					$.ajax({
						url: users_url,
						data: {
							search: query
						}
					})
						.done(function (data) {
							callback(data);
						})
						.fail(function () {
							//TODO: handle the error of ajax request
						});
				}
			}
		});
	}

	/* Hide search form when there are search results found */
	if ($('#kunena_search_results').is(':visible')) {
		$('#search').collapse("hide");
	}

	if (jQuery.fn.datepicker !== undefined) {
		jQuery('#searchatdate .input-append.date').datepicker({
			orientation: "top auto",
			language: "kunena"
		});
	}
});
