<?php
/**
 * Agent Functions
 *
 * Functions related to agents
 *
 * @package     KBS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Whether or not a multiple agents is enabled.
 *
 * @since	1.1
 * @return	bool	True if enabled, otherwise false
 */
function kbs_multiple_agents()  {
    return kbs_get_option( 'multiple_agents', false );
} // kbs_multiple_agents

/**
 * Retrieve the agent ID from a ticket.
 *
 * @since	1.0
 * @param	int		$ticket_id	The ticket ID
 * @return	int		The agent ID
 */
function kbs_get_agent_id_from_ticket( $ticket_id )	{
	return get_post_meta( $ticket_id, '_kbs_ticket_agent_id', true );
} // kbs_get_agent_id_from_ticket

/**
 * Retrieve the additional agents from a ticket.
 *
 * @since	1.1
 * @param	int		$ticket_id	The ticket ID
 * @return	arr		Array of secondary agents assigned to a ticket
 */
function kbs_get_workers_of_ticket( $ticket_id )	{

	$agents = array();

	if ( kbs_multiple_agents() )	{
		$agents = get_post_meta( $ticket_id, '_kbs_ticket_agents', true );
	}

	return apply_filters( 'kbs_workers_of_ticket', $agents );
} // kbs_get_workers_of_ticket

/**
 * Retrieve all agents.
 *
 * @since	1.0
 * @param	bool	$ids	True to return array of IDs, false for array of user objects
 * @return	mixed
 */
function kbs_get_agents( $ids = false )	{
	$role  = array( 'support_agent', 'support_manager' );

	if ( kbs_get_option( 'admin_agents', false ) )	{
		$role[] = 'administrator';
	}

	$users = kbs_get_users_by_role( $role, $ids );
	
	return $users;
} // kbs_get_agents

/**
 * Whether or not the user is an agent.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID to check.
 * @return	bool	True if the user is an agent, or false
 */
function kbs_is_agent( $agent_id = 0 )	{

	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	$agents = kbs_get_agents( true );

	return in_array( $agent_id, $agents );

} // kbs_is_agent

/**
 * Whether or not an agent can submit a ticket from the front end.
 *
 * @since	1.0.4
 * @return	bool	True if an agent can submit a ticket, otherwise false
 */
function kbs_agent_can_submit( $can_submit )	{

	if ( kbs_is_agent() )	{
		$can_submit = apply_filters( 'kbs_agent_can_submit', false );

		add_action( 'kbs_user_cannot_submit', function() {
			ob_start();
			echo kbs_display_notice( 'agents_cannot_submit' );
			echo ob_get_clean();
		} );
	}

	return $can_submit;

} // kbs_agent_can_submit
add_filter( 'kbs_user_can_submit', 'kbs_agent_can_submit', 999 );

/**
 * Whether or not an agent can view the ticket.
 *
 * @since	1.0
 * @param	int|obj	$ticket		The ticket ID or a KBS_Ticket class object
 * @param	int		$agent_id	The user ID of the agent.
 * @return	bool	True if agent can view, otherwise false
 */
function kbs_agent_can_access_ticket( $ticket = '', $agent_id = '' )	{

	if ( empty( $ticket ) )	{
		return false;
	}

	if ( is_numeric( $ticket ) )	{
		$ticket = new KBS_Ticket( $ticket );
		if ( empty( $ticket->ID ) )	{
			return false;
		}
	}

	if ( ! kbs_get_option( 'admin_agents' ) && current_user_can( 'administrator' ) )	{
		return false;
	}

    if ( current_user_can( 'manage_ticket_settings' ) ) {
        return true;
    }

	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

    $return   = false;
	$restrict = kbs_get_option( 'restrict_agent_view' );

    if ( empty( $ticket->agent_id ) || $agent_id == $ticket->agent_id )	{
		$return = true;
	}

	if ( ! $restrict )	{
		$return = true;
	}

    if ( kbs_multiple_agents() )    {
        if ( in_array( $agent_id, $ticket->agents ) )   {
            $return = true;
        }
    }

	$allowed_statuses = array( 'new', 'auto-draft', 'draft' );
	if ( in_array( get_post_status( $ticket->ID ), $allowed_statuses ) )	{
		$return = true;
	}

	$return = apply_filters( 'kbs_agent_can_access_ticket', $return, $ticket, $agent_id );

	return (bool) $return;

} // kbs_agent_can_access_ticket

/**
 * Retrieve the agent with the least ticket count.
 *
 * @since	1.0
 * @return	int		Agent ID
 */
function kbs_get_agent_least_tickets()	{
	$agents    = kbs_get_agents( true );
	$agent_id  = 0;
	$low_count = 999999;

	foreach( $agents as $agent )	{
		$ticket_count = kbs_agent_ticket_count( $agent );

		if ( $ticket_count < $low_count )	{
			$low_count = $ticket_count;
			$agent_id  = $agent;
		}
	}

	return $agent_id;
} // kbs_get_agent_least_tickets

/**
 * Retrieve a random agent.
 *
 * @since	1.0
 * @return	int		Random agent user ID
 */
