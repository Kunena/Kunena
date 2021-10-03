/**
 * Kunena Component
 * @package Kunena.Template.Aurelia
 *
 * @copyright     Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link https://www.kunena.org
 **/

jQuery(document).ready(function ($) {
    const qreply = $('.qreply');
    const editor = $('#editor');
    const pollcategoriesid = jQuery.parseJSON(Joomla.getOptions('com_kunena.pollcategoriesid'));
    const arrayanynomousbox = jQuery.parseJSON(Joomla.getOptions('com_kunena.arrayanynomousbox'));
    const pollexist = $('#poll_exist_edit');
    const pollcatid = jQuery('#poll_catid').val();
    const polliconstatus = false;

    // Check is anynomous options can be displayed on newtopic page
    const catiddefault = $('#postcatid').val();

    if (arrayanynomousbox !== null) {
        if (arrayanynomousbox[catiddefault] == 1) {
            $('#kanonymous').prop('checked', true);
        }
    }

    $('#reset').onclick = function () {
        localStorage.removeItem('copyKunenaeditor');
    };

    /* To enabled emojis in kunena textera feature like on github */
    if ($('#kemojis_allowed').val() == 1) {
        let item = '';
        if (editor.length > 0 && qreply.length == 0) {
            item = '#editor';
        } else if (qreply.length > 0) {
            item = '.qreply';
        }

        if (item != undefined) {
            /*$(item).atwho({
                at: ":",
                displayTpl: "<li data-value='${key}'>${name} <img src='${url}' height='20' width='20' /></li>",
                insertTpl: '${name}',
                callbacks: {
                    remoteFilter: function (query, callback) {
                        if (query.length > 0) {
                            $.ajax({
                                url: $("#kurl_emojis").val(),
                                data: {
                                    search: query
                                }
                            })
                                .done(function (data) {
                                    callback(data.emojis);
                                })
                                .fail(function () {
                                    //TODO: handle the error of ajax request
                                });
                        }
                    }
                }
            });*/
        }
    }

    /*if (item !== undefined) {
        const users_url = $('#kurl_users').val();
        $(item).atwho({
            at: "@",
            data: users_url,
            limit: 5
        });
    }*/


    /* Store form data into localstorage every 1 second */
    if ($.fn.sisyphus !== undefined) {
        $("#postform").sisyphus({
            locationBased: true,
            timeout: 5
        });
    }

    $('#kshow_attach_form').click(function () {
        if ($('#kattach_form').is(":visible")) {
            $('#kattach_form').hide();
        } else {
            $('#kattach_form').show();
        }
    });

    $('#form_submit_button').click(function () {
        $("#subject").attr('required', 'required');
        $("#editor").attr('required', 'required');
        localStorage.removeItem('copyKunenaeditor');
    });

    // Needed to open and close quickreply from on blue Eagle5
    $('.Kreplyclick').click(function () {
        const name = '#' + $(this).attr('data-related');
        if ($(name).is(":visible")) {
            $(name).hide();
        } else {
            $(name).show();
        }
    });

    $('.kreply-cancel').click(function () {
        const name = '#' + $(this).attr('data-related');
        $(name).hide();
    });

    let category_template_text = null;
    $('#postcatid').change(function () {
        const catid = $('select#postcatid option').filter(':selected').val();
        const kurl_topicons_request = $('#kurl_topicons_request').val();
        const pollcategoriesid = jQuery.parseJSON(Joomla.getOptions('com_kunena.pollcategoriesid'));
        const pollexist = jQuery('#poll_exist_edit');
        const pollcatid = jQuery('#poll_catid').val();
        const polliconstatus = true;

        if (typeof pollcategoriesid !== 'undefined' && pollcategoriesid !== null && pollexist.length === 0) {
            if (pollcatid !== undefined) {
                const catid = jQuery('#kcategory_poll').val();
            }

            if (pollcategoriesid[catid] !== undefined) {
                CKEDITOR.instances.message.getCommand('polls').enable();

            } else {
                CKEDITOR.instances.message.getCommand('polls').disable();

            }
        } else if (pollexist.length > 0) {
            CKEDITOR.instances.message.getCommand('polls').enable();

        } else {
            CKEDITOR.instances.message.getCommand('polls').disable();

        }

        if ($('#kanynomous-check').length > 0) {
            if (arrayanynomousbox[catid] !== undefined) {
                $('#kanynomous-check').show();
                $('#kanonymous').prop('checked', true);
            } else {
                $('#kanynomous-check').hide();
                $('#kanonymous').prop('checked', false);
            }
        }

        // Load topic icons by ajax request
        $.ajax({
            type: 'POST',
            url: kurl_topicons_request,
            async: true,
            dataType: 'json',
            data: {catid: catid}
        })
            .done(function (data) {
                $('#iconset_topic_list').remove();

                const div_object = $('<div>', {'id': 'iconset_topic_list'});

                $('#iconset_inject').append(div_object);

                $.each(data, function (index, value) {
                    if (value.type !== 'system') {
                        if (value.id === 0) {
                            const input = $('<input>', {
                                type: 'radio',
                                id: 'radio' + value.id,
                                name: 'topic_emoticon',
                                value: value.id
                            }).prop('checked', true);
                        } else {
                            const input = $('<input>', {
                                type: 'radio',
                                id: 'radio' + value.id,
                                name: 'topic_emoticon',
                                value: value.id
                            });
                        }

                        const span_object = $('<span>', {'class': 'kiconsel'}).append(input);

                        if (Joomla.getOptions('com_kunena.kunena_topicicontype') === 'B2') {
                            const label = $('<label>', {
                                'class': 'radio inline',
                                'for': 'radio' + value.id
                            }).append($('<span>', {
                                'class': 'icon icon-topic icon-' + value.b2,
                                'border': '0',
                                'al': ''
                            }));
                        } else if (Joomla.getOptions('com_kunena.kunena_topicicontype') === 'fa') {
                            const label = $('<label>', {
                                'class': 'radio inline',
                                'for': 'radio' + value.id
                            }).append($('<i>', {
                                'class': 'fa glyphicon-topic fa-2x fa-' + value.fa,
                                'border': '0',
                                'al': ''
                            }));
                        } else {
                            const label = $('<label>', {
                                'class': 'radio inline',
                                'for': 'radio' + value.id
                            }).append($('<img>', {'src': value.path, 'border': '0', 'al': ''}));
                        }

                        span_object.append(label);

                        $('#iconset_topic_list').append(span_object);
                    }
                });
            })
            .fail(function () {
                //TODO: handle the error of ajax request
            });

        // Load template text for the category by ajax request
        category_template_text = function cat_template_text() {
            return $.ajax({
                type: 'POST',
                url: $('#kurl_category_template_text').val(),
                async: true,
                dataType: 'json',
                data: {catid: catid}
            })
                .done(function (data) {
                    const editor_text = CKEDITOR.instances.message.getData();

                    if (editor_text.length > 1) {
                        if (editor_text.length > 1) {
                            $('#modal_confirm_template_category').modal('show');
                        } else {
                            CKEDITOR.instances.message.setData(category_template_text);
                        }
                    } else {
                        if (data.length > 1) {
                            $('#modal_confirm_template_category').modal('show');
                        } else {
                            CKEDITOR.instances.message.setData(data);
                        }
                    }

                })
                .fail(function () {
                    //TODO: handle the error of ajax request
                });
        }();
    });

    $('#modal_confirm_erase').click(function () {
        $('#modal_confirm_template_category').modal('hide');
        const textarea = $("#editor").next();
        textarea.empty();
        CKEDITOR.instances.message.setData(category_template_text.responseJSON);
    });

    $('#modal_confirm_erase_keep_old').click(function () {
        $('#modal_confirm_template_category').modal('hide');
        const existing_content = CKEDITOR.instances.message.getData();
        const textarea = $("#editor").next();
        textarea.empty();
        CKEDITOR.instances.message.setData(category_template_text.responseJSON + ' ' + existing_content);
    });

    if ($.fn.datepicker !== undefined) {
        // Load datepicker for poll
        $('#datepoll-container .input-append.date').datepicker({
            orientation: "top auto"
        });
    }

	var toolbar_buttons = '';
	if(Joomla.getOptions('com_kunena.template_editor_buttons_configuration') !== undefined)
	{
		// TODO: need to change the values(bold, italic) from template parameters to be handled here
		toolbar_buttons = 'bold,italic,underline,strike,subscript,superscript|left,center,right,justify|font,size,color,removeformat|cut,copy,paste|bulletlist,orderedlist|table,code,quote,img,link,unlink,emoticon,video,map|source';
	}
	else
	{
		toolbar_buttons = 'bold,italic,underline,strike,subscript,superscript|left,center,right,justify|font,size,color,removeformat|cut,copy,paste|bulletlist,orderedlist|table,code,quote,img,link,unlink,emoticon,video,map|source';
	}
	
	var emoticons = Joomla.getOptions('com_kunena.ckeditor_emoticons');
	var obj = jQuery.parseJSON( emoticons );
	var list_emoticons = [];

	jQuery.each(obj, function( index, value ) {
		list_emoticons.push(value);
	});

	var textarea = document.getElementById('message');
	var kunenaCmd = {
		align: ['left', 'center', 'right', 'justify'],
		fsStr: ['xx-small', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large'],
		fSize: [9, 12, 15, 17, 23, 31],
		video: {
			'Dailymotion': {
				'match': /(dailymotion\.com\/video\/|dai\.ly\/)([^\/]+)/,
				'url': '//www.dailymotion.com/embed/video/',
				'html': '<iframe frameborder="0" width="480" height="270" src="{url}" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'Facebook': {
				'match': /facebook\.com\/(?:photo.php\?v=|video\/video.php\?v=|video\/embed\?video_id=|v\/?)(\d+)/,
				'url': 'https://www.facebook.com/video/embed?video_id=',
				'html': '<iframe src="{url}" width="625" height="350" frameborder="0" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'Liveleak': {
				'match': /liveleak\.com\/(?:view\?[a-z]=)([^\/]+)/,
				'url': 'http://www.liveleak.com/ll_embed?i=',
				'html': '<iframe width="500" height="300" src="{url}" frameborder="0" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'MetaCafe': {
				'match': /metacafe\.com\/watch\/([^\/]+)/,
				'url': 'http://www.metacafe.com/embed/',
				'html': '<iframe src="{url}" width="440" height="248" frameborder=0 data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'Mixer': {
				'match': /mixer\.com\/([^\/]+)/,
				'url': '//mixer.com/embed/player/',
				'html': '<iframe allowfullscreen="true" src="{url}" width="620" height="349" frameborder="0" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'Vimeo': {
				'match': /vimeo.com\/(\d+)($|\/)/,
				'url': '//player.vimeo.com/video/',
				'html': '<iframe src="{url}" width="500" height="281" frameborder="0" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'Youtube': {
				'match': /(?:v=|v\/|embed\/|youtu\.be\/)(.{11})/,
				'url': '//www.youtube-nocookie.com/embed/',
				'html': '<iframe width="560" height="315" src="{url}" frameborder="0" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			},
			'Twitch': {
				'match': /twitch\.tv\/(?:[\w+_-]+)\/v\/(\d+)/,
				'url': '//player.twitch.tv/?video=v',
				'html': '<iframe src="{url}" frameborder="0" scrolling="no" height="378" width="620" data-kunena-vt="{type}" data-kunena-vsrc="{src}"></iframe>'
			}
		}
	};

	// Add bbcode maps
	sceditor.formats.bbcode.set('map', {
		format: function (element, content) {
			if (jQuery(element).data('sceditor-emoticon'))
				return content;

			var url = jQuery(element).attr('src'),
				width = jQuery(element).attr('width'),
				height = jQuery(element).attr('height'),
				align = jQuery(element).data('scealign');

			var attrs = width !== undefined && height !== undefined && width > 0 && height > 0
				? '=' + width + 'x' + height
				: ''
			;

			if (align === 'left' || align === 'right')
				attrs += ' align='+align

			return '[map' + attrs + ']' + url + '[/map]';
		},
		html: function (token, attrs, content) {
			var	width, height, match,
				align = attrs.align,
				attribs = '';

			// handle [img=340x240]url[/img]
			if (attrs.defaultattr) {
				match = attrs.defaultattr.split(/x/i);

				width  = match[0];
				height = (match.length === 2 ? match[1] : match[0]);

				if (width !== undefined && height !== undefined && width > 0 && height > 0) {
					attribs +=
						' width="' + sceditor.escapeEntities(width, true) + '"' +
						' height="' + sceditor.escapeEntities(height, true) + '"';
				}
			}

			if (align === 'left' || align === 'right')
				attribs += ' style="float: ' + align + '" data-scealign="' + align + '"';

			return '<img' + attribs +
				' src="' + sceditor.escapeUriScheme(content) + '" />';
		}
	})

	sceditor.command.set('map', {
		_dropDown: function (editor, caller) {
			var $content;

			$content = jQuery(
				'<div>' +
				'<div>' +
				'<label for="map">Type :</label> ' +
				'<select name="type" id="type-select">' +
				'<option value="hybrid">Hybrid</option>' +
				'<option value="roadmap">Roadmap</option>' +
				'<option value="terrain">Terrain</option>' +
				'<option value="satelite">Satelite</option>' +
				'</select>' +
				'</div>' +
				'<div>' +
				'<label for="width">Zoom level:</label> ' +
				'<select name="zoom" id="zoom-select">' +
				'<option value="2">2</option>' +
				'<option value="4">4</option>' +
				'<option value="8">8</option>' +
				'<option value="10">10</option>' +
				'<option value="12">12</option>' +
				'<option value="14">14</option>' +
				'<option value="16">16</option>' +
				'<option value="18">18</option>' +
				'</select>' +
				'</div>' +
				'<div>' +
				'<label for="height">City:</label> ' +
				'<input type="text" id="city" size="10" />' +
				'</div>' +
				'<div>' +
				'<input type="button" class="button" value="' + editor._('Insert') + '" />' +
				'</div>' +
				'</div>'
			);

			$content.find('.button').on('click', function (e) {
				var city = $content.find('#city').val(),
					width = $content.find('#width').val(),
					height = $content.find('#height').val()
				;

				var attrs = width !== undefined && height !== undefined && width > 0 && height > 0
					? '=' + width + 'x' + height
					: ''
				;

				if (city)
					editor.insert('[map' + attrs + ']' + city + '[/map]');

				editor.closeDropDown(true);
				e.preventDefault();
			});

			editor.createDropDown(caller, 'insertmap', $content.get(0));
		},
		exec: function (caller) {
			sceditor.command.get('map')._dropDown(this, caller);
		},
		txtExec: function (caller) {
			sceditor.command.get('map')._dropDown(this, caller);
		},
		tooltip: 'Insert a map',
	});

	// Image bbcode improved
	sceditor.formats.bbcode.set('img', {
		format: function (element, content) {
			if (jQuery(element).data('sceditor-emoticon'))
				return content;

			var url = jQuery(element).attr('src'),
				width = jQuery(element).attr('width'),
				height = jQuery(element).attr('height'),
				align = jQuery(element).data('scealign');

			var attrs = width !== undefined && height !== undefined && width > 0 && height > 0
				? '=' + width + 'x' + height
				: ''
			;

			if (align === 'left' || align === 'right')
				attrs += ' align='+align

			return '[img' + attrs + ']' + url + '[/img]';
		},
		html: function (token, attrs, content) {
			var	width, height, match,
				align = attrs.align,
				attribs = '';

			// handle [img=340x240]url[/img]
			if (attrs.defaultattr) {
				match = attrs.defaultattr.split(/x/i);

				width  = match[0];
				height = (match.length === 2 ? match[1] : match[0]);

				if (width !== undefined && height !== undefined && width > 0 && height > 0) {
					attribs +=
						' width="' + sceditor.escapeEntities(width, true) + '"' +
						' height="' + sceditor.escapeEntities(height, true) + '"';
				}
			}

			if (align === 'left' || align === 'right')
				attribs += ' style="float: ' + align + '" data-scealign="' + align + '"';

			return '<img' + attribs +
				' src="' + sceditor.escapeUriScheme(content) + '" />';
		}
	})

	sceditor.command.set('img', {
		_dropDown: function (editor, caller) {
			var $content;

			$content = jQuery(
				'<div>' +
				'<div>' +
				'<label for="image">' + editor._('URL') + ':</label> ' +
				'<input type="text" id="image" placeholder="https://" />' +
				'</div>' +
				'<div>' +
				'<label for="width">' + editor._('Width (optional)') + ':</label> ' +
				'<input type="text" id="width" size="2" />' +
				'</div>' +
				'<div>' +
				'<label for="height">' + editor._('Height (optional)') + ':</label> ' +
				'<input type="text" id="height" size="2" />' +
				'</div>' +
				'<div>' +
				'<input type="button" class="button" value="' + editor._('Insert') + '" />' +
				'</div>' +
				'</div>'
			);

			$content.find('.button').on('click', function (e) {
				var url = $content.find('#image').val(),
					width = $content.find('#width').val(),
					height = $content.find('#height').val()
				;

				var attrs = width !== undefined && height !== undefined && width > 0 && height > 0
					? '=' + width + 'x' + height
					: ''
				;

				if (url)
					editor.insert('[img' + attrs + ']' + url + '[/img]');

				editor.closeDropDown(true);
				e.preventDefault();
			});

			editor.createDropDown(caller, 'insertimage', $content.get(0));
		},
		exec: function (caller) {
			sceditor.command.get('img')._dropDown(this, caller);
		},
		txtExec: function (caller) {
			sceditor.command.get('img')._dropDown(this, caller);
		},
		tooltip: 'Insert an image',
	});

	// Add video command
	sceditor.formats.bbcode.set('video', {
		allowsEmpty: true,
		allowedChildren: ['#', '#newline'],
		tags: {
			iframe: {
				'data-kunena-vt': null
			}
		},
		format: function ($element, content) {
			return '[video=' + $($element).data('kunena-vt') + ']' + $($element).data('kunena-vsrc') + '[/video]';
		},
		html: function (token, attrs, content) {
			var params = kunenaCmd.video[Object.keys(kunenaCmd.video).find(key => key.toLowerCase() === attrs.defaultattr)];
			var matches, url;
			var n = (attrs.defaultattr == 'dailymotion') ? 2 : 1;
			if (typeof params !== "undefined") {
				matches = content.match(params['match']);
				url = matches ? params['url'] + matches[n] : false;
			}
			if (url) {
				return params['html'].replace('{url}', url).replace('{src}', content).replace('{type}', attrs.defaultattr);
			}
			return sceditor.escapeEntities(token.val + content + (token.closing ? token.closing.val : ''));
		}
	});

	sceditor.command.set('video', {
		_dropDown: function (editor, caller) {
			var $content, videourl, videotype, videoOpts;

			jQuery.each(kunenaCmd.video, function (provider, data) {
				videoOpts += '<option value="' + provider.toLowerCase() + '">' + editor._(provider) + '</option>';
			});
			$content = $(
				'<div>' +
				'<div>' +
				'<label for="videotype">' + editor._('Video Type:') + '</label> ' +
				'<select id="videotype">' + videoOpts + '</select>' +
				'</div>' +
				'<div>' +
				'<label for="link">' + editor._('Video URL:') + '</label> ' +
				'<input type="text" id="videourl" placeholder="https://" />' +
				'</div>' +
				'<div><input type="button" class="button" value="' + editor._('Insert') + '" /></div>' +
				'</div>'
			);

			$content.find('.button').on('click', function (e) {
				videourl = $content.find('#videourl').val();
				videotype = $content.find('#videotype').val();

				if (videourl !== '' && videourl !== 'http://')
					editor.insert('[video=' + videotype + ']' + videourl + '[/video]');

				editor.closeDropDown(true);
				e.preventDefault();
			});

			editor.createDropDown(caller, 'insertvideo', $content.get(0));
		},
		exec: function (caller) {
			sceditor.command.get('video')._dropDown(this, caller);
		},
		txtExec: function (caller) {
			sceditor.command.get('video')._dropDown(this, caller);
		},
		tooltip: 'Insert a video'
	});

	sceditor.create(textarea, {
		format: 'bbcode',
		toolbar: toolbar_buttons,
		style: Joomla.getOptions('com_kunena.sceditor_style_path'),
		emoticonsRoot: Joomla.getOptions('com_kunena.root_path')+'/media/kunena/emoticons/',
		/*emoticons: {
			// Emoticons to be included in the dropdown
			dropdown: list_emoticons,
			// Emoticons to be included in the more section
			more: {
				':alien:': 'emoticons/alien.png',
				':blink:': 'emoticons/blink.png'
			},
			// Emoticons that are not shown in the dropdown but will still
			// be converted. Can be used for things like aliases
			hidden: {
				':aliasforalien:': 'emoticons/alien.png',
				':aliasforblink:': 'emoticons/blink.png'
			}
		}*/
	});
});