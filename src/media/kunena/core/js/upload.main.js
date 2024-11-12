/**
 * Kunena Component
 * @package Kunena.Media
 *
 * @copyright     Copyright (C) 2008 - @currentyear@ Kunena Team. All rights reserved.
 * @license https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link https://www.kunena.org
 **/

jQuery(function ($) {
    'use strict';

    $.widget('blueimp.fileupload', $.blueimp.fileupload, {
        options: {
            // The maximum width of resized images:
            imageMaxWidth: Joomla.getOptions('com_kunena.imageWidth'),
            // The maximum height of resized images:
            imageMaxHeight: Joomla.getOptions('com_kunena.imageHeight')
        }
    });

    // Insert bbcode in message
    function insertInMessage(attachid, filename, button) {
        if (Joomla.getOptions('com_kunena.ckeditor_config') !== undefined) {
            CKEDITOR.instances.message.insertText(' [attachment=' + attachid + ']' + filename + '[/attachment]');
        } else {
            sceditor.instance(document.getElementById('message')).insert(' [attachment=' + attachid + ']' + filename + '[/attachment]');
        }

        if (button !== undefined) {
            button.removeClass('btn-primary');
            button.addClass('btn-success');
            button.html(Joomla.getOptions('com_kunena.icons.upload') + ' ' + Joomla.Text._('COM_KUNENA_EDITOR_IN_MESSAGE'));
        }
    }

    jQuery.fn.extend({
        insertAtCaret: function (myValue) {
            return this.each(function (i) {
                if (document.selection) {
                    //For browsers like Internet Explorer
                    this.focus();
                    //noinspection JSUnresolvedconstiable
                    let sel;
                    sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart === '0') {
                    //For browsers like Firefox and Webkit based
                    const startPos = this.selectionStart;
                    const endPos = this.selectionEnd;
                    const scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            })
        }
    });

    var fileCount = null;
    var filesedit = null;
    var fileeditinline = 0;

    $('#set-secure-all').on('click', function (e) {
        e.preventDefault();

        const child = $('#kattach-list').find('input');
        const filesidtosetprivate = [];
        const $this = $(this);

        child.each(function (i, el) {
            const elem = $(el);

            if (!elem.attr('id').match("[a-z]{8}")) {
                const fileid = elem.attr('id').match("[0-9]{1,8}");
                filesidtosetprivate.push(fileid);
            }
        });

        if (filesidtosetprivate.length !== 0) {
            $.ajax({
                url: Joomla.getOptions('com_kunena.kunena_upload_files_set_private') + '&files_id=' + JSON.stringify(filesidtosetprivate),
                type: 'POST'
            })
            .done(function (data) {
                // Update all individual private buttons
                $('#files button').each(function() {
                    const $btn = $(this);
                    if ($btn.html().includes(Joomla.Text._('COM_KUNENA_EDITOR_INSERT_PRIVATE_ATTACHMENT'))) {
                        $btn.removeClass('btn-primary')
                           .addClass('btn-success')
                           .prop('disabled', true)
                           .html(Joomla.getOptions('com_kunena.icons.secure') + ' ' + 
                                Joomla.Text._('COM_KUNENA_ATTACHMENT_IS_PRIVATE'));
                    }
                });

                // Update the set-secure-all button
                $this.removeClass('btn-primary')
                     .addClass('btn-success')
                     .prop('disabled', true)
                     .html(Joomla.getOptions('com_kunena.icons.secure') + ' ' + 
                          Joomla.Text._('COM_KUNENA_ALL_ATTACHMENTS_ARE_PRIVATE'));
            })
            .fail(function () {
                //TODO: handle the error of ajax request
            });
        }
    });

  $('#remove-all').on('click', function (e) {
    e.preventDefault();

    $('#progress').hide();

    // Reset insert-all button state
    $('#insert-all').removeClass('btn-success').addClass('btn-primary');
    $('#insert-all').html(Joomla.getOptions('com_kunena.icons.upload') + ' ' + Joomla.Text._('COM_KUNENA_UPLOADED_LABEL_INSERT_ALL_BUTTON'));

    // Hide action buttons
    $('#remove-all').hide();
    $('#insert-all').hide();
    $('#set-secure-all').hide(); // Also hide the secure-all button

    // Get editor content while preserving line breaks
    let editor_text = '';
    if (Joomla.getOptions('com_kunena.ckeditor_config') !== undefined) {
        editor_text = CKEDITOR.instances.message.getData();
    } else {
        editor_text = sceditor.instance(document.getElementById('message')).val();
    }

    // Find all attachment BBCodes
    const attachmentRegex = /\[attachment=([0-9]+)\]([^[\]]+)\[\/attachment\]/g;
    const attachments = [];
    let attachmentMatches;
    
    // Collect all attachments from the editor content
    while ((attachmentMatches = attachmentRegex.exec(editor_text)) !== null) {
        attachments.push({
            id: parseInt(attachmentMatches[1]),
            filename: attachmentMatches[2]
        });
    }

    // Also collect attachments from the #files container that aren't in the editor
    $('#files > div').each(function() {
        const $buttons = $(this).find('button');
        $buttons.each(function() {
            const $btn = $(this);
            const data = $btn.data();
            if (data.result?.data?.id || data.file_id) {
                const fileId = data.result?.data?.id || data.file_id;
                if (!attachments.some(a => a.id === fileId)) {
                    attachments.push({
                        id: fileId,
                        filename: data.result?.data?.filename || data.name
                    });
                }
            }
        });
    });

    // Remove all attachments if we found any
    if (attachments.length > 0) {
        // Clean editor content
        const cleanedEditorText = editor_text.replace(attachmentRegex, '');
        
        // Update editor content
        if (Joomla.getOptions('com_kunena.ckeditor_config') !== undefined) {
            CKEDITOR.instances.message.setData(cleanedEditorText);
        } else {
            sceditor.instance(document.getElementById('message')).val(cleanedEditorText);
        }

        // Remove each attachment via AJAX
        let i = 0;
        const removeAttachments = function() {
            if (i < attachments.length) {
                const attachment = attachments[i];
                $.ajax({
                    url: Joomla.getOptions('com_kunena.kunena_upload_files_rem'),
                    type: 'POST',
                    data: {
                        files_id_delete: JSON.stringify([attachment.id])
                    }
                })
                .always(function () {
                    // Remove the attachment's input fields
                    $('#kattachs-' + attachment.id).remove();
                    $('#kattach-' + attachment.id).remove();
                    
                    i++;
                    removeAttachments();
                });
            } else {
                // Clear the files container and reset counters
                $('#files').empty();
                fileCount = 0;
                fileeditinline = 0;
            }
        };
        removeAttachments();
    }

    // Remove any alert messages
    $('#alert_max_file').remove();
});


    $('#insert-all').on('click', function (e) {
    e.preventDefault();

    const child = $('#kattach-list').find('input');
    const files_id = [];
    let content_to_inject = '';

    child.each(function (i, el) {
        const elem = $(el);

        if (!elem.attr('id').match("[a-z]{8}")) {
            const attachid = elem.attr('id').match("[0-9]{1,8}");
            const filename = elem.attr('placeholder');

            content_to_inject += '[attachment=' + attachid + ']' + filename + '[/attachment]';

            // Find all buttons in #files div
            $('#files > div').each(function() {
                const $buttons = $(this).find('button');
                $buttons.each(function() {
                    const $btn = $(this);
                    // Check if this is the insert button (not private or remove button)
                    if ($btn.hasClass('btn-primary') && 
                        !$btn.hasClass('btn-danger') && 
                        $btn.html().includes(Joomla.Text._('COM_KUNENA_EDITOR_INSERT')) &&
                        ($btn.data('result')?.data?.id == attachid || 
                         $btn.data('id') == attachid)) {
                            $btn.removeClass('btn-primary btn-outline-primary')
                               .addClass('btn-success')
                               .html(Joomla.getOptions('com_kunena.icons.upload') + ' ' + 
                                    Joomla.Text._('COM_KUNENA_EDITOR_IN_MESSAGE'));
                            
                            // Hide the private button for this attachment
                            $(this).siblings('button').each(function() {
                                if ($(this).html().includes(Joomla.Text._('COM_KUNENA_EDITOR_INSERT_PRIVATE_ATTACHMENT'))) {
                                    $(this).hide();
                                }
                            });
                    }
                });
            });

            files_id.push(attachid);
        }
    });

    // Update the Insert All button state
    $('#insert-all').removeClass('btn-outline-primary btn-primary')
                   .addClass('btn-success')
                   .html(Joomla.getOptions('com_kunena.icons.upload') + ' ' + 
                        Joomla.Text._('COM_KUNENA_EDITOR_IN_MESSAGE'));

    // Hide the "Set all attachments private" button since they're now inline
    if ($('#set-secure-all').length > 0) {
        $('#set-secure-all').hide();
    }

    // Inserting items in message from edit if they aren't already present
    if ($.isEmptyObject(filesedit) === false) {
        $(filesedit).each(function (index, file) {
            if (file.inline !== true) {
                content_to_inject += '[attachment=' + file.id + ']' + file.name + '[/attachment]';
                files_id.push(file.id);
            }
        });
    }

    // Insert content into message
    if ($('#message').length > 0) {
        if (Joomla.getOptions('com_kunena.ckeditor_config') !== undefined) {
            CKEDITOR.instances.message.insertText(content_to_inject);
        } else {
            sceditor.instance(document.getElementById('message')).insert(content_to_inject);
        }
    }

    $.ajax({
        url: Joomla.getOptions('com_kunena.kunena_upload_files_set_inline') + '&files_id=' + JSON.stringify(files_id),
        type: 'POST'
    })
    .done(function (data) {
        // Success handler if needed
    })
    .fail(function () {
        // TODO: handle the error of ajax request
    });

    filesedit = null;
});

      const setPrivateButton = $('<button>')
        .addClass("btn btn-primary")
        .html(Joomla.getOptions('com_kunena.icons.secure') + ' ' + Joomla.Text._('COM_KUNENA_EDITOR_INSERT_PRIVATE_ATTACHMENT'))
        .on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $this = $(this),
            data = $this.data();

            let file_id = 0;
            if (data.result !== undefined) {
                file_id = data.result.data.id;
            } else {
                file_id = data.id;
            }

            const files_id = [];
            files_id.push(file_id);

            $.ajax({
                url: Joomla.getOptions('com_kunena.kunena_upload_files_set_private') + '&files_id=' + JSON.stringify(files_id),
                type: 'POST'
            })
            .done(function (data) {
                $this.removeClass('btn-primary')
                     .addClass('btn-success')
                     .prop('disabled', true)
                     .html(Joomla.getOptions('com_kunena.icons.secure') + ' ' + 
                          Joomla.Text._('COM_KUNENA_ATTACHMENT_IS_PRIVATE'));
            })
            .fail(function () {
                //TODO: handle the error of ajax request
            });
        });

    const insertButton = $('<button>')
        .addClass("btn btn-primary")
        .html(Joomla.getOptions('com_kunena.icons.upload') + ' ' + Joomla.Text._('COM_KUNENA_EDITOR_INSERT'))
        .on('click', function (e) {
            // Make sure the button click doesn't submit the form:
            e.preventDefault();
            e.stopPropagation();

            const $this = $(this),
                data = $this.data();

            let file_id = 0;
            let filename = null;
            if (data.result !== undefined) {
                file_id = data.result.data.id;
                filename = data.result.data.filename;
            } else {
                file_id = data.id;
                filename = data.name;
            }

            insertInMessage(file_id, filename, $this);

            const files_id = [];
            files_id.push(file_id);

            $.ajax({
                url: Joomla.getOptions('com_kunena.kunena_upload_files_set_inline') + '&files_id=' + JSON.stringify(files_id),
                type: 'POST'
            })
                .done(function (data) {

                })
                .fail(function () {
                    //TODO: handle the error of ajax request
                });
        });

   const removeButton = $('<button/>')
        .addClass('btn btn-danger')
        .attr('type', 'button')
        .html(Joomla.getOptions('com_kunena.icons.trash') + ' ' + Joomla.Text._('COM_KUNENA_GEN_REMOVE_FILE'))
        .on('click', function (e) {
            // Make sure the button click doesn't submit the form:
            e.preventDefault();
            e.stopPropagation();

            const $this = $(this),
                data = $this.data();

            $('#klabel_info_drop_browse').show();

            let file_id = 0;
            if (data.uploaded === true) {
                if (data.result !== false) {
                    file_id = data.result.data.id;
                } else {
                    file_id = data.file_id;
                }
            }

            if ($('#kattachs-' + file_id).length === 0) {
                $('#kattach-list').append('<input id="kattachs-' + file_id + '" type="hidden" name="attachments[' + file_id + ']" value="1" />');
            }

            if ($('#kattach-' + file_id).length > 0) {
                $('#kattach-' + file_id).remove();
            }

            fileCount = fileCount - 1;

            if (fileCount == 0) {
                $('#insert-all').hide();
                $('#remove-all').hide();
            }

            $('#alert_max_file').remove();
            
            // Get editor content while preserving line breaks
            let editor_text = '';
            if (Joomla.getOptions('com_kunena.ckeditor_config') !== undefined) {
                // For CKEditor, getData() already preserves formatting
                editor_text = CKEDITOR.instances.message.getData();
            } else {
                // For SCEditor, get raw content with preserved line breaks
                editor_text = sceditor.instance(document.getElementById('message')).val();
            }

            if (editor_text.length > 0) {
                const file_query_id = [];
                file_query_id.push(file_id);

                // Ajax Request to delete the file from filesystem
                $.ajax({
                    url: Joomla.getOptions('com_kunena.kunena_upload_files_rem') + '&files_id_delete=' + JSON.stringify(file_query_id) + '&editor_text=' + encodeURIComponent(editor_text),
                    type: 'POST'
                })
                .done(function (data) {
                    $this.parent().remove();

                    if (data.text_prepared !== false) {
                        if (Joomla.getOptions('com_kunena.ckeditor_config') !== undefined) {
                            // Set content while preserving line breaks for CKEditor
                            CKEDITOR.instances.message.setData(data.text_prepared);
                        } else {
                            // Set content while preserving line breaks for SCEditor
                            sceditor.instance(document.getElementById('message')).val(data.text_prepared);
                        }
                    }
                })
                .fail(function () {
                    //TODO: handle the error of ajax request
                });
            }
        });

    $('#fileupload').fileupload({
        url: $('#kunena_upload_files_url').val(),
        dataType: 'json',
        autoUpload: true,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: / Android( ?!.*Chrome) |Opera /
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).bind('fileuploadsubmit', function (e, data) {
        var params = {};
        $.each(data.files, function (index, file) {
            params = {
                'catid': $('#kunena_upload').val(),
                'filename': file.name,
                'size': file.size,
                'mime': file.type
            };
        });

        data.formData = params;
    })
        .bind('fileuploaddrop', function (e, data) {
            $('#form_submit_button').prop('disabled', true);

            $('#remove-all').show();
            $('#insert-all').show();
            
            if (Joomla.getOptions('com_kunena.privateMessage') == 1) {
                $('#set-secure-all').show();
            }

            $('#kattach_form').show();

            const filecoutntmp = Object.keys(data['files']).length + fileCount;

            if (filecoutntmp > Joomla.getOptions('com_kunena.kunena_upload_files_maxfiles')) {
                $('<div class="alert alert-danger alert-dismissible fade show" id="alert_max_file" role="alert">  ' + Joomla.Text._('COM_KUNENA_UPLOADED_LABEL_ERROR_REACHED_MAX_NUMBER_FILES') + '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>').insertBefore($('#files'));

                $('#form_submit_button').prop('disabled', false);

                return false;
            } else {
                fileCount = Object.keys(data['files']).length + fileCount;
            }
        })
        .bind('fileuploadchange', function (e, data) {
            $('#form_submit_button').prop('disabled', true);

            $('#remove-all').show();
            $('#insert-all').show();
            
            if (Joomla.getOptions('com_kunena.privateMessage') == 1) {
                $('#set-secure-all').show();
            }

            const filecoutntmp = Object.keys(data['files']).length + fileCount;

            if (filecoutntmp > Joomla.getOptions('com_kunena.kunena_upload_files_maxfiles')) {
                $('<div class="alert alert-danger alert-dismissible fade show" id="alert_max_file" role="alert">  ' + Joomla.Text._('COM_KUNENA_UPLOADED_LABEL_ERROR_REACHED_MAX_NUMBER_FILES') + '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>').insertBefore($('#files'));

                $('#form_submit_button').prop('disabled', false);

                return false;
            } else {
                fileCount = Object.keys(data['files']).length + fileCount;
            }
        })
        .on('fileuploadadd', function (e, data) {
            $('#progress-bar').css(
                'width',
                '0%'
            );

			$('#progress').show();

            data.context = $('<div/>').appendTo('#files');

            $.each(data.files, function (index, file) {
                const node = $('<p/>')
                    .append($('<span/>').text(file.name));
                if (!index) {
                    node
                        .append('<br>');
                }

                node.appendTo(data.context);
            });
        }).on('fileuploadprocessalways', function (e, data) {
        const index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                .prepend('<br>')
                .prepend(file.preview);
        }

        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }

        if (index + 1 === data.files.length) {
            data.context.find('button.btn-primary')
                .text(Joomla.Text._('COM_KUNENA_UPLOADED_LABEL_UPLOAD_BUTTON'))
                .prop('disabled', !!data.files.error);
        }
    }).on('fileuploaddone', function (e, data) {
        // $.each(data.result.data, function (index, file)
        const progress = parseInt(data.loaded / data.total * 100, 10);

        $('.progress-bar').css(
            'width',
            progress + '%'
        );

        $('.progress-bar').prop('aria-valuenow', progress);

        const link = $('<a>')
            .attr('target', '_blank')
            .prop('href', data.result.location);

        data.context.find('span')
            .wrap(link);

        if (data.result.success === true) {
            $('#form_submit_button').prop('disabled', false);

            // The attachment has been right uploaded, so now we need to put into input hidden to added to message
            $('#kattach-list').append('<input id="kattachs-' + data.result.data.id + '" type="hidden" name="attachments[' + data.result.data.id + ']" value="1" />');
            $('#kattach-list').append('<input id="kattach-' + data.result.data.id + '" placeholder="' + data.result.data.filename + '" type="hidden" name="attachment[' + data.result.data.id + ']" value="1" />');

            data.uploaded = true;

            data.context.append(insertButton.clone(true).data(data));
            if (data.context.find('button').hasClass('btn-danger')) {
                data.context.find('button.btn-danger').remove();
            }

			if (Joomla.getOptions('com_kunena.privateMessage') == 1) {
                data.context.append(setPrivateButton.clone(true).data(data));
            }

            data.context.append(removeButton.clone(true).data(data));
        } else if (data.result.message) {
            $('#form_submit_button').prop('disabled', false);

            data.uploaded = false;
            data.context.append(removeButton.clone(true).data(data));

            var error = null;

            if (data.result.message.length > 0) {
                error = $('<div class="alert alert-danger" role="alert">').text(data.result.message);
                data.context.find('span')
                    .append('<br>')
                    .append(error);
            }
        }
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index, file) {
            const error = $('<span class="text-danger"/>').text(file.error);
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    // Load attachments when the message is edited
     if ($('#kmessageid').val() > 0) {
        $.ajax({
            type: 'POST',
            url: Joomla.getOptions('com_kunena.kunena_upload_files_preload'),
            async: true,
            dataType: 'json',
            data: {mes_id: $('#kmessageid').val()}
        })
        .done(function (data) {
            if ($.isEmptyObject(data.files) === false) {
                fileCount = Object.keys(data.files).length;

                filesedit = data.files;

                $(data.files).each(function (index, file) {
                    let image = '';
                    if (file.image === true) {
                        image = '<img alt="" src="' + file.path + '" width="100" height="100" /><br />';
                    } else {
                        image = Joomla.getOptions('com_kunena.icons.attach') + ' <br />';
                    }

                    if (file.inline === true) {
                        fileeditinline = fileeditinline + 1;
                    }

                    const object = $('<div><p>' + image + '<span>' + file.name + '</span><br /></p></div>');
                    data.uploaded = true;
                    data.result = false;
                    data.file_id = file.id;

                    // Add insert button
                    object.append(insertButton.clone(true).data(file));

                    // Add private button if private messages are enabled
                    if (Joomla.getOptions('com_kunena.privateMessage') == 1) {
                        const privateBtn = setPrivateButton.clone(true).data(file);
                        
                        // Check the protected status instead of private
                        if (file.protected) {
                            privateBtn.removeClass('btn-primary')
                                    .addClass('btn-success')
                                    .prop('disabled', true)
                                    .html(Joomla.getOptions('com_kunena.icons.secure') + ' ' + 
                                         Joomla.Text._('COM_KUNENA_ATTACHMENT_IS_PRIVATE'));
                        }
                        
                        object.append(privateBtn);
                    }

                    // Add remove button
                    object.append(removeButton.clone(true).data(data));

                    object.appendTo("#files");
                });

                if (fileeditinline == 0) {
                    $('#insert-all').show();
                }

                $('#remove-all').show();
                
                // Show set-secure-all button if private messages are enabled
                if (Joomla.getOptions('com_kunena.privateMessage') == 1) {
                    $('#set-secure-all').show();
                }
            }
        })
        .fail(function () {
            //TODO: handle the error of ajax request
        });
    }
});
