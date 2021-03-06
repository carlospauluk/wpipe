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

// If class `Tm_Get_Theme_Update` doesn't exists yet.
if ( ! class_exists( 'Tm_Get_Theme_Update' ) ) {

	/**
	 * Tm_Get_Theme_Update class.
	 */
	class Tm_Get_Theme_Update extends TM_Remote_Query{

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Contains a link to the update.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string
		 */
		private $api_upd = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/get-update?template=%1$s&order_id=%2$s&update_version=%3$s';

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 */
		private $respons = array(
			'slug'          => '',
			'error'         => false,
			'updateVersion' => '',
			'message'       => '',
		);

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			tm_dashboard()->get_core()->init_module( 'cherry-js-core' );
			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_update_theme',
				'action'       => 'tm_update_theme',
				'capability'   => 'manage_options',
				'type'         => 'GET',
				'callback'     => array( $this, 'update_theme' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Unable to process the request without nonche or server error', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No right for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Stop CHEATING!!!', 'tm-dashboard' ),
					'access_is_allowed' => '',
				),
			) );
		}

		/**
		 * .
		 *
		 * @since  1.0.0
		 * @access public
		 * @return json
		 */
		public function update_theme() {
			if ( empty( $_GET['data'] ) ) {
				return;
			}

			$slug = $_GET['data']['slug'];
			$dir  = get_theme_root( $slug );
			$dir  .= '/'. $slug;
			$this->respons[ 'slug' ] = $slug;
			$this->respons[ 'message' ] = esc_html__( 'Update successfully.', 'tm-dashboard' );

			$verified_themes = get_option( 'verified_themes', array() );

			if ( empty( $verified_themes ) || empty( $verified_themes[ $slug ] ) ) {
				$this->respons[ 'error' ] = true;
				$this->respons[ 'message' ] = esc_html__( 'Theme not verified', 'tm-dashboard' );
				return $this->respons;
			}

			$url = sprintf( $this->api_upd, $verified_themes[ $slug ]['product-id'], $verified_themes[ $slug ]['order-id'], $_GET['data']['updateVersion'] );
			$respons = $this->get_request( $url );
			$this->respons[ 'updateVersion' ] = $_GET['data']['updateVersion'];

			if ( true === $respons['error'] || empty( $respons['download_url'] ) ){
				$this->respons[ 'error' ] = true;
				$this->respons[ 'message' ] = esc_html__( 'Can not get update.', 'tm-dashboard' );
				return $this->respons;
			}

			$result = $this->intall_packag( $respons['download_url'], $dir );

			if ( is_wp_error( $result ) ){
				$this->respons[ 'error' ] = true;
				$this->respons[ 'message' ] = esc_html__( 'Can not install update.', 'tm-dashboard' );
				return $this->respons;
			}
			return $this->respons;
		}

		/**
		 * .
		 *
		 * @since  1.0.0
		 * @access private
		 * @return object
		 */
		private function intall_packag( $url, $dir ){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-updater-upgrader-skin.php' );

			$args = array(
				'package'                     => $url, // Please always pass this.
				'destination'                 => $dir, // And this
				'clear_destination'           => true,
				'abort_if_destination_exists' => true, // Abort if the Destination directory exists, Pass clear_destination as false please
				'clear_working'               => true,
			);

			$wp_upgrader = new WP_Upgrader( new TM_Update_Upgrader_Skin() );
			$result = $wp_upgrader->run( $args );

			return $result;
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


	if ( ! function_exists( 'tm_update_theme' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_update_theme() {
			return Tm_Get_Theme_Update::get_instance();
		}
	}

	tm_update_theme();
}
