<?php	
/**
 * Manage kbs_ticket post metaboxes.
 * 
 * @since		0.1
 * @package		KBS
 * @subpackage	Functions/Metaboxes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns default KBS Ticket meta fields.
 *
 * @since	1.0
 * @return	arr		$fields		Array of fields.
 */
function kbs_ticket_metabox_fields() {

	// Format is KBS_Ticket key => field name
	$fields = array(
		'customer_id' => 'kbs_customer_id',
		'agent_id'    => 'kbs_agent_id',
        'agents'      => 'kbs_assigned_agents'
	);

	return apply_filters( 'kbs_ticket_metabox_fields_save', $fields );

} // kbs_ticket_metabox_fields

/**
 * Remove unwanted metaboxes to for the kbs_ticket post type.
 *
 * @since	1.0
 * @param
 * @return
 */
function kbs_ticket_remove_meta_boxes()	{
	$metaboxes = apply_filters( 'kbs_ticket_remove_metaboxes',
		array(
			array( 'submitdiv', 'kbs_ticket', 'side' ),
			array( 'comments', 'kbs_ticket', 'normal' )
		)
	);

	foreach( $metaboxes as $metabox )	{
		remove_meta_box( $metabox[0], $metabox[1], $metabox[2] );
	}
} // kbs_ticket_remove_meta_boxes
add_action( 'admin_head', 'kbs_ticket_remove_meta_boxes' );

/**
 * Define and add the metaboxes for the kbs_ticket post type.
 *
 * @since	1.0
 * @param	obj		$post	The WP_Post object.
 * @return	void
 */
function kbs_ticket_add_meta_boxes( $post )	{

	if ( ! kbs_agent_can_access_ticket( $post->ID ) )	{
		wp_die(
			sprintf(
				__( 'You do not have access to this %s. <a href="%s">Go Back</a>', 'kb-support' ),
				kbs_get_ticket_label_singular( true ),
				admin_url( 'edit.php?post_type=kbs_ticket' )
			),
			__( 'Error', 'kb-support' ),
			array( 'response' => 403 )
		);
	}

	global $kbs_ticket, $kbs_ticket_update;

	$save              = __( 'Create', 'kb-support' );
	$kbs_ticket_update = false;
	$kbs_ticket        = new KBS_Ticket( $post->ID );
	$single_label      = kbs_get_ticket_label_singular();
    $ticket_number     = '';

	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$save              = __( 'Update', 'kb-support' );
		$kbs_ticket_update = true;
        $ticket_number     = '# ' . kbs_format_ticket_number( $kbs_ticket->number );
		remove_post_type_support( $post->post_type, 'editor' );
	}

	add_meta_box(
		'kbs-ticket-metabox-save',
		sprintf( '%1$s %2$s %3$s', $save, $single_label, $ticket_number ),
		'kbs_ticket_metabox_save_callback',
		'kbs_ticket',
		'side',
		'high',
		array()
	);

    add_meta_box(
		'kbs-ticket-metabox-agents',
		__( 'Assignment', 'kb-support' ),
		'kbs_ticket_metabox_agents_callback',
		'kbs_ticket',
		'side',
		'high',
		array()
	);

	if ( $kbs_ticket_update )	{
		add_meta_box(
			'kbs-ticket-metabox-ticket-details',
			sprintf( __( 'Original %1$s', 'kb-support' ), $single_label ),
			'kbs_ticket_metabox_data_callback',
			'kbs_ticket',
			'normal',
			'high',
			array()
		);

		if ( 'new' != $kbs_ticket->status )	{

			add_meta_box(
				'kbs-ticket-metabox-ticket-reply',
				sprintf( __( 'Reply to %1$s', 'kb-support' ), $single_label ),
				'kbs_ticket_metabox_reply_callback',
				'kbs_ticket',
				'normal',
				'high',
				array()
			);

		}

		add_meta_box(
			'kbs-ticket-metabox-ticket-private',
			__( 'Private Notes', 'kb-support' ),
			'kbs_ticket_metabox_notes_callback',
			'kbs_ticket',
			'normal',
			'high',
			array()
		);
	}
	
} // kbs_ticket_add_meta_boxes
add_action( 'add_meta_boxes_kbs_ticket', 'kbs_ticket_add_meta_boxes' );

/**
 * The callback function for the save metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 * @param
 * @return
 */
function kbs_ticket_metabox_save_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	wp_nonce_field( 'kbs_ticket_meta_save', 'kbs_ticket_meta_box_nonce' );

	/*
	 * Output the items for the save metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_status_fields', $post->ID );
} // kbs_ticket_metabox_save_callback

/**
 * The callback function for the agent assignment metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 * @param
 * @return
 */
