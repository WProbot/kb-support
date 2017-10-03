<?php	
/**
 * Manage kbs-ticket posts.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove the post lock for tickets.
 *
 * By default this function does not run unless the
 * `kbs_disable_ticket_post_lock` is filtered.
 *
 * @since	1.1
 * @return	void
 */
function kbs_disable_ticket_post_lock()	{

	/**
	 * Allow developers to override the default setting
	 * which is NOT to disable the post lock
	 *
	 */
	if ( ! apply_filters( 'kbs_disable_ticket_post_lock', false ) )	{
		return;
	}

    if ( 'kbs_ticket' != get_current_screen()->post_type ) {
		return;
	}

	add_filter( 'show_post_locked_dialog', '__return_false' );
	add_filter( 'wp_check_post_lock_window', '__return_false' );
	wp_deregister_script('heartbeat');

} // kbs_disable_ticket_post_lock
add_action( 'load-edit.php', 'kbs_disable_ticket_post_lock' );
add_action( 'load-post.php', 'kbs_disable_ticket_post_lock' );

/**
 * Define the columns that should be displayed for the KBS ticket post lists screen
 *
 * @since	1.0
 * @param	arr		$columns	An array of column name ⇒ label. The label is shown as the column header.
 * @return	arr		$columns	Filtered array of column name => label to be shown as the column header.
 */
function kbs_set_kbs_ticket_post_columns( $columns ) {

	$category_labels = kbs_get_taxonomy_labels( 'ticket_category' );
	$tag_labels      = kbs_get_taxonomy_labels( 'ticket_tag' );

	$columns = array(
        'cb'               => '<input type="checkbox" />',
        'id'               => '#',
		'dates'            => __( 'Date', 'kb-support' ),
		'title'            => __( 'Title', 'kb-support' ),
        'customer'         => __( 'Customer', 'kb-support' ),
		'ticket_category'  => $category_labels['menu_name'],
		'ticket_tag'       => $tag_labels['menu_name'],
        'agent'            => __( 'Agent', 'kb-support' )
    );
	
	if ( kbs_track_sla() )	{
		$columns['sla'] = __( 'SLA Status', 'kb-support' );
	}
	
	return apply_filters( 'kbs_ticket_post_columns', $columns );
	
} // kbs_set_kbs_ticket_post_columns
add_filter( 'manage_kbs_ticket_posts_columns' , 'kbs_set_kbs_ticket_post_columns' );

/**
 * Define which columns are sortable for ticket posts
 *
 * @since	1.0.9
 * @param	arr		$sortable_columns	Array of sortable columns
 * @return	arr		Array of sortable columns
 */
function kbs_ticket_post_sortable_columns( $sortable_columns )	{
	$sortable_columns['id']    = 'id';
    $sortable_columns['dates'] = 'date';
	
	return $sortable_columns;
} // kbs_ticket_post_sortable_columns
add_filter( 'manage_edit-kbs_ticket_sortable_columns', 'kbs_ticket_post_sortable_columns' );

/**
 * Define the data to be displayed within the KBS ticket post custom columns.
 *
 * @since	1.0
 * @param	str		$column_name	The name of the current column for which data should be displayed.
 * @param	int		$post_id		The ID of the current post for which data is being displayed.
 * @return	str
 */
function kbs_set_kbs_ticket_column_data( $column_name, $post_id ) {

	$kbs_ticket = new KBS_Ticket( $post_id );

	switch ( $column_name ) {
		case 'id':
			echo kb_tickets_post_column_id( $post_id, $kbs_ticket );
			break;

		case 'dates':
			echo kb_tickets_post_column_date( $post_id, $kbs_ticket );
			break;

		case 'customer':
			echo kb_tickets_post_column_customer( $post_id, $kbs_ticket );
			$company = kbs_get_company_name( $kbs_ticket->company_id );
			if ( ! empty( $company ) )	{
				echo '<br />' . $company;
			}
			break;

		case 'ticket_category':
            $terms = wp_get_post_terms( $post_id, 'ticket_category' );
			if ( $terms )	{
				$output = array();

                foreach( $terms as $term )  {
                    $output[] = sprintf(
                        '<a href="%s">' . $term->name . '</a>',
                        add_query_arg( 'ticket_category', $term->slug )
                    );
                }

				echo implode( ', ', $output );

			} else	{
				echo '&mdash;';
			}
			break;

		case 'ticket_tag':
			$terms = wp_get_post_terms( $post_id, 'ticket_tag' );
			if ( $terms )	{
				$output = array();

                foreach( $terms as $term )  {
                    $output[] = sprintf(
                        '<a href="%s">' . $term->name . '</a>',
                        add_query_arg( 'ticket_tag', $term->slug )
                    );

                }

				echo implode( ', ', $output );

			} else	{
				echo '&mdash;';
			}
			break;

		case 'agent':
			echo kb_tickets_post_column_agent( $post_id, $kbs_ticket );
			break;
			
		case 'sla':
			echo kb_tickets_post_column_sla( $post_id, $kbs_ticket );
			break;

		default:
			echo __( 'No callback found for post column', 'kb-support' );
			break;
	}

} // kbs_set_kbs_ticket_column_data
add_action( 'manage_kbs_ticket_posts_custom_column' , 'kbs_set_kbs_ticket_column_data', 10, 2 );

