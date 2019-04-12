<?php
/**
 * TM Dashboard Tracker.
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.0.0
 * @license   GPL-3.0+
 * @copyright 2012-2016, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'TM_Dashboard_Tracker' not exists.
if ( ! class_exists( 'TM_Dashboard_Tracker' ) ) {

	class TM_Dashboard_Tracker {

		/**
		 * URL to the Tracker API endpoint.
		 *
		 * @since 1.0.3
		 * @var string
		 */
		private static $api_url = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/v1/clients';

		/**
		 * Hook into cron event.
		 *
		 * @since 1.0.3
		 */
		public static function init() {
			add_action( 'tm_dashboard_run_tracker', array( __CLASS__, 'send_tracking_data' ) );
		}

		/**
		 * Decide whether to send tracking data or not.
		 *
		 * @since 1.0.3
		 */
		public static function send_tracking_data() {

			// Don't trigger this on AJAX requests.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( false !== get_option( 'tm_dashboard_tracker_last_send', false ) ) {
				return;
			}

			// Update time first before sending to ensure it is set.
			update_option( 'tm_dashboard_tracker_last_send', time() );

			$result = array();
			$params = self::get_tracking_data();
			$args   = array(
				'method' => 'POST',
				'body'   => $params,
			);

			return wp_remote_post( self::$api_url, $args );
		}

		/**
		 * Get all the tracking data.
		 *
		 * @since 1.0.3
		 * @return array
		 */
		private static function get_tracking_data() {
			$data = array();

			$data['site']     = wp_list_pluck( TM_Dashboard_Tools::get_site_info(), 'value' );
			$data['template'] = wp_list_pluck( TM_Dashboard_Tools::get_theme_info(), 'value' );
			$data['themes']   = TM_Dashboard_Tools::get_installed_themes();

			$plugins = TM_Dashboard_Tools::get_installed_plugins();
			$data['plugins']['active']   = $plugins['active'];
			$data['plugins']['inactive'] = $plugins['inactive'];

			$data['server'] = wp_list_pluck( TM_Dashboard_Tools::get_server_params(), 'value' );

			return apply_filters( 'tm_dashboard_tracker_data', $data );
		}
	}

	TM_Dashboard_Tracker::init();
}
