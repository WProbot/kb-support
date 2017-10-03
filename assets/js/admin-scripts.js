var kbs_vars;
jQuery(document).ready(function ($) {

	// Setup datepicker
	var kbs_datepicker = $('.kbs_datepicker');
	if ( kbs_datepicker.length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		kbs_datepicker.datepicker({
			dateFormat: dateFormat
		});
	}

	// Setup Chosen menus
	$('.kbs_select_chosen').chosen({
		inherit_select_classes: true
	});

	/**
	 * Settings screen JS
	 */
	var KBS_Settings = {

		init : function() {
			this.general();
		},

		general : function() {

			var kbs_color_picker = $('.kbs-color-picker');

			if( kbs_color_picker.length ) {
				kbs_color_picker.wpColorPicker();
			}

			// Settings Upload field JS
			if ( typeof wp === 'undefined' || '1' !== kbs_vars.new_media_ui ) {
				// Old Thickbox uploader
				var kbs_settings_upload_button = $( '.kbs_settings_upload_button' );
				if ( kbs_settings_upload_button.length > 0 ) {
					window.formfield = '';

					$( document.body ).on('click', kbs_settings_upload_button, function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(kbs_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
						}, 2000);
						tb_show( kbs_vars.add_new_ticket, 'media-upload.php?TB_iframe=true' );
					});

					window.kbs_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr('href');
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.kbs_send_to_editor(html);
						}
						window.send_to_editor = window.kbs_send_to_editor;
						window.formfield = '';
						window.imagefield = false;
					};
				}
			} else {
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$( document.body ).on('click', '.kbs_settings_upload_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).parent().prev();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' )
						},
						multiple: false
					});

					file_frame.on( 'menu:render:default', function( view ) {
						// Store our views in an object.
						var views = {};

						// Unset default menu items
						view.unset( 'library-separator' );
						view.unset( 'gallery' );
						view.unset( 'featured-image' );
						view.unset( 'embed' );

						// Initialize the views in our view object.
						view.set( views );
					} );

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment ) {
							attachment = attachment.toJSON();
							window.formfield.val(attachment.url);
						});
					});

					// Finally, open the modal
					file_frame.open();
				});

			}

		}

	};
	KBS_Settings.init();
	
	/**
	 * Tickets screen JS
	 */
	var KBS_Tickets = {
		init : function() {
            this.save();
			this.reply();
			this.notes();
		},

        save : function()   {
            $( document.body ).on( 'click', '#save-post', function() {
                var ticketResponse = '';
                var tinymceActive  = (typeof tinyMCE !== 'undefined') && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

				if (tinymceActive) {
					tinyMCE.triggerSave();
				}

				ticketResponse = $('#kbs_ticket_reply' ).val();
                if ( ticketResponse.length > 0 )	{
                    return confirm( kbs_vars.reply_has_data );
                }
            });
        },

		reply : function() {
			// Reply to ticket Requests
			$( document.body ).on( 'click', '#kbs-reply-close, #kbs-reply-update', function(event) {
				
				event.preventDefault();

				var ticket_id      = kbs_vars.post_id;
				var ticketResponse = '';
				var tinymceActive  = (typeof tinyMCE !== 'undefined') && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

				if (tinymceActive) {
					tinyMCE.triggerSave();
				}

				ticketResponse = $('#kbs_ticket_reply' ).val();

				if ( ticketResponse.length === 0 )	{
					window.alert( kbs_vars.no_ticket_reply_content );
					return false;
				}

				if ( 'kbs-reply-close' === event.target.id )	{
					var confirmClose = confirm( kbs_vars.ticket_confirm_close );

					if (confirmClose === false) {
						return;
					}
				}

				var postData         = {
					ticket_id    : ticket_id,
					response     : ticketResponse,
					close_ticket : ( 'kbs-reply-close' === event.target.id ? 1 : 0 ),
					action       : 'kbs_insert_ticket_reply'
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$('.kbs-reply').hide();
						$('#kbs-new-reply-loader').html('<img src="' + kbs_vars.ajax_loader + '" />');
					},
					success: function (response) {
						if (response.reply_id)	{
							kbs_load_ticket_replies(ticket_id, response.reply_id);
							window.location.href = kbs_vars.admin_url + '?kbs-action=ticket_reply_added&ticket_id=' + ticket_id;
							return true;
						} else	{
							window.alert(kbs_vars.reply_not_added);
						}
						$('#kbs-new-reply-loader').html('');
						$('.kbs-reply').show();
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
		},
		notes : function() {
			// Add a new ticket note
			$( document.body ).on( 'click', '#kbs-add-note', function() {
				var note_content = $('#kbs_new_note').val();
				var ticket_id    = kbs_vars.post_id;

				if ( note_content.length < 1 )	{
					window.alert(kbs_vars.no_note_content);
					return;
				}

				var postData         = {
					ticket_id    : ticket_id,
					note_content : note_content,
					action       : 'kbs_insert_ticket_note'
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$('#kbs-add-note').hide();
						$('#kbs-new-note-loader').html('<img src="' + kbs_vars.ajax_loader + '" />');
					},
					success: function (response) {
						if (response.note_id)	{
							kbs_load_ticket_notes(ticket_id, response.note_id);
							$('#kbs_new_note').val('');
						} else	{
							window.alert(kbs_vars.note_not_added);
						}
						$('#kbs-new-note-loader').html('');
						$('#kbs-add-note').show();
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});

			// Auto load ticket replies and notes
			if( kbs_vars.editing_ticket === '1' ) {
				setTimeout( function() {
					kbs_load_ticket_replies( kbs_vars.post_id, 0 );
					kbs_load_ticket_notes( kbs_vars.post_id, 0 );
				}, 200);
			}
		}
	};

    // Toggle display of historic ticket replies
	$( document.body ).on( 'click', '.toggle-view-reply-option-section', function(e) {
		e.preventDefault();
		var show = $(this).html() === kbs_vars.view_reply ? true : false;

		if ( show ) {
			$(this).html( kbs_vars.hide_reply );
		} else {
			$(this).html( kbs_vars.view_reply );
		}

		var header = $(this).parents('.kbs-replies-row-header');
		header.siblings('.kbs-replies-content-wrap').slideToggle();

	});

	// Toggle display of ticket notes
	$( document.body ).on( 'click', '.toggle-view-note-option-section', function(e) {
		e.preventDefault();
		var show = $(this).html() === kbs_vars.view_note ? true : false;

		if ( show ) {
			$(this).html( kbs_vars.hide_note );
		} else {
			$(this).html( kbs_vars.view_note );
		}

		var header = $(this).parents('.kbs-notes-row-header');
		header.siblings('.kbs-notes-content-wrap').slideToggle();

	});

	KBS_Tickets.init();
	
	/**
	 * Forms screen JS
	 */
	var KBS_Forms = {

		init : function() {
			this.forms();
			this.move();
		},

		forms : function() {

			var toggleFieldOptions = function(kbs_selected_field)	{
				if ( 'text' === kbs_selected_field || 'date_field' === kbs_selected_field || 'email' === kbs_selected_field || 'number' === kbs_selected_field || 'select' === kbs_selected_field || 'textarea' === kbs_selected_field || 'url' === kbs_selected_field )	{

					$('#kbs_meta_field_placeholder_wrap').show();
					$('#kbs_meta_field_hide_label_wrap').show();
				} else	{
					$('#kbs_meta_field_placeholder_wrap').hide();
					$('#kbs_meta_field_hide_label_wrap').hide();
				}

				if ( 'select' === kbs_selected_field || 'checkbox_list' === kbs_selected_field || 'radio' === kbs_selected_field )	{
					$('#kbs_meta_field_select_options_wrap').show();
				} else	{
					$('#kbs_meta_field_select_options_wrap').hide();
				}

				if ( 'select' === kbs_selected_field )	{
					$('#kbs_meta_field_select_multiple_wrap').show();
				} else	{
					$('#kbs_meta_field_select_multiple_wrap').hide();
				}

				if ( 'select' === kbs_selected_field || 'ticket_category_dropdown' === kbs_selected_field )	{
					$('#kbs_meta_field_select_searchable_wrap').show();
				} else	{
					$('#kbs_meta_field_select_searchable_wrap').hide();
				}

				if ( 'checkbox' === kbs_selected_field )	{
					$('#kbs_meta_field_option_selected_wrap').show();
				} else	{
					$('#kbs_meta_field_option_selected_wrap').hide();
				}

				if ( 'checkbox' === kbs_selected_field || 'file_upload' === kbs_selected_field )	{
					$('#kbs_meta_field_required_wrap').hide();
				} else	{
					$('#kbs_meta_field_required_wrap').show();
				}
			
				if ( 'recaptcha' === kbs_selected_field )	{
					$('#kbs_meta_field_required_wrap').hide();
					$('#kbs_meta_field_label_class_wrap').hide();
					$('#kbs_meta_field_input_class_wrap').hide();
				} else	{
					$('#kbs_meta_field_required_wrap').show();
					$('#kbs_meta_field_label_class_wrap').show();
					$('#kbs_meta_field_input_class_wrap').show();
				}

				if ( 'text' === kbs_selected_field || 'email' === kbs_selected_field || 'url' === kbs_selected_field || 'textarea' === kbs_selected_field || 'rich_editor' === kbs_selected_field )	{
					$('#kbs_meta_field_mapping_wrap').show();
				} else	{
					$('#kbs_meta_field_mapping_wrap').hide();
				}

				if( 'post_title' === $('#kbs_field_mapping').val() )	{
					$('#kbs_meta_field_kb_search_wrap').show();
				} else	{
					$('#kbs_meta_field_kb_search_wrap').hide();
				}

			};

			// Preload field options when editing
			if ( kbs_vars.editing_field_type )	{
				toggleFieldOptions(kbs_vars.editing_field_type);
			}

			var kbs_field_type = $('.kbs_field_type');

			$( document.body ).on('change', kbs_field_type, function()	{

				toggleFieldOptions(kbs_field_type.val());

			});

			$( document.body ).on('change', $('#kbs_field_mapping'), function()	{
				if( 'post_title' === $('#kbs_field_mapping').val() )	{
					$('#kbs_meta_field_kb_search_wrap').show();
				} else	{
					$('#kbs_meta_field_kb_search_wrap').hide();
				}
			});

			// Send Add New Field Requests
			$( document.body ).on( 'click', '#kbs-add-form-field', function(event) {
				
				event.preventDefault();
				
				if ( $('#kbs_field_label').val().length < 1 )	{
					window.alert( kbs_vars.field_label_missing );
					return false;
				}
				if ( $('#kbs_field_type').val() === '-1' )	{
					window.alert( kbs_vars.field_type_missing );
					return false;
				}

				var return_url       = $('#form_return_url').val();			
				var postData         = {
					form_id          : kbs_vars.post_id,
					form_data        : $('#post').serialize(),
					label            : $('#kbs_field_label').val(),
					type             : $('#kbs_field_type').val(),
					mapping          : $('#kbs_field_mapping').val(),
					kb_search        : ( $('#kbs_field_kb_search').is(':checked') )       ? $('#kbs_field_kb_search').val()     : 0,
					required         : ( $('#kbs_field_required').is(':checked') )        ? $('#kbs_field_required').val()        : 0,
					label_class      : $('#kbs_field_label_class').val(),
					input_class      : $('#kbs_field_input_class').val(),
					select_options   : $('textarea#kbs_field_select_options').val(),
					select_multiple  : ( $('#kbs_field_select_multiple').is(':checked') ) ? $('#kbs_field_select_multiple').val() : 0,
					selected         : ( $('#kbs_field_option_selected').is(':checked') ) ? $('#kbs_field_option_selected').val() : 0,
					chosen           : ( $('#kbs_field_select_chosen').is(':checked') )   ? $('#kbs_field_select_chosen').val()   : 0,
					description      : $('#kbs_field_description').val(),
					description_pos  : $('input[name=kbs_field_description_pos]').filter(':checked').val(),
					placeholder      : $('#kbs_field_placeholder').val(),
					hide_label       : ( $('#kbs_field_hide_label').is(':checked') )      ? $('#kbs_field_hide_label').val()      : 0,
					action           : 'kbs_add_form_field'
				};
				
				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$('#kbs-field-add').addClass('kbs-hidden');
						$('#kbs-loading').removeClass('kbs-hidden');
					},
					success: function (response) {
						window.location.href = return_url + '&kbs-message=' + response.message;
						return true;
					}
				}).fail(function (data) {
					$('#kbs-field-add').removeClass('kbs-hidden');
					$('#kbs-loading').addClass('kbs-hidden');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
			
			// Send Edit Field Requests
			$( document.body ).on( 'click', '#kbs-save-form-field', function(event) {
				
				event.preventDefault();
				
				if ( $('#kbs_field_label').val().length < 1 )	{
					window.alert( kbs_vars.field_label_missing );
					return false;
				}
				if ( $('#kbs_field_type').val() === '-1' )	{
					window.alert( kbs_vars.field_type_missing );
					return false;
				}

				var return_url       = $('#form_return_url').val();			
				var postData         = {
					form_id          : kbs_vars.post_id,
					form_data        : $('#post').serialize(),
					field_id         : $('#kbs_edit_field').val(), 
					label            : $('#kbs_field_label').val(),
					type             : $('#kbs_field_type').val(),
					mapping          : $('#kbs_field_mapping').val(),
					kb_search        : ( $('#kbs_field_kb_search').is(':checked') ) ? $('#kbs_field_kb_search').val() : 0,
					required         : ( $('#kbs_field_required').is(':checked') ) ? $('#kbs_field_required').val() : 0,
					label_class      : $('#kbs_field_label_class').val(),
					input_class      : $('#kbs_field_input_class').val(),
					select_options   : $('textarea#kbs_field_select_options').val(),
					select_multiple  : ( $('#kbs_field_select_multiple').is(':checked') ) ? $('#kbs_field_select_multiple').val() : 0,
					selected         : ( $('#kbs_field_option_selected').is(':checked') ) ? $('#kbs_field_option_selected').val() : 0,
					chosen           : ( $('#kbs_field_select_chosen').is(':checked') )   ? $('#kbs_field_select_chosen').val()   : 0,
					placeholder      : $('#kbs_field_placeholder').val(),
					description      : $('#kbs_field_description').val(),
					description_pos  : $('input[name=kbs_field_description_pos]').filter(':checked').val(),
					hide_label       : ( $('#kbs_field_hide_label').is(':checked') )      ? $('#kbs_field_hide_label').val()      : 0,
					action           : 'kbs_save_form_field'
				};
				
				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$('#kbs-field-save').addClass('kbs-hidden');
						$('#kbs-loading').removeClass('kbs-hidden');
					},
					success: function (response) {
						window.location.href = return_url + '&kbs-message=' + response.message;
						return true;
					}
				}).fail(function (data) {
					$('#kbs-field-save').removeClass('kbs-hidden');
					$('#kbs-loading').addClass('kbs-hidden');
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});
				
			});
			
		},
		
		move : function() {

			$('.kbs_sortable_table tbody').sortable({
				handle: '.kbs_draghandle', items: '.kbs_sortable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var order = $(this).sortable('serialize') + '&action=kbs_order_form_fields';
						
					$.post(ajaxurl, order, function()	{
						// Success
					});
				}
			});

		}

	};
	KBS_Forms.init();

	/**
	 * Customer management screen JS
	 */
	var KBS_Customer = {
		vars: {
			customer_wrap_editable:  $( '.kbs-customer-wrapper .editable' ),
			customer_wrap_edit_item: $( '.kbs-customer-wrapper .edit-item' ),
			user_id: $('input[name="customerinfo[user_id]"]'),
			note: $( '#customer-note' )
		},
		init : function() {
			this.add_customer();
			this.edit_customer();
			this.cancel_edit();
			this.add_email();
			this.user_search();
			this.remove_user();
			this.add_note();
			this.delete_checked();
		},
		add_customer: function() {
			$( document.body ).on( 'click', '#kbs-add-customer-save', function(e) {
				e.preventDefault();
				var button  = $(this);
				var wrapper = button.parent();

				var customer_name     = $('#customer-name').val();
				var customer_company  = $('#customer_company').val();
				var customer_email    = $('#customer-email').val();
				var nonce             = $('add_customer_nonce').val();

				var postData = {
					action             : 'kbs_add_customer',
					customer_name      : customer_name,
					customer_company   : customer_company,
					customer_email     : customer_email,
					_wpnonce           : nonce
				};

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: postData,
					url: ajaxurl,
					beforeSend: function()	{
						$('.notice-wrap').html('');
						wrapper.find('.spinner').css('visibility', 'visible');
						button.attr('disabled', true);
					},
					success: function (response)	{
						if ( ! response.error )	{
							window.location.href = response.redirect;
							return true;
						} else	{
							button.attr('disabled', false);
							$('.notice-wrap').html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
							wrapper.find('.spinner').css('visibility', 'hidden');
						}
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			});
		},
		edit_customer: function() {
			$( document.body ).on( 'click', '#edit-customer', function( e ) {
				e.preventDefault();

				KBS_Customer.vars.customer_wrap_editable.hide();
				KBS_Customer.vars.customer_wrap_edit_item.fadeIn().css( 'display', 'block' );
			});
		},
		cancel_edit: function() {
			$( document.body ).on( 'click', '#kbs-edit-customer-cancel', function( e ) {
				e.preventDefault();
				KBS_Customer.vars.customer_wrap_edit_item.hide();
				KBS_Customer.vars.customer_wrap_editable.show();

				$( '.kbs_user_search_results' ).html('');
			});
		},
		add_email: function() {
			$( document.body ).on( 'click', '#add-customer-email', function(e) {
				e.preventDefault();
				var button  = $(this);
				var wrapper = button.parent();

				wrapper.parent().find('.notice-wrap').remove();
				wrapper.find('.spinner').css('visibility', 'visible');
				button.attr('disabled', true);

				var customer_id = wrapper.find('input[name="customer-id"]').val();
				var email       = wrapper.find('input[name="additional-email"]').val();
				var primary     = wrapper.find('input[name="make-additional-primary"]').is(':checked');
				var nonce       = wrapper.find('input[name="add_email_nonce"]').val();

				var postData = {
					kbs_action:  'customer-add-email',
					customer_id: customer_id,
					email:       email,
					primary:     primary,
					_wpnonce:    nonce
				};

				$.post(ajaxurl, postData, function( response ) {

					if ( true === response.success ) {
						window.location.href=response.redirect;
					} else {
						button.attr('disabled', false);
						wrapper.after('<div class="notice-wrap"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
						wrapper.find('.spinner').css('visibility', 'hidden');
					}

				}, 'json');

			});
		},
		user_search: function() {
			// Upon selecting a user from the dropdown, we need to update the User ID
			$( document.body ).on('click.kbsSelectUser', '.kbs_user_search_results a', function( e ) {
				e.preventDefault();
				var user_id = $(this).data('userid');
				KBS_Customer.vars.user_id.val(user_id);
			});
		},
		remove_user: function() {
			$( document.body ).on( 'click', '#disconnect-customer', function( e ) {
				e.preventDefault();
				var customer_id = $('input[name="customerinfo[id]"]').val();

				var postData = {
					kbs_action:   'disconnect-userid',
					customer_id: customer_id,
					_wpnonce:     $( '#edit-customer-info #_wpnonce' ).val()
				};

				$.post(ajaxurl, postData, function() {
					window.location.href=window.location.href;
				}, 'json');

			});
		},
		add_note : function() {
			$( document.body ).on( 'click', '#add-customer-note', function( e ) {
				e.preventDefault();
				var postData = {
					kbs_action : 'add-customer-note',
					customer_id : $( '#customer-id' ).val(),
					customer_note : KBS_Customer.vars.note.val(),
					add_customer_note_nonce: $( '#add_customer_note_nonce' ).val()
				};

				if( postData.customer_note ) {

					$.ajax({
						type: 'POST',
						data: postData,
						url: ajaxurl,
						success: function ( response ) {
							$( '#kbs-customer-notes' ).prepend( response );
							$( '.kbs-no-customer-notes' ).hide();
							KBS_Customer.vars.note.val( '' );
						}
					}).fail( function ( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				} else {
					var border_color = KBS_Customer.vars.note.css( 'border-color' );
					KBS_Customer.vars.note.css( 'border-color', 'red' );
					setTimeout( function() {
						KBS_Customer.vars.note.css( 'border-color', border_color );
					}, 500 );
				}
			});
		},
		delete_checked: function() {
			$( '#kbs-customer-delete-confirm' ).change( function() {
				var submit_button = $('#kbs-delete-customer');

				if ( $(this).prop('checked') ) {
					submit_button.attr('disabled', false);
				} else {
					submit_button.attr('disabled', true);
				}
			});
		}
	};
	KBS_Customer.init();

    /**
	 * Export screen JS
	 */
	var KBS_Export = {

		init : function() {
			this.submit();
			this.dismiss_message();
		},

		submit : function() {

			var self = this;

			$( document.body ).on( 'submit', '.kbs-export-form', function(e) {
				e.preventDefault();

				var submitButton = $(this).find( 'input[type="submit"]' );

				if ( ! submitButton.hasClass( 'button-disabled' ) ) {

					var data = $(this).serialize();

					submitButton.addClass( 'button-disabled' );
					$(this).find('.notice-wrap').remove();
					$(this).append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="kbs-progress"><div></div></div></div>' );

					// start the process
					self.process_step( 1, data, self );

				}

			});
		},

		process_step : function( step, data, self ) {

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'kbs_do_ajax_export',
					step: step,
				},
				dataType: 'json',
				success: function( response ) {
					if( 'done' === response.step || response.error || response.success ) {

						// We need to get the actual in progress form, not all forms on the page
						var export_form    = $('.kbs-export-form').find('.kbs-progress').parent().parent();
						var notice_wrap    = export_form.find('.notice-wrap');

						export_form.find('.button-disabled').removeClass('button-disabled');

						if ( response.error ) {

							var error_message = response.message;
							notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

						} else if ( response.success ) {

							var success_message = response.message;
							notice_wrap.html('<div id="kbs-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

						} else {

							notice_wrap.remove();
							window.location = response.url;

						}

					} else {
						$('.kbs-progress div').animate({
							width: response.percentage + '%',
						}, 50, function() {
							// Animation complete.
						});
						self.process_step( parseInt( response.step ), data, self );
					}

				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});

		},

		dismiss_message : function() {
			$('body').on( 'click', '#kbs-batch-success .notice-dismiss', function() {
				$('#kbs-batch-success').parent().slideUp('fast');
			});
		}

	};
	KBS_Export.init();

	// AJAX user search
	$('.kbs-ajax-user-search').keyup(function() {
		var user_search = $(this).val();
		var exclude     = '';

		if ( $(this).data('exclude') ) {
			exclude = $(this).data('exclude');
		}

		$('.kbs-ajax').show();
		var data = {
			action: 'kbs_search_users',
			user_name: user_search,
			exclude: exclude
		};

		document.body.style.cursor = 'wait';

		$.ajax({
			type: 'POST',
			data: data,
			dataType: 'json',
			url: ajaxurl,
			success: function (search_response) {

				$('.kbs-ajax').hide();
				$('.kbs_user_search_results').removeClass('hidden');
				$('.kbs_user_search_results span').html('');
				$(search_response.results).appendTo('.kbs_user_search_results span');
				document.body.style.cursor = 'default';
			}
		});
	});

	$( document.body ).on('click.kbsSelectUser', '.kbs_user_search_results span a', function(e) {
		e.preventDefault();
		var login = $(this).data('login');
		$('.kbs-ajax-user-search').val(login);
		$('.kbs_user_search_results').addClass('hidden');
		$('.kbs_user_search_results span').html('');
	});

	$( document.body ).on('click.kbsCancelUserSearch', '.kbs_user_search_results a.kbs-ajax-user-cancel', function(e) {
		e.preventDefault();
		$('.kbs-ajax-user-search').val('');
		$('.kbs_user_search_results').addClass('hidden');
		$('.kbs_user_search_results span').html('');
	});

	if( $('#kbs_dashboard_tickets').length ) {
		$.ajax({
			type: 'GET',
			data: {
				action: 'kbs_load_dashboard_widget'
			},
			url: ajaxurl,
			success: function (response) {
				$('#kbs_dashboard_tickets .inside').html( response );
			}
		});
	}

	$(document).on('keydown', '.customer-note-input', function(e) {
		if(e.keyCode === 13 && (e.metaKey || e.ctrlKey)) {
			$('#add-customer-note').click();
		}
	});

});

// Retrieve ticket replies
function kbs_load_ticket_replies( ticket_id, reply_id )	{
	jQuery('#kbs-replies-loader').html('<img src="' + kbs_vars.ajax_loader + '" />');

	jQuery.post(ajaxurl, { action: 'kbs_display_ticket_replies', kbs_ticket_id: ticket_id, kbs_reply_id: reply_id },
		function(response)	{
			jQuery('.kbs-historic-reply-option-fields').prepend(response);
			jQuery('#kbs-replies-loader').html('');
		}
	);
}

// Retrieve ticket notes
function kbs_load_ticket_notes( ticket_id, note_id )	{
	jQuery('#kbs-notes-loader').html('<img src="' + kbs_vars.ajax_loader + '" />');

	jQuery.post(ajaxurl, { action: 'kbs_display_ticket_notes', kbs_ticket_id: ticket_id, kbs_note_id: note_id },
		function(response)	{
			jQuery('.kbs-notes-option-fields').prepend(response);
			jQuery('#kbs-notes-loader').html('');
		}
	);
}
