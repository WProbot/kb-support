<?php
/**
 * Admin Options Page
 *
 * @package     KBS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since	1.0
 * @return	void
 */
function kbs_options_page() {

	if ( ! current_user_can( 'manage_ticket_settings' ) )	{
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?', 'kb-support' ) . '</h1>' .
			'<p>'  . __( 'You do not have permission to access this page.', 'kb-support' ) . '</p>',
			403
		);
	}

	$settings_tabs = kbs_get_settings_tabs();
	$settings_tabs = empty( $settings_tabs) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
	$sections      = kbs_get_settings_tab_sections( $active_tab );
	$key           = 'main';

	if ( is_array( $sections ) ) {
		$key = key( $sections );
	}

	$registered_sections = kbs_get_settings_tab_sections( $active_tab );
	$section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( $_GET['section'], $registered_sections ) ? sanitize_text_field( $_GET['section'] ) : $key;

	// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
	$all_settings = kbs_get_registered_settings();

	// Let's verify we have a 'main' section to show
	$has_main_settings = true;
	if ( empty( $all_settings[ $active_tab ]['main'] ) )	{
		$has_main_settings = false;
	}

	// Check for old non-sectioned settings
	if ( ! $has_main_settings )	{
		foreach( $all_settings[ $active_tab ] as $sid => $stitle )	{
			if ( is_string( $sid ) && is_array( $sections ) && array_key_exists( $sid, $sections ) )	{
				continue;
			} else	{
				$has_main_settings = true;
				break;
			}
		}
	}

	$override = false;
	if ( false === $has_main_settings ) {
		unset( $sections['main'] );

		if ( 'main' === $section ) {
			foreach ( $sections as $section_key => $section_title ) {
				if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
					$section  = $section_key;
					$override = true;
					break;
				}
			}
		}
	}

	ob_start();
	?>
	<div class="wrap <?php echo 'wrap-' . $active_tab; ?>">
		<h1 class="nav-tab-wrapper">
			<?php
			foreach( kbs_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'post_type'        => 'kbs_ticket',
					'page'             => 'kbs-settings',
					'settings-updated' => false,
					'tab'              => $tab_id
				), admin_url( 'edit.php' ) );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h1>
		<?php

		$number_of_sections = is_array( $sections ) ? count( $sections ) : 0;
		$number = 0;
		if ( $number_of_sections > 1 ) {
			echo '<div><ul class="subsubsub">';
			foreach( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number++;
				$tab_url = add_query_arg( array(
					'post_type'        => 'kbs_ticket',
					'page'             => 'kbs-settings',
					'settings-updated' => false,
					'tab'              => $active_tab,
					'section'          => $section_id
				), admin_url( 'edit.php' ) );

				/**
				 * Allow filtering of the section URL.
				 *
				 * Enables plugin authors to insert links to non-setting pages as sections.
				 *
				 * @since	1.1.10
				 * @param	str		The section URL
				 * @param	str		The section ID (array key)
				 * @param	str		The current active tab
				 * @return	str
				 */
				$tab_url = apply_filters( 'kbs_options_page_section_url', $tab_url, $section_id, $active_tab );

				$class = '';
				if ( $section == $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

				if ( $number != $number_of_sections ) {
					echo ' | ';
				}
				echo '</li>';
			}
			echo '</ul></div>';
		}
		?>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php

				settings_fields( 'kbs_settings' );

				if ( 'main' === $section ) {
					do_action( 'kbs_settings_tab_top', $active_tab );
				}

				do_action( 'kbs_settings_tab_top_' . $active_tab . '_' . $section );

				do_settings_sections( 'kbs_settings_' . $active_tab . '_' . $section );

				do_action( 'kbs_settings_tab_bottom_' . $active_tab . '_' . $section  );

				// For backwards compatibility
				if ( 'main' === $section ) {
					do_action( 'kbs_settings_tab_bottom', $active_tab );
				}

				// If the main section was empty and we overrode the view with the next subsection, prepare the section for saving
				if ( true === $override ) {
					?><input type="hidden" name="kbs_section_override" value="<?php echo $section; ?>" /><?php
				}
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
} // kbs_options_page
