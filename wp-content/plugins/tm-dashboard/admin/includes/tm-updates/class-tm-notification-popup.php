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

// If class `Tm_Notification_Popup` doesn't exists yet.
if ( ! class_exists( 'Tm_Notification_Popup' ) ) {

	/**
	 * Tm_Notification_Popup class.
	 */
	class Tm_Notification_Popup {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of `Cherry_Interface_Builder` class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $builder = null;

		/**
		 * Class constructor.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			if ( ! defined( 'DOING_AJAX' ) ) {
				add_action( 'admin_print_footer_scripts', array( $this, 'render_popup' ), 99 );
				$this->builder = tm_dashboard()->get_core()->init_module( 'cherry-interface-builder' );
			}
		}

		public function render_popup( $hook ) {
			$notice = sprintf(
				esc_html__( 'Attention! We recommend to %1$sbackup your theme files manually%2$s or with the help of %3$sBackUpWordPress%4$s plugin before the updating because they will be overwritten. If you didn`t make a backup, please cancel the updating.', 'tm-dashboard' ),
				'<a href="https://codex.wordpress.org/WordPress_Backups" target="_blank">',
				'</a>',
				'<a href="https://wordpress.org/plugins/backupwordpress/" target="_blank">',
				'</a>'
			);

			$this->builder->reset_structure();

			$this->builder->register_html( array(
				'view_wrapping' => false,
				'id'            => 'notice-text',
				'html'          => '<div class="tm-updates-popup-notice">' . $notice . '</div>',
			) );

			$this->builder->register_settings( array(
				'id'            => 'tm-updates-popup-buttons',
			) );

			$this->builder->register_control( array(
				'update-theme-continue'  => array(
					'type'          => 'button',
					'style'         => 'success',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Ok', 'tm-dashboard' ),
					'parent'        => 'tm-updates-popup-buttons',
				),
				'update-theme-cancel'  => array(
					'type'          => 'button',
					'style'         => 'primary',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Cancel', 'tm-dashboard' ),
					'parent'        => 'tm-updates-popup-buttons',
				)
			) );


			$content = $this->builder->render( false );

			printf( '<div class="tm-updates-popup"><div class="tm-updates-popup-inner">%1$s</div><div class="tm-updates-popup-background"></div></div>', $content );
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

	if ( ! function_exists( 'tm_notification_popup' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_notification_popup() {
			return Tm_Notification_Popup::get_instance();
		}
	}

	tm_notification_popup();
}