/**
 * Output the ID row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The KBS Ticket object
 * @return	str
 */
function kb_tickets_post_column_id( $ticket_id, $kbs_ticket )	{
	do_action( 'kbs_tickets_pre_column_id', $kbs_ticket );

	$output = '<a href="' . get_edit_post_link( $ticket_id ) . '">' . kbs_format_ticket_number( kbs_get_ticket_number( $ticket_id ) ) . '</a>';
	$output .= '<br />';
	$output .= get_post_status_object( $kbs_ticket->post_status )->label;

	do_action( 'kbs_tickets_post_column_id', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_id', $output, $ticket_id );
} // kb_tickets_post_column_id

/**
 * Output the Date row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The KBS Ticket object
 * @return	str
 */
function kb_tickets_post_column_date( $ticket_id, $kbs_ticket )	{
	do_action( 'kbs_tickets_pre_column_date', $kbs_ticket );

	$output  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $kbs_ticket->date ) );
	$output .= '<br />';
	$output .= date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $kbs_ticket->modified_date ) );

	do_action( 'kbs_tickets_post_column_date', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_date', $output, $ticket_id );
} // kb_tickets_post_column_date

/**
 * Output the Customer row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The KBS Ticket object
 * @return	str
 */
function kb_tickets_post_column_customer( $ticket_id, $kbs_ticket )	{
	do_action( 'kbs_tickets_pre_column_customer', $kbs_ticket );

	if ( ! empty( $kbs_ticket->customer_id ) )	{

		$customer = new KBS_Customer( $kbs_ticket->customer_id );

		$customer_page = add_query_arg( array(
			'post_type' => 'kbs_ticket',
			'page'      => 'kbs-customers',
			'view'      => 'userdata',
			'id'        => $kbs_ticket->customer_id
		), admin_url( 'edit.php' ) );

		$output = '<a href="' . $customer_page . '">' . $customer->name . '</a>';

	} else	{
		$output = __( 'No Customer Assigned', 'kb-support' );
	}

	do_action( 'kbs_tickets_post_column_customer', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_customer', $output, $ticket_id );
} // kb_tickets_post_column_customer

/**
 * Output the Agent row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The KBS Ticket object
 * @return	str
 */
function kb_tickets_post_column_agent( $ticket_id, $kbs_ticket )	{
	do_action( 'kbs_tickets_pre_column_agent', $kbs_ticket );

	if ( ! empty( $kbs_ticket->agent_id ) && $agent = get_userdata( $kbs_ticket->agent_id ) )	{
		$output = sprintf( '<a href="%s">%s</a>',
			get_edit_user_link( $kbs_ticket->agent_id ),
			$agent->display_name
		);
	} else	{
		$output = __( 'No Agent Assigned', 'kb-support' );
	}

	do_action( 'kbs_tickets_post_column_agent', $kbs_ticket );

	return apply_filters( 'kb_tickets_post_column_agent', $output, $ticket_id );
} // kb_tickets_post_column_agent

/**
 * Output the SLA Status row.
 *
 * @since	1.0
 * @param	int	$ticket_id	The ticket ID
 * @param	obj	$kbs_ticket	The KBS Ticket object
 * @return	str
 */
function kb_tickets_post_column_sla( $ticket_id, $kbs_ticket )	{
	do_action( 'kbs_tickets_pre_column_sla', $kbs_ticket );

	$output = kbs_display_sla_status_icons( $kbs_ticket, '<br />', false );

	do_action( 'kbs_tickets_post_column_sla', $kbs_ticket );

	$output = apply_filters( 'kb_tickets_post_column_sla', $output, $ticket_id );

	return implode( '<br />', $output );

} // kb_tickets_post_column_sla

/**
 * Order tickets.
 *
 * @since	1.0.9
 * @param	obj		$query		The WP_Query object
 * @return	void
 */
function kbs_order_admin_tickets( $query )	{
	
	if ( ! is_admin() || 'kbs_ticket' != $query->get( 'post_type' ) )	{
		return;
	}

    return;

	$orderby = '' == $query->get( 'orderby' ) ? 'date' : $query->get( 'orderby' );
	$order   = '' == $query->get( 'order' )   ? 'DESC' : $query->get( 'order' );

	switch( $orderby )	{
		case 'ID':
        case 'id':
			$query->set( 'orderby',  'ID' );
			$query->set( 'order',  $order );
			break;

		case 'post_date':
        case 'date':
        case 'dates':
			$query->set( 'orderby',  'post_date' );
			$query->set( 'order',  $order );
			break;

		case 'title':
			$query->set( 'orderby',  'ID' );
			$query->set( 'order',  $order );
			break;			
	}
	
} // kbs_order_admin_tickets
add_action( 'pre_get_posts', 'kbs_order_admin_tickets' );

/**
 * Filter tickets query so agents only see their own tickets and new tickets.
 *
 * @since	1.0
 * @return	void
 */
function kbs_restrict_agent_ticket_view( $query )	{

	if ( ! is_admin() || 'kbs_ticket' != $query->get( 'post_type' ) )	{
		return;
	}

	// If user is admin and admins are agents, they see all.
	if ( kbs_get_option( 'admin_agents' ) && ( current_user_can( 'manage_ticket_settings' ) || current_user_can( 'administrator' ) ) )	{
		return;
	}

	if ( kbs_get_option( 'restrict_agent_view', false ) )	{
		$agent_id = get_current_user_id();

        $meta_query = array(
            'relation' => 'OR',
            array(
                'key'     => '_kbs_ticket_agent_id',
                'value'   => $agent_id,
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => '_kbs_ticket_agent_id',
                'value'   => ''
            ),
            array(
                'key'     => '_kbs_ticket_agent_id',
                'value'   => 'anything',
                'compare' => 'NOT EXISTS'
            )
        );

        if ( kbs_multiple_agents() )    {
            $meta_query[] = array(
                'key'     => '_kbs_ticket_agents',
                'value'   => sprintf( ':%d;', $agent_id ),
                'compare' => 'LIKE'
            );
        }

        $query->set( 'meta_query', $meta_query );

  }

} // kbs_restrict_agent_ticket_view
add_action( 'pre_get_posts', 'kbs_restrict_agent_ticket_view' );

/**
 * Add Ticket Filters
 *
 * Adds taxonomy drop down filters for tickets.
 *
 * @since	1.0
 * @return	void
 */
function kbs_add_ticket_filters() {
	global $typenow;

	if ( 'kbs_ticket' == $typenow ) {
		$terms = get_terms( 'ticket_category' );

		if ( count( $terms ) > 0 )	{
			$category_labels = kbs_get_taxonomy_labels( 'ticket_category' );

			echo "<select name='ticket_category' id='ticket_category' class='postform'>";
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'kb-support' ), strtolower( $category_labels['name'] ) ) . "</option>";

				foreach ( $terms as $term )	{
					$selected = isset( $_GET['ticket_category'] ) && $_GET['ticket_category'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}

			echo "</select>";
		}

		$terms = get_terms( 'ticket_tag' );
		if ( count( $terms ) > 0 )	{
			$tag_labels = kbs_get_taxonomy_labels( 'ticket_tag' );

			echo "<select name='ticket_tag' id='ticket_tag' class='postform'>";
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'kb-support' ), strtolower( $tag_labels['name'] ) ) . "</option>";

				foreach ( $terms as $term ) {
					$selected = isset( $_GET['ticket_tag'] ) && $_GET['ticket_tag'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}

			echo "</select>";
		}

		if ( isset( $_REQUEST['all_posts'] ) && '1' === $_REQUEST['all_posts'] )	{

			echo '<input type="hidden" name="all_posts" value="1" />';

		} elseif ( ! current_user_can( 'view_ticket_reports' ) )	{

			$author_id = get_current_user_id();
			echo '<input type="hidden" name="author" value="' . esc_attr( $author_id ) . '" />';

		}
	}

} // kbs_add_ticket_filters
add_action( 'restrict_manage_posts', 'kbs_add_ticket_filters', 100 );

