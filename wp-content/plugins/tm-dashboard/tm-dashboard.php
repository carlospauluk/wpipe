<?php
/**
 * Plugin Name: TM Dashboard
 * Plugin URI:  http://www.cherryframework.com/
 * Description: Dashboard for Template Monster themes.
 * Version:     1.0.3.1
 * Author:      Cherry Team
 * Text Domain: tm-dashboard
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.0.3.1
 * @license   GPL-3.0+
 * @copyright 2012-2016, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `TM_Dashboard` doesn't exists yet.
if ( ! class_exists( 'TM_Dashboard' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class TM_Dashboard {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of cherry framework core class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $core = null;

		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = '1.0.3.1';

		/**
		 * Plugin folder URL.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_url = '';

		/**
		 * Plugin folder path.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_dir = '';

		/**
		 * Plugin slug.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_slug = 'tm-dashboard';

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

			$this->includes();

			// Do this only on backend.
			if ( is_admin() ) {
				$this->init_hooks();
				$this->updater();
			}
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 1.0.3
		 */
		public function init_hooks() {
			// Internationalize the text strings used.
			add_action( 'plugins_loaded', array( $this, 'lang' ), 1 );

			// Register stylesheet and javascript.
			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );

			// Load the installer core.
			add_action( 'after_setup_theme', require( trailingslashit( dirname( __FILE__ ) ) . 'cherry-framework/setup.php' ), 0 );

			add_action( 'after_setup_theme', array( $this, 'get_core' ),                 1 );
			add_action( 'after_setup_theme', array( 'Cherry_Core', 'load_all_modules' ), 2 );
			add_action( 'after_setup_theme', array( $this, 'load_interface' ),           3 );

			add_filter( 'extra_theme_headers', array( $this, 'add_extra_headers' ) );
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0.3
		 */
		public function includes() {
			require_once $this->plugin_dir( 'admin/includes/class-tm-dashboard-tools.php' );

			if ( defined( 'DOING_CRON' ) && 'yes' === get_option( 'tm_dashboard_allow_tracking', 'yes' ) ) {
				require_once $this->plugin_dir( 'admin/includes/class-tm-dashboard-tracker.php' );
			}
		}

		/**
		 * Init updater.
		 *
		 * @since 1.0.0
		 */
		public function updater() {
			require_once $this->plugin_dir( 'admin/includes/class-cherry-update/class-cherry-plugin-update.php' );

			$updater = new Cherry_Plugin_Update();
			$updater->init( array(
				'version'         => $this->version,
				'slug'            => $this->plugin_slug,
				'repository_name' => $this->plugin_slug,
				'product_name'    => 'templatemonster',
			) );
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 */
		public function lang() {
			load_plugin_textdomain( 'tm-dashboard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Register stylesheet and javascript.
		 *
		 * @since 1.0.0
		 */
		public function register_assets( $hook ) {
			wp_register_style(
				'tm-dashboard',
				$this->plugin_url( 'admin/assets/css/min/admin.min.css' ),
				array(),
				$this->version
			);

			wp_register_style(
				'tm-dashboard-notification',
				$this->plugin_url( 'admin/assets/css/min/admin-notification.min.css' ),
				array(),
				$this->version
			);

			wp_register_script(
				'tm-dashboard',
				$this->plugin_url( 'admin/assets/js/min/admin.min.js' ),
				array( 'cherry-js-core', 'cherry-handler-js' ),
				$this->version,
				true
			);
		}

		/**
		 * Loads the core functions. These files are needed before loading anything else in the
		 * plugin because they have required functions for use.
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public function get_core() {

			/**
			 * Fires before loads the plugin's core.
			 *
			 * @since 1.0.0
			 */
			do_action( 'tm_dashboard_core_before' );

			global $chery_core_version;

			if ( null !== $this->core ) {
				return $this->core;
			}

			if ( 0 < sizeof( $chery_core_version ) ) {
				$core_paths = array_values( $chery_core_version );
				require_once( $core_paths[0] );
			} else {
				die( 'Class Cherry_Core not found' );
			}

			$this->core = new Cherry_Core( array(
				'base_dir' => $this->plugin_dir( 'cherry-framework' ),
				'base_url' => $this->plugin_url( 'cherry-framework' ),
				'modules'  => array(
					'cherry-js-core' => array(
						'autoload' => false,
					),
					'cherry-ui-elements' => array(
						'autoload' => false,
					),
					'cherry-interface-builder' => array(
						'autoload' => false,
					),
					'cherry-handler' => array(
						'autoload' => false,
					),
				),
			) );

			return $this->core;
		}

		/**
		 * Loads interface.
		 *
		 * @since 1.0.0
		 */
		public function load_interface() {
			require_once $this->plugin_dir( 'admin/includes/class-tm-dashboard-interface.php' );
		}

		/**
		 * Add new extra headers `DocumentationID` - TemplateMonster unique.
		 *
		 * @since 1.0.0
		 * @param array
		 */
		public function add_extra_headers( $headers ) {
			$headers[] = 'DocumentationID';

			return $headers;
		}

		/**
		 * Get plugin URL (or some plugin dir/file URL)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			if ( null != $path ) {
				return $this->plugin_url . $path;
			}

			return $this->plugin_url;
		}

		/**
		 * Get plugin dir path (or some plugin dir/file path)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_dir( $path = null ) {

			if ( ! $this->plugin_dir ) {
				$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			if ( null != $path ) {
				return $this->plugin_dir . $path;
			}

			return $this->plugin_dir;
		}

		/**
		 * On plugin activation.
		 *
		 * @since 1.0.0
		 */
		public function activation() {
			$this->run_tracker();
		}

		/**
		 * On plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		public function deactivation() {}

		/**
		 * Tracker's schedule.
		 *
		 * @since 1.0.0
		 */
		public function run_tracker() {
			wp_clear_scheduled_hook( 'tm_dashboard_run_tracker' );
			wp_schedule_single_event( time(), 'tm_dashboard_run_tracker' );
		}

		/**
		 * Returns the instance.
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}

if ( ! function_exists( 'tm_dashboard' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tm_dashboard() {
		return TM_Dashboard::get_instance();
	}
}

tm_dashboard();
