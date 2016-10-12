<?php
/**
 * This template is used to display a single ticket for a customer.
 *
 * @shortcode	[kbs_view_ticket]
 */
$singular = kbs_get_ticket_label_singular();
$plural   = kbs_get_ticket_label_plural();
if ( is_numeric( $_GET['ticket'] ) )	{
	$field = 'id';
} else	{
	$field = 'key';
}
$ticket = kbs_get_ticket_by( $field, $_GET['ticket'] );

if ( ! empty( $ticket->ID ) ) : ?>

	<?php $ticket = new KBS_Ticket( $ticket->ID );
	$customer = new KBS_Customer( $ticket->customer_id ); ?>

	<?php do_action( 'kbs_notices' ); ?>
	<div id="kbs_item_wrapper" class="kbs_ticket_wrapper" style="float: left">
		<div class="ticket_info_wrapper data_section">

			<?php do_action( 'kbs_before_single_ticket_form' ); ?>

			<form<?php kbs_maybe_set_enctype(); ?> id="kbs_ticket_reply_form" class="kbs_form" action="" method="post">

				<div class="kbs_item_info ticket_info">
					<fieldset id="kbs_ticket_info_details">
                    <legend><?php printf( __( 'Support %s Details # %s', 'kb-support' ), $singular, kbs_get_ticket_id( $ticket->ID ) ); ?></legend>
                        <div class="ticket_files_wrapper right">
                            <?php do_action( 'kbs_before_single_ticket_sla' ); ?>

							<?php if ( kbs_track_sla() ) : ?>
								<div class="sla_data">
                                    <strong><?php _e( 'SLA Status', 'kb-support' ); ?></strong>
                                    <span class="sla_target_respond info_item">dd</span>
                                    <span class="sla_target_resolve info_item">dd</span>
                                </div>

                            <?php endif; ?>

							<?php do_action( 'kbs_before_single_ticket_form_files' ); ?>

                            <?php if ( ! empty( $ticket->files ) ) : ?>
                                <strong><?php _e( 'File Attachments', 'kb-support' ); ?></strong>
                                <span class="ticket_files info_item">

                                    <?php foreach( $ticket->files as $file ) : ?>
                                        <span class="info_item" data-key="file<?php echo $file->ID; ?>">
                                            <a href="<?php echo wp_get_attachment_url( $file->ID ); ?>" target="_blank"><?php echo basename( get_attached_file( $file->ID ) ); ?></a>
                                        </span>
                                    <?php endforeach; ?>

                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="ticket_main_wrapper left">

                            <span class="ticket_date info_item">
                                <label><?php _e( 'Date', 'kb-support' ); ?>:</label> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->date ) ); ?>
                            </span>

                            <span class="ticket_customer_name info_item">
                                <label><?php _e( 'Logged by', 'kb-support' ); ?>:</label> <?php echo $customer->name; ?>
                            </span>

                            <span class="ticket_status info_item">
                                <label><?php _e( 'Status', 'kb-support' ); ?>:</label> <?php echo $ticket->status_nicename; ?>
                            </span>
    
                            <span class="ticket_agent info_item">
                                <label><?php _e( 'Agent', 'kb-support' ); ?>:</label> <?php echo get_userdata( $ticket->agent_id )->display_name; ?>
                            </span>

							<div class="major_ticket_items">

                                <span class="ticket_subject info_item">
                                    <label><?php _e( 'Subject', 'kb-support' ); ?>:</label> <?php esc_attr_e( $ticket->ticket_title ); ?>
                                </span>
    
                                <span class="ticket_content info_item">
                                    <label><?php _e( 'Content', 'kb-support' ); ?>:</label> <?php echo $ticket->get_content(); ?>
                                </span>

							</div>

                        </div>

					</fieldset>

					<?php do_action( 'kbs_before_single_ticket_form_replies' ); ?>

					<fieldset id="kbs_ticket_replies">
                    <legend><?php _e( 'Replies', 'kb-support' ); ?></legend>
                    <?php if ( ! empty( $ticket->replies ) ) : ?>
						<ul>
						<?php foreach( $ticket->replies as $reply ) : ?>
							<li id="kbs_ticket_reply-<?php echo $reply->ID; ?>" class="kbs-ticket-reply-head" data-item="reply-<?php echo $reply->ID; ?>">
                            	<span class="ticket_reply info-item">
									<?php echo date_i18n( get_option( 'time_format' ) . ' \o\n ' . get_option( 'date_format' ), strtotime(  $reply->post_date ) ); ?> 
									<?php _e( 'by', 'kb-support' ); ?>  
									<?php echo kbs_get_reply_author_name( $reply->ID, true ); ?>
                                </span>
                            </li>
						<?php endforeach; ?>
                        </ul>

					<?php else : ?>
						<span class="ticket-no-replies info_item">
							<p><?php _e( 'No replies yet', 'kb-support' ); ?></p>
                        </span>
                    <?php endif; ?>

					<?php do_action( 'kbs_before_single_ticket_reply' ); ?>

					<?php if( is_ssl() ) : ?>
                        <div id="kbs_secure_site_wrapper">
                            <span class="padlock"></span>
                            <span><?php _e( 'This form is secured and encrypted via SSL', 'kb-support' ); ?></span>
                        </div>
                    <?php endif; ?>

					<?php if ( 'closed' != $ticket->status ) : ?>

                        <div class="kbs_alert kbs_alert_error kbs_hidden"></div>
                        <div class="ticket_reply_fields">
    
                            <strong><?php _e( 'Add a Reply', 'kb-support' ); ?></strong>
    
                            <?php $wp_settings  = apply_filters( 'kbs_ticket_reply_editor_settings', array(
                                'media_buttons' => false,
                                'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
                                'teeny'         => true,
                                'quicktags'     => false
                            ) );
                            echo wp_editor( '', 'kbs_reply', $wp_settings ); ?>

							<?php if ( kbs_file_uploads_are_enabled() ) : ?>
                            	<?php $multiple = kbs_get_option( 'file_uploads' ) > 1 ? ' multiple' : ''; ?>
                                <div class="reply_files">
                                    <p><label for="kbs_files"><?php _e( 'Attach Files', 'kb-support' ); ?></label>
                                    	<span class="kbs-description"><?php printf( __( 'Maximum %d files', 'kb-support' ), kbs_get_max_file_uploads() ); ?></span>
                                        <input type="file" class="kbs-input" name="kbs_files[]" id="kbs-files"<?php echo $multiple; ?> />
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="reply_confirm_email">
                                <p><label for="kbs_confirm_email"><?php _e( 'Confirm your Email Address', 'kb-support' ); ?></label>
                                	<span class="kbs-description"><?php _e( 'So we can verify your identity', 'kb-support' ); ?></span>
                                    <input type="email" class="kbs-input" name="kbs_confirm_email" id="kbs-confirm-email" />
                                </p>
                            </div>
    
                            <div class="reply_close">
                                <p><input type="checkbox" name="kbs_close_ticket" id="kbs-close-ticket" value="1" /> 
									<?php printf( __( 'This %s can be closed', 'kb-support' ), strtolower( $singular ) ); ?>
                                </p>
                            </div>
    
                            <?php do_action( 'kbs_before_single_ticket_reply_button' ); ?>
                            <?php kbs_render_hidden_reply_fields( $ticket->ID ); ?>
                            <input class="button" name="kbs_ticket_reply" id="kbs_reply_submit" type="submit" value="<?php _e( 'Reply', 'kb-support' ); ?>" />
    
                        </div>

					<?php else : ?>
						<div class="kbs_alert kbs_alert_info"><?php printf( __( 'This %s is closed.', 'kb-support' ), strtolower( $singular ) ); ?></div>
					<?php endif; ?>

                </fieldset>

                </div>

            </form>

			<?php do_action( 'kbs_after_single_ticket_form' ); ?>

        </div>
    </div>

<?php else : ?>
	<?php echo kbs_display_notice( 'no_ticket' ); ?>
<?php endif; ?>