/**
 * Filter tickets by customer.
 *
 * @since	1.0
 * @return	void
 */
function kbs_filter_customer_tickets( $query )	{
	if ( ! is_admin() || 'kbs_ticket' != $query->get( 'post_type' ) || ! isset( $_GET['customer'] ) )	{
		return;
	}

	$query->set( 'meta_key', '_kbs_ticket_customer_id' );
	$query->set( 'meta_value', $_GET['customer'] );
	$query->set( 'meta_type', 'NUMERIC' );
} // kbs_filter_customer_tickets
add_action( 'pre_get_posts', 'kbs_filter_customer_tickets' );

/**
 * Filter tickets by copmany.
 *
 * @since	1.0
 * @return	void
 */
function kbs_filter_company_tickets( $query )	{
	if ( ! is_admin() || 'kbs_ticket' != $query->get( 'post_type' ) || ! isset( $_GET['company_id'] ) )	{
		return;
	}

	$query->set( 'meta_key', '_kbs_ticket_company_id' );
	$query->set( 'meta_value', $_GET['company_id'] );
	$query->set( 'meta_type', 'NUMERIC' );
} // kbs_filter_customer_tickets
add_action( 'pre_get_posts', 'kbs_filter_company_tickets' );

/**
 * Hide inactive tickets from the 'all' tickets list.
 *
 * @since	1.0
 * @param	obj		$query	The WP_Query.
 * @return	void
 */
