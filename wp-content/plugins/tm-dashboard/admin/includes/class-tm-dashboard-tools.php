<?php
/**
 * Plugin tools.
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.0.3
 * @license   GPL-3.0+
 * @copyright 2012-2016, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'TM_Dashboard_Tools' not exists.
if ( ! class_exists( 'TM_Dashboard_Tools' ) ) {

	/**
	 * Interface management class.
	 */
	class TM_Dashboard_Tools {

		/**
		 * Class constructor.
		 */
		public function __construct() {}

		public static function get_template_id() {
			$theme_data = get_file_data(
				get_stylesheet_directory() . '/style.css',
				array( 'TextDomain' => 'Text Domain' ),
				'theme'
			);

			return ! empty( $theme_data['templateID'] ) ? $theme_data['templateID'] : $theme_data['TextDomain'];
		}

		/**
		 * Get the current theme info, theme name and version.
		 *
		 * @since 1.0.3
		 * @return array
		 */
		public static function get_theme_info() {
			$theme_data    = wp_get_theme();
			$theme_name    = $theme_data->Name;
			$theme_version = $theme_data->Version;

			$theme_child_theme = is_child_theme() ? esc_html__( 'Yes', 'tm-dashboard' ) : esc_html__( 'No', 'tm-dashboard' );

			return array(
				'id' => array(
					'name'  => esc_html__( 'ID', 'tm-dashboard' ),
					'value' => self::get_template_id(),
				),
				'name' => array(
					'name'  => esc_html__( 'Name', 'tm-dashboard' ),
					'value' => $theme_name,
				),
				'version' => array(
					'name'  => esc_html__( 'Version', 'tm-dashboard' ),
					'value' => $theme_version,
				),
				'child_theme' => array(
					'name'  => esc_html__( 'Child Theme', 'tm-dashboard' ),
					'value' => $theme_child_theme,
				),
			);
		}

		public static function get_site_info() {
			return apply_filters( 'tm_dashboard_get_site_info', array(
				'site_title' => array(
					'name'  => esc_html__( 'Site Title', 'tm-dashboard' ),
					'value' => get_bloginfo( 'name' ),
				),
				'site_url' => array(
					'name'  => esc_html__( 'Site URL', 'tm-dashboard' ),
					'value' => esc_url( site_url( '/' ) ),
				),
				'home_url' => array(
					'name'  => esc_html__( 'Home URL', 'tm-dashboard' ),
					'value' => esc_url( home_url( '/' ) ),
				),
				'is_multisite' => array(
					'name'  => esc_html__( 'Multisite', 'tm-dashboard' ),
					'value' => is_multisite() ? esc_html__( 'Yes', 'tm-dashboard' ) : esc_html__( 'No', 'tm-dashboard' ),
				),
				'email' => array(
					'name'  => esc_html__( 'Email Address', 'tm-dashboard' ),
					'value' => sanitize_email( get_option( 'admin_email' ) ),
				),
			) );
		}

		public static function get_installed_themes( $skip_default = true ) {
			$installed = array();
			$all       = wp_get_themes();
			$default   = array(
				'twentyten',
				'twentyeleven',
				'twentytwelve',
				'twentythirteen',
				'twentyfourteen',
				'twentyfifteen',
				'twentysixteen',
				'twentyseventeen',
			);

			foreach ( $all as $theme_slug => $theme_data ) {

				if ( $skip_default && in_array( $theme_slug, $default ) ) {
					continue;
				}

				$installed[ $theme_slug ] = array(
					'Name'    => $theme_data->Name,
					'Version' => $theme_data->Version,
				);
			}

			return $installed;
		}

		public static function get_installed_plugins() {

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = get_plugins();
			$active  = get_option( 'active_plugins', array() );
			$result  = array(
				'active'   => array(),
				'inactive' => array(),
			);

			foreach ( $plugins as $plugin_path => $plugin ) {

				$current = array(
					'Name'    => $plugin['Name'],
					'Version' => $plugin['Version'],
				);

				if ( in_array( $plugin_path, $active ) ) {
					$result['active'][] = $current;
				} else {
					$result['inactive'][] = $current;
				}
			}

			return $result;
		}

		public static function get_server_params() {
			$server_params = array();

			if ( function_exists( 'phpversion' ) ) {
				$server_params['php_version'] = array(
					'name'  => esc_html__( 'PHP Version', 'tm-dashboard' ),
					'value' => phpversion(),
				);
			}

			if ( function_exists( 'ini_get' ) ) {
				$server_params['memory_limit'] = array(
					'name'  => esc_html__( 'PHP Memory Limit', 'tm-dashboard' ),
					'value' => ini_get( 'memory_limit' ),
				);

				$server_params['post_max_size'] = array(
					'name'  => esc_html__( 'PHP Post Max Size', 'tm-dashboard' ),
					'value' => ini_get( 'post_max_size' ),
				);

				$server_params['upload_max_filesize'] = array(
					'name'  => esc_html__( 'PHP Max Upload File Size', 'tm-dashboard' ),
					'value' => ini_get( 'upload_max_filesize' ),
				);

				$server_params['max_input_time'] = array(
					'name'  => esc_html__( 'PHP Max Input Time', 'tm-dashboard' ),
					'value' => ini_get( 'max_input_time' ),
				);

				$server_params['max_execution_time'] = array(
					'name'  => esc_html__( 'PHP Time Limit', 'tm-dashboard' ),
					'value' => ini_get( 'max_execution_time' ),
				);
			}

			return apply_filters( 'tm_dashboard_server_params', $server_params );
		}
	}
}