function kbs_ticket_metabox_agents_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the save metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_agent_fields', $post->ID );
} // kbs_ticket_metabox_agents_callback

/**
 * The callback function for the original ticket details metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 * @param
 * @return
 */
function kbs_ticket_metabox_data_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the details metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_data_fields', $post->ID );
} // kbs_ticket_metabox_data_callback

/**
 * The callback function for the ticket reply metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 * @param
 * @return
 */
function kbs_ticket_metabox_reply_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the ticket reply metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_reply_fields', $post->ID );
} // kbs_ticket_metabox_reply_callback

/**
 * The callback function for the private notes metabox.
 *
 * @since	1.0
 * @global	obj		$post				WP_Post object
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 * @param
 * @return
 */
function kbs_ticket_metabox_notes_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the pruvate notes metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_notes_fields', $post->ID );
} // kbs_ticket_metabox_notes_callback

/**
 * Display the save ticket metabox row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_save_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update; ?>

	<div class="submitbox" id="submitpost">
		<div id="minor-publishing">
        	<div id="minor-publishing-actions">
                <div class="clear"></div>
            </div><!-- #minor-publishing-actions -->
            <div id="kbs-ticket-actions">

                <?php do_action( 'kbs_ticket_metabox_save_top', $ticket_id ); ?>

				<?php if ( $kbs_ticket_update ) : ?>

					<?php $date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' ); ?>

                    <p><label><?php _e( 'Received', 'kb-support' ); ?>:</label>
                        <?php echo date_i18n( $date_format, strtotime( $kbs_ticket->date ) ); ?>
                    </p>
    
                    <?php if ( $kbs_ticket->date != $kbs_ticket->modified_date ) : ?>
                        <p><label><?php _e( 'Updated', 'kb-support' ); ?>:</label>
                            <?php echo date_i18n( $date_format, strtotime( $kbs_ticket->modified_date ) ); ?>
                        </p>
                    <?php endif; ?>

				<?php endif; ?>

                <?php do_action( 'kbs_ticket_metabox_save_before_status', $ticket_id ); ?>

                <p><label for="ticket_status"><?php _e( 'Status', 'kb-support' ); ?>:</label>
                    <?php echo KBS()->html->ticket_status_dropdown( 'ticket_status', $kbs_ticket->post_status ); ?>
                </p>

                <?php do_action( 'kbs_ticket_metabox_save_after_status', $ticket_id ); ?>

                <p><label for="kbs_customer_id"><?php _e( 'Customer', 'kb-support' ); ?>:</label>
					<?php echo KBS()->html->customer_dropdown( array(
                        'name'     => 'kbs_customer_id',
                        'selected' => $kbs_ticket->customer_id,
                        'chosen'   => false
                    ) ); ?>
                </p>

                <?php do_action( 'kbs_ticket_metabox_save_after_customer', $ticket_id ); ?>

                <p><a href="<?php echo wp_get_referer(); ?>"><?php printf( __( 'Back to %s', 'kb-support' ), kbs_get_ticket_label_plural() ); ?></a>

					<?php submit_button( 
                        sprintf( '%s %s',
                            empty( $kbs_ticket_update ) ? __( 'Add', 'kb-support' ) : __( 'Update', 'kb-support' ),
                            kbs_get_ticket_label_singular()
                        ),
                        'primary',
                        'save',
                        false,
                        array( 'id' => 'save-post' )
                    ); ?>
                </p>

                <?php do_action( 'kbs_ticket_metabox_save_bottom', $ticket_id ); ?>

            </div><!-- #kbs-ticket-actions -->
        </div><!-- #minor-publishing -->
    </div><!-- #submitpost -->
    <?php

} // kbs_ticket_metabox_save_row
add_action( 'kbs_ticket_status_fields', 'kbs_ticket_metabox_save_row', 10 );

/**
 * Output the SLA data for the ticket.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_sla_row( $ticket_id )	{
	
	global $kbs_ticket, $kbs_ticket_update;
	
	if ( ! kbs_track_sla() || ! $kbs_ticket_update )	{
		return;
	}
	
	$sla_respond_class  = '';
	$sla_resolve_class  = '';
	$sla_respond_remain = '';
	$sla_resolve_remain = '';
	
	if ( $kbs_ticket_update )	{
		$respond            = $kbs_ticket->sla_respond;
		$resolve            = $kbs_ticket->sla_resolve;
		$sla_respond_class  = kbs_sla_has_passed( $kbs_ticket->ID ) ? '_over' : '';
		$sla_resolve_class  = kbs_sla_has_passed( $kbs_ticket->ID, 'resolve' ) ? '_over' : '';
		$sla_respond_remain = ' ' . $kbs_ticket->get_sla_remain();
		$sla_resolve_remain = ' ' . $kbs_ticket->get_sla_remain( 'resolve' );
	} else	{
		$respond = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( kbs_calculate_sla_target_response() ) );
		$resolve = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( kbs_calculate_sla_target_resolution() ) );
	}

	if ( ! empty( $ticket->sla_respond ) || ! empty( $kbs_ticket->sla_resolve ) ) : ?>
        <p><strong><?php _e( 'SLA Status', 'kb-support' ); ?></strong></p>
        <p><?php echo kbs_display_sla_response_status_icon( $kbs_ticket ); ?></p>
            
        <p><?php echo kbs_display_sla_resolve_status_icon( $kbs_ticket ); ?></p>
    
    <?php endif;

} // kbs_ticket_metabox_sla_row
add_action( 'kbs_ticket_metabox_save_after_customer', 'kbs_ticket_metabox_sla_row', 10 );

/**
 * Display the agent ticket metabox row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_agent_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

    ?>
    <div id="kbs-agent-options">
        <p><label for="kbs_agent_id"><?php _e( 'Agent', 'kb-support' ); ?>:</label>
            <?php echo KBS()->html->agent_dropdown( array(
                'name'     => 'kbs_agent_id',
                'selected' => ( ! empty( $kbs_ticket->agent_id ) ? $kbs_ticket->agent_id : get_current_user_id() )
            ) ); ?>
        </p>
    </div>
    <?php

} // kbs_ticket_metabox_agent_row
add_action( 'kbs_ticket_agent_fields', 'kbs_ticket_metabox_agent_row', 10 );

/**
 * Display the additional agents ticket metabox row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_additional_agents_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

    if ( ! kbs_multiple_agents() )  {
        return;
    }

    ?>
    <div id="kbs-multi-agent-options">
        <p><label for="kbs_assigned_agents"><?php _e( 'Additional Agents', 'kb-support' ); ?>:</label>
            <?php echo KBS()->html->agent_dropdown( array(
                'name'            => 'kbs_assigned_agents',
                'selected'        => $kbs_ticket->agents,
                'chosen'          => true,
                'multiple'        => true,
                'show_option_all' => false,
                'placeholder'     => __( 'Select Additional Agents', 'kb-support' ),
                'exclude'         => array( $kbs_ticket->agent_id )
            ) ); ?>
        </p>

        <?php do_action( 'kbs_ticket_metabox_after_agent', $ticket_id ); ?>
    </div>
    <?php

} // kbs_ticket_metabox_additional_agents_row
add_action( 'kbs_ticket_agent_fields', 'kbs_ticket_metabox_additional_agents_row', 50 );

/**
 * Display the original ticket details row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_content_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	?>
	<div id="kbs-ticket-content-container" class="kbs_ticket_wrap">
        <p><?php echo $kbs_ticket->get_content(); ?></p>
        <?php if ( ! empty( $kbs_ticket->ticket_meta['form_data'] ) ) : ?>
        	<p><a href="#TB_inline?width=600&height=550&inlineId=terms-conditions" title="Terms &amp; Conditions for Add-ons" class="thickbox">View license terms</a>.</p>
        <?php endif; ?>
    </div>
    <?php
		
} // kbs_ticket_metabox_details_row
add_action( 'kbs_ticket_data_fields', 'kbs_ticket_metabox_content_row', 10 );

/**
 * Display the ticket form submission details row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_form_data_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update; ?>

	<?php if ( ! empty( $kbs_ticket->form_data ) ) : ?>

        <div id="kbs-ticket-form-data-container" class="kbs_ticket_wrap">
                <p><a href="#TB_inline?width=600&height=550&inlineId=kbs-ticket-form-data" title="<?php _e( 'Submitted Form Data', 'kb-support' ); ?>" class="thickbox kbs_ticket_form_data"><?php _e( 'View Form Submission Data', 'kb-support' ); ?></a></p>
        </div>

    <?php endif; ?>

    <div id="kbs-ticket-form-data" class="kbs-hidden"><?php echo $kbs_ticket->show_form_data(); ?></div>
    <?php
		
} // kbs_ticket_metabox_form_data_row
add_action( 'kbs_ticket_data_fields', 'kbs_ticket_metabox_form_data_row', 20 );

/**
 * Display the ticket files row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_files_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	if ( ! kbs_get_option( 'file_uploads' ) )	{
		return;
	}

	?>

	<?php if ( $kbs_ticket_update && ! empty( $kbs_ticket->files ) ) : ?>
		
		<div id="kbs-original-ticket-files-wrap" class="kbs_ticket_files_wrap">
        	<p><strong><?php _e( 'Attached Files', 'kb-support' ); ?></strong></p>
			<?php foreach( $kbs_ticket->files as $file ) : ?>
                <p><a class="kbs_ticket_form_data" href="<?php echo wp_get_attachment_url( $file->ID ); ?>" target="_blank"><?php echo basename( get_attached_file( $file->ID ) ); ?></a></p>
            <?php endforeach; ?>
		</div>
	
	<?php endif;
		
} // kbs_ticket_metabox_details_row
add_action( 'kbs_ticket_data_fields', 'kbs_ticket_metabox_files_row', 30 );

/**
 * Display the ticket replies row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_existing_replies_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	?>
    <div id="kbs-replies-loader"></div>

    <div id="kbs_historic_reply_fields" class="kbs_meta_table_wrap">
        <div class="widefat">
            <div class="kbs-historic-reply-option-fields"></div>
        </div>
    </div>
	<?php

} // kbs_ticket_metabox_replies_row
add_action( 'kbs_ticket_reply_fields', 'kbs_ticket_metabox_existing_replies_row', 10 );

/**
 * Display the ticket reply row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_reply_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	if ( 'closed' == $kbs_ticket->post_status ) : ?>
		<p>
			<?php printf( __( 'This %1$s is currently closed. <a href="%2$s">Re-open %3$s.</a>', 'kb-support' ),
				kbs_get_ticket_label_singular( true ),
				wp_nonce_url( add_query_arg( 'kbs-action', 're-open-ticket', get_edit_post_link( $ticket_id ) ), 'kbs-reopen-ticket', 'kbs-ticket-nonce' ),
				kbs_get_ticket_label_singular()
			); ?>
		</p>
	<?php else :

		$settings = apply_filters( 'kbs_ticket_reply_mce_settings', array(
			'textarea_rows'    => 10,
			'quicktags'        => true
		) ); ?>

		<div id="kbs-ticket-reply-wrap">
        	<p><label for="kbs_ticket_reply"><strong><?php _e( 'Add a New Reply', 'kb-support' ); ?></strong></label><br />
            	<?php do_action( 'kbs_ticket_metabox_before_reply_content', $ticket_id );
				wp_editor( '', 'kbs_ticket_reply', $settings ); ?>
            </p>
        </div>

		<?php
		/*
		 * Fires immediately before the reply buttons are output
		 * @since	1.0
		 * @param	int	$post_id	The Ticket post ID
		 */
		do_action( 'kbs_ticket_before_reply_buttons', $ticket_id );

		?>
        <div id="kbs-ticket-reply-container">
            <div class="kbs-reply"><a id="kbs-reply-update" class="button button-primary"><?php _e( 'Reply', 'kb-support' ); ?></a></div>
            <div class="kbs-reply"><a id="kbs-reply-close" class="button button-secondary"><?php _e( 'Reply and Close', 'kb-support' ); ?></a></div>
        </div>
        <div id="kbs-new-reply-loader"></div>

	<?php endif;
		
} // kbs_ticket_metabox_details_row
add_action( 'kbs_ticket_reply_fields', 'kbs_ticket_metabox_reply_row', 20 );