function kbs_remove_inactive_tickets( $query )	{
	if ( ! is_admin() || ! $query->is_main_query() || 'kbs_ticket' != $query->get( 'post_type' ) )	{
		return;
	}

	if ( ! kbs_get_option( 'hide_closed', false ) )	{
		return;
	}

	if ( isset( $_GET['post_status'] ) && 'all' != $_GET['post_status'] )	{
		return;
	}

	$active_statuses = kbs_get_ticket_status_keys( false );

	if ( ( $key = array_search( 'closed', $active_statuses ) ) !== false )	{
		unset( $active_statuses[ $key ] );
	}

	$active_tickets = kbs_get_tickets( array(
		'status' => $active_statuses,
		'fields' => 'ids',
		'output' => 'fields',
		'number' => -1
	) );

	if ( $active_tickets )	{
		$query->set( 'post__in', $active_tickets );
	}

} // kbs_remove_inactive_tickets
add_action( 'pre_get_posts', 'kbs_remove_inactive_tickets' );

/**
 * Customise the view filter counts
 *
 * @since	1.0
 * @param	arr		$views		Array of views
 * @return	arr		$views		Filtered Array of views
 */
function kbs_ticket_filter_views( $views )	{

	$active_only = kbs_get_option( 'hide_closed' );

	if ( 'kbs_ticket' != get_post_type() || ! $active_only )	{
		return $views;
	}

	$args = array();
	if ( kbs_get_option( 'restrict_agent_view' ) && ! current_user_can( 'manage_ticket_settings' ) )	{
		$args['agent'] = get_current_user_id();
	}

	$all_statuses      = kbs_get_ticket_status_keys( false );
	$inactive_statuses = kbs_get_inactive_ticket_statuses();
	$num_posts         = kbs_count_tickets( $args );
	$count             = 0;

	if ( ! empty( $num_posts ) )	{
		foreach( $num_posts as $status => $status_count )	{
			if ( ! empty( $num_posts->$status ) && in_array( $status, $all_statuses ) )	{
				$views[ $status ] = preg_replace( '/\(.+\)/U', '(' . number_format_i18n( $num_posts->$status ) . ')', $views[ $status ] );
			}
			if ( ! in_array( $status, $inactive_statuses ) )	{
				$count += $status_count;
			}
		}
	}

	$views['all'] = preg_replace( '/\(.+\)/U', '(' . number_format_i18n( $count ) . ')', $views['all'] );

	if ( $active_only )	{
		$search       = __( 'All', 'kb-support' );
		$replace      = sprintf( __( 'Active %s', 'kb-support' ), kbs_get_ticket_label_plural() ); 
		$views['all'] = str_replace( $search, $replace, $views['all'] );
	}

	foreach( $views as $status => $link )	{
		if ( 'all' != $status && 'trash' != $status && ! in_array( $status, $all_statuses ) )	{
			unset( $views[ $status ] );
		}
	}

	// Force trash view to end
	if ( isset( $views['trash'] ) )	{
		$trashed = $views['trash'];
		unset( $views['trash'] );
		$views['trash'] = $trashed;
	}

	return apply_filters( 'kbs_ticket_views', $views );

} // kbs_ticket_filter_views
add_filter( 'views_edit-kbs_ticket' , 'kbs_ticket_filter_views' );

