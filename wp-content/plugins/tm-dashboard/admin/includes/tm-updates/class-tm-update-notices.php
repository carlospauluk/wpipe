<?php
/**
 * Update/Install Plugin/Theme administration panel.
 *
 * @package    TM_Dashboard
 * @subpackage Class
 * @author     Cherry Team
 * @version    1.0.0
 * @license    GPL-3.0+
 * @copyright  2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class `Tm_Update_Notices` doesn't exists yet.
if ( ! class_exists( 'Tm_Update_Notices' ) ) {

	/**
	 * Tm_Update_Notices class.
	 */
	class Tm_Update_Notices {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Marker informing about the update.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $menu_bage = '';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->menu_bage = $this->menu_notification();

			add_action( 'admin_notices', array( &$this, 'page_notification' ) );
			add_filter( 'tm_update_image_notification', array( &$this, 'image_notification' ) );
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_update() {
			$updates = get_option( 'tm_updates_themes', array() );
			$need_update = array();

			if ( empty( $updates ) ) {
				return false;
			}

			$themes = wp_get_themes();
			foreach ( $updates as $slug => $value ) {
				if( ! isset( $themes[ $slug ] ) ){
					continue;
				}
				$get_theme = $themes[ $slug ];

				if ( version_compare( $get_theme->get( 'Version' ), $value['update'], '<' ) ) {
					$need_update[ $slug ] = $get_theme;
				}
			}

			return $need_update;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function image_notification() {
			return sprintf( '<div class="update-message notice inline notice-warning notice-alt tm-notification-image-lable"><p>%1$s</p></div>', esc_html__( 'New version available.', 'tm-dashboard' ) );
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function page_notification( $current_screen ) {
			$current_screen = get_current_screen();
			$update_coutn = $this->get_update();

			if ( 'tm-dashboard' === $current_screen->parent_base || ! $update_coutn ) {
				return;
			}

			printf(
				'<div class="updated"><p>%1$s <a href="admin.php?page=tm-updates">%2$s</a></p></div>',
				esc_html__( 'New version available.', 'tm-dashboard' ),
				esc_html__( 'View.', 'tm-dashboard' )
			);
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function menu_notification() {
			$update_coutn = $this->get_update();

			if ( ! $update_coutn ) {
				return '';
			}

			return '<span class="tm-notification-lable">' . count( $update_coutn ) . '</span>';
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
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

	if ( ! function_exists( 'tm_update_notices' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_update_notices() {
			return Tm_Update_Notices::get_instance();
		}
	}

	tm_update_notices();
}