function kbs_get_random_agent()	{
	$agents   = kbs_get_agents( true );
	$agent_id = 0;

	if ( ! empty( $agents ) )	{
		$random   = array_rand( $agents, 1 );
		$agent_id = $agents[ $random ];
	}

	return $agent_id;
} // kbs_get_random_agent

/**
 * Retrieve the count of agent tickets.
 *
 * @since	1.0
 * @param	int		$agent_id	The agent ID for which to retrieve the count.
 * @return	int		Count of agent tickets
 */
function kbs_agent_ticket_count( $agent_id )	{
	$tickets = kbs_count_tickets( array( 'agent' => $agent_id ) );
	$total   = 0;

	if ( ! empty( $tickets ) )	{
		$active_statuses = kbs_get_active_ticket_status_keys();
		foreach( $tickets as $status => $count )	{
			if ( ! empty( $tickets->$status ) && in_array( $status, $active_statuses ) )	{
				$total += $count;
			}
		}
	}

	return $total;
} // kbs_agent_ticket_count

/**
 * Auto assigns an agent to a ticket.
 *
 * @since	1.0
 * @param	arr		$ticket_data	Data passed to ticket
 * @return	void
 */
function kbs_auto_assign_agent( $ticket_data )	{
	$auto_assign = kbs_get_option( 'assign_on_submit', false );

	if ( ! empty( $auto_assign ) )	{
		switch( $auto_assign )	{
			case 'least':
				$ticket_data['agent_id'] = kbs_get_agent_least_tickets();
				break;
	
			case 'random':
				$ticket_data['agent_id'] = kbs_get_random_agent();		
				break;
	
			default:
				do_action( 'kbs_auto_assign_agent', $ticket_data );
				break;
		}
	
		// If an agent is assigned update status if 'new'
		if ( ! empty( $ticket_data['agent_id'] ) )	{
			if ( empty( $ticket_data['status'] ) || 'new' == $ticket_data['status'] )	{
				$ticket_data['status'] = 'open';
			}
		}
	}

	return $ticket_data;

} // kbs_auto_assign_agent
add_filter( 'kbs_add_ticket_data', 'kbs_auto_assign_agent' );

/**
 * Log an agents online status.
 *
 * Sets a transient that tells us the agent is actively logged on.
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_agent_status()	{

	if ( is_admin() && is_user_logged_in() )	{

		$agent_id = get_current_user_id();

		$expire   = MINUTE_IN_SECONDS * 30;
		$expire   = apply_filters( 'kbs_agent_status_expire_time', $expire );
		$screen   = get_current_screen();

		if ( ! empty( $agent_id ) && kbs_is_agent( $agent_id ) )	{
			$transient_key = 'kbs_active_agent_' . $agent_id;
			set_transient( $transient_key, $screen->id, $expire );
		}

	}

} // kbs_set_agent_status
add_action( 'current_screen', 'kbs_set_agent_status' );

/**
 * Retrieve an agents status.
 *
 * This function is a wrapper for the get_transient function
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	mixed	True if online, or false
 */
function kbs_get_agent_status( $agent_id )	{
	return get_transient( 'kbs_active_agent_' . $agent_id );
} // kbs_get_agent_status

/**
 * Whether to display an agents online status.
 *
 * @since	1.0
 * @return	bool
 */
function kbs_display_agent_status()	{
	return kbs_get_option( 'agent_status', false );
} // kbs_display_agent_status

/**
 * Whether or not an agent is online.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	bool	True if online, or false
 */
function kbs_agent_is_online( $agent_id )	{
	$status = kbs_get_agent_status( $agent_id );

	$online = false === $status ? false : true;

	return apply_filters( 'kbs_agent_is_online', $online, $agent_id );
} // kbs_agent_is_online

/**
 * Sets an agents status to offline during logoff.
 *
 * @since	1.0
 * @return	void
 */
function kbs_set_agent_offline( $agent_id = 0 )	{
	if ( empty( $agent_id ) )	{
		$agent_id = get_current_user_id();
	}

	if ( kbs_is_agent( $agent_id ) )	{
		delete_transient( 'kbs_active_agent_' . $agent_id );
	}
} // kbs_set_agent_offline
add_action( 'login_form_logout', 'kbs_set_agent_offline' );

/**
 * Retrieve an agents online status.
 *
 * @since	1.0
 * @param	int		$agent_id	The user ID of the agent to check
 * @return	str		'online' | 'offline'
 */
function kbs_get_agent_online_status( $agent_id )	{
	$status = kbs_agent_is_online( $agent_id ) ? 'online' : 'offline';
	$status = apply_filters( 'kbs_agent_online_status', $status, $agent_id );

	return $status;
} // kbs_get_agent_online_status

/**
 * Retrieve count of agents currently online and available.
 *
 * @since	1.0
 * @return	int
 */
function kbs_get_online_agent_count()	{
	$agent_ids = kbs_get_agents( true );
	$online    = 0;

	foreach( $agent_ids as $agent_id )	{
		if ( kbs_agent_is_online( $agent_id ) )	{
			$online++;
		}
	}

	return (int)$online;
} // kbs_get_online_agent_count