/**
 * Display the ticket add note row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_notes_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	global $kbs_ticket, $kbs_ticket_update;

	?>
    <div id="kbs-notes-loader"></div>

    <div id="kbs_notes_fields" class="kbs_meta_table_wrap">
        <div class="widefat">
            <div class="kbs-notes-option-fields"></div>
        </div>
    </div>
	<?php

} // kbs_ticket_metabox_notes_row
add_action( 'kbs_ticket_notes_fields', 'kbs_ticket_metabox_notes_row', 10 );

/**
 * Display the ticket add note row.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_add_note_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update; ?>

	<div id="kbs-ticket-add-note-container">
    	<p><label for="kbs_new_note"><strong><?php _e( 'Add a New Note', 'kb-support' ); ?></strong></label><br />
			<?php echo KBS()->html->textarea( array(
                'name'  => 'kbs_new_note',
				'id'    => 'kbs_new_note',
                'desc'  => __( 'Notes are only visible to support workers', 'kb-support' ),
                'class' => 'large-text',
                'rows'  => 5
            ) ); ?>
        </p>
        <?php
        /*
         * Fires immediately before the add note button is output.
         * @since	1.0
         * @param	int	$post_id	The Ticket post ID
         */
        do_action( 'kbs_ticket_before_add_note_button', $ticket_id ); ?>

		<div class="kbs-add-note"><a id="kbs-add-note" class="button button-secondary"><?php _e( 'Add Note', 'kb-support' ); ?></a></div>
        <div id="kbs-new-note-loader"></div>
	</div>
	<?php
		
} // kbs_ticket_metabox_add_note_row
add_action( 'kbs_ticket_notes_fields', 'kbs_ticket_metabox_add_note_row', 20 );
