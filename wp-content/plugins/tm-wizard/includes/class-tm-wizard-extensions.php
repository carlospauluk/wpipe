<?php
/**
 * Extensions
 *
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TM_Wizard_Extensions' ) ) {

	/**
	 * Define TM_Wizard_Extensions class
	 */
	class TM_Wizard_Extensions {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class.
		 */
		public function __construct() {

			add_action( 'tm_wizard_after_plugin_activation', array( $this, 'prevent_bp_redirect' ) );
			add_action( 'tm_wizard_after_plugin_activation', array( $this, 'prevent_bbp_redirect' ) );
			add_action( 'tm_wizard_after_plugin_activation', array( $this, 'prevent_booked_redirect' ) );
			add_action( 'tm_wizard_after_plugin_activation', array( $this, 'prevent_tribe_redirect' ) );
			add_action( 'tm_wizard_after_plugin_activation', array( $this, 'prevent_woo_redirect' ) );

			add_filter( 'tm_wizard_send_install_data', array( $this, 'add_multi_arg' ), 10, 2 );

			add_action( 'tm_dashboard_add_section', array( $this, 'add_dashboard_plugins_section' ), 25, 2 );
			add_action( 'admin_head', array( $this, 'maybe_print_dashboard_css' ), 99 );

			// Booked somitemes not processed correctly and still redirect so pervent it hard
			add_filter( 'pre_transient__booked_welcome_screen_activation_redirect', array( $this, 'hard_prevent_booked_redirect' ), 10, 2 );
		}

		/**
		 * Hard prevent booked redirect
		 *
		 * @param  bool $pre   Pre-get value.
		 * @param  bool $value Default transient value.
		 * @return mixed
		 */
		public function hard_prevent_booked_redirect( $pre, $value ) {

			if ( isset( $_REQUEST['action'] ) && 'tm_wizard_install_plugin' === $_REQUEST['action'] ) {
				return null;
			}

			if ( isset( $_GET['page'] ) && tm_wizard()->slug() === $_GET['page'] ) {
				return null;
			}

			return $pre;
		}

		/**
		 * Maybe print dashboard CSS file
		 *
		 * @return void
		 */
		public function maybe_print_dashboard_css() {

			if ( ! isset( $_GET['page'] ) || 'tm-dashboard' !== $_GET['page'] ) {
				return;
			}

			tm_wizard()->print_inline_css( 'dashboard.css' );
			wp_enqueue_script( 'tm-wizard-dashboard' );

		}

		/**
		 * Adds required theme plugins on dashboard page.
		 *
		 * @param object $builder   Builder module instance.
		 * @param object $dashboard Dashboard plugin instance.
		 */
		public function add_dashboard_plugins_section( $builder, $dashboard ) {

			$plugins = tm_wizard_settings()->get( array( 'plugins' ) );

			if ( empty( $plugins ) ) {
				return;
			}

			ob_start();

			foreach ( $plugins as $slug => $plugin ) {
				$this->single_plugin_item( $slug, $plugin );
			}

			$content = ob_get_clean();

			$builder->register_section(
				array(
					'tm-wizard' => array(
						'title' => esc_html__( 'Recommended plugins', 'tm-dashboard' ),
						'class' => 'tm-dashboard-section tm-dashboard-section--tm-wizard',
						'view'  => $dashboard->plugin_dir( 'admin/views/section.php' ),
					),
				)
			);

			$builder->register_html(
				array(
					'tm-wizard-content' => array(
						'parent' => 'tm-wizard',
						'html'   => $content,
					),
				)
			);

		}

		/**
		 * Print single plugin item for dashbaord list.
		 *
		 * @param  string $slug   Plugins slug.
		 * @param  array  $plugin Plugins data.
		 * @return void
		 */
		public function single_plugin_item( $slug, $plugin ) {

			$plugin_data = get_plugins( '/' . $slug );
			$pluginfiles = array_keys( $plugin_data );
			$installed   = true;
			$activated   = false;
			$plugin_path = null;

			if ( empty( $pluginfiles ) ) {
				$installed = false;
			} else {
				$plugin_path = $slug . '/' . $pluginfiles[0];
				$activated   = is_plugin_active( $plugin_path );
			}

			$data = array_merge(
				array(
					'slug'       => $slug,
					'pluginpath' => $plugin_path,
					'installed'  => $installed,
					'activated'  => $activated,
				),
				$plugin
			);

			tm_wizard()->get_template( 'dashboard/item.php', $data );
		}

		/**
		 * Prevent redirect after WooCommerce activation.
		 *
		 * @param  string $plugin Plugin slug.
		 * @return bool
		 */
		public function prevent_woo_redirect( $plugin ) {

			if ( 'woocommerce' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_wc_activation_redirect' );

			return true;
		}

		/**
		 * Prevent BuddyPress redirect.
		 *
		 * @return bool
		 */
		public function prevent_bp_redirect( $plugin ) {

			if ( 'buddypress' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_bp_activation_redirect' );
			delete_transient( '_bp_is_new_install' );

			return true;
		}

		/**
		 * Prevent BBPress redirect.
		 *
		 * @return bool
		 */
		public function prevent_bbp_redirect( $plugin ) {

			if ( 'bbpress' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_bbp_activation_redirect' );

			return true;
		}

		/**
		 * Prevent booked redirect.
		 *
		 * @return bool
		 */
		public function prevent_booked_redirect( $plugin ) {

			if ( 'booked' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_booked_welcome_screen_activation_redirect' );

			return true;
		}

		/**
		 * Prevent tribe events calendar redirect.
		 *
		 * @return bool
		 */
		public function prevent_tribe_redirect( $plugin ) {

			if ( 'the-events-calendar' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_tribe_tickets_activation_redirect' );
			delete_transient( '_tribe_events_activation_redirect' );

			return true;
		}

		/**
		 * Add multi-install argument.
		 *
		 * @param  array  $data   Send data.
		 * @param  string $plugin Plugin slug.
		 * @return array
		 */
		public function add_multi_arg( $data = array(), $plugin = '' ) {

			if ( in_array( $plugin, array( 'woocommerce', 'booked' ) ) ) {
				$data['activate-multi'] = true;
			}

			return $data;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
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

/**
 * Returns instance of TM_Wizard_Extensions
 *
 * @return object
 */
function tm_wizard_ext() {
	return TM_Wizard_Extensions::get_instance();
}

tm_wizard_ext();