/**
 * Remove action items from the bulk item menu and post row action list.
 *
 * @since	1.0
 * @param	arr		$actions	The action items array
 * @return	arr		Filtered action items array
 */
function kbs_tickets_remove_trash_action( $actions )	{
	if ( 'kbs_ticket' == get_post_type() )	{

		$remove_actions = array( 'edit', 'trash', 'inline hide-if-no-js' );

		foreach( $remove_actions as $remove_action )	{

			if ( doing_filter( 'bulk_actions-edit-kbs_ticket' ) && current_user_can( 'manage_ticket_settings' ) && 'trash' == $remove_action )	{
				continue;
			}

			if ( isset( $actions[ $remove_action ] ) )	{
				unset( $actions[ $remove_action ] );
			}

		}

	}

	return $actions;
} // kbs_tickets_remove_bulk_trash
add_filter( 'bulk_actions-edit-kbs_ticket', 'kbs_tickets_remove_trash_action' );
add_filter( 'post_row_actions', 'kbs_tickets_remove_trash_action' );

/**
 * Save the KBS Ticket custom posts
 *
 * @since	1.0
 * @param	int		$post_id		The ID of the post being saved.
 * @param	obj		$post			The WP_Post object of the post being saved.
 * @param	bool	$update			Whether an existing post if being updated or not.
 *
 * @return	void
 */
function kbs_ticket_post_save( $post_id, $post, $update )	{	

	if ( ! isset( $_POST['kbs_ticket_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['kbs_ticket_meta_box_nonce'], 'kbs_ticket_meta_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( is_admin() )	{
		if ( isset( $_POST['_kbs_ticket_logged_by'] ) )	{
			add_post_meta( $post_id, '_kbs_ticket_logged_by', absint( $_POST['_kbs_ticket_logged_by'] ), true );
		}
	}
	// The default fields that get saved
	$fields = kbs_ticket_metabox_fields();

	$ticket = new KBS_Ticket( $post_id );

	foreach ( $fields as $key => $field )	{

		$posted_value = '';

		if ( ! empty( $_POST[ $field ] ) ) {

			if ( is_string( $_POST[ $field ] ) )	{
				$posted_value = sanitize_text_field( $_POST[ $field ] );
			} elseif ( is_int( $_POST[ $field ] ) )	{
				$posted_value = absint( $_POST[ $field ] );
			} elseif( is_array( $_POST[ $field ] ) )	{
				$posted_value = array_map( 'absint', $_POST[ $field ] );
			}
		}

		$new_value = apply_filters( 'kbs_ticket_metabox_save_' . $field, $posted_value );

		$ticket->__set( $key, $new_value );

	}

	if ( empty( $ticket->key ) )	{
		$auth_key  = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$key = strtolower( md5( $ticket->email . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'kbs', true ) ) );
		$ticket->__set( 'key', $key );
	}

    $ticket_number = get_post_meta( $post_id, '_kbs_ticket_number', true );
    if ( ! $ticket_number ) {
        $number = kbs_get_next_ticket_number();
        if ( $number ) {
            $number = kbs_format_ticket_number( $number );
            $ticket->__set( 'number', $number );
            update_option( 'kbs_last_ticket_number', $number );
        }
    }

	if ( ! empty( $_POST['ticket_status'] ) && $_POST['ticket_status'] != $post->post_status )	{
		$ticket->__set( 'status', $_POST['ticket_status'] );
	}

	$ticket->save();

	add_post_meta( $post_id, '_kbs_ticket_version_created', KBS_VERSION, true );
	do_action( 'kbs_save_ticket', $post_id, $post );

} // kbs_ticket_post_save
add_action( 'save_post_kbs_ticket', 'kbs_ticket_post_save', 10, 3 );
