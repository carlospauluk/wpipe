<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Cherry_Data_Importer_Extensions' ) ) {

	/**
	 * Define Cherry_Data_Importer_Extensions class
	 */
	class Cherry_Data_Importer_Extensions {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			// Prevent from errors triggering while MotoPress Booking posts importing (loving it)
			add_filter( 'cherry_import_skip_post', array( $this, 'prevent_import_errors' ), 10, 2 );

			// Clear fonts cache after import
			add_action( 'cherry_data_import_finish', array( $this, 'clear_fonts_cache' ) );

			// Allow switch Monstroid2 skins
			add_action( 'cherry_data_import_before_skip_redirect', array( $this, 'switch_skin_on_skip_import' ) );
		}

		/**
		 * Switch Monstroid2 skin on skip demo content import.
		 *
		 * @return null
		 */
		public function switch_skin_on_skip_import() {

			if ( ! isset( $_GET['file'] ) ) {
				return;
			}

			preg_match( '/demo-content\/(.*?)\//', base64_decode( $_GET['file'] ), $matches );

			if ( empty( $matches ) || ! isset( $matches[1] ) ) {
				return;
			}

			$skin = esc_attr( $matches[1] );

			$map = array(
				'default' => 'default',
				'skin-1'  => 'skin1',
				'skin-2'  => 'skin2',
				'skin-3'  => 'skin8',
				'skin-4'  => 'skin3',
				'skin-5'  => 'skin4',
				'skin-6'  => 'skin9',
				'skin-7'  => 'skin5',
				'skin-8'  => 'skin7',
				'skin-9'  => 'skin6',
			);

			$mapped_skin = isset( $map[ $skin ] ) ? $map[ $skin ] : false;

			if ( ! $mapped_skin ) {
				return;
			}

			$skin_file = get_stylesheet_directory() . '/tm-style-switcher-pressets/' . $mapped_skin . '.json';

			if ( ! file_exists( $skin_file ) ) {
				return;
			}

			ob_start();
			include $skin_file;
			$skin_data = ob_get_clean();

			$skin_data = json_decode( $skin_data, true );

			if ( empty( $skin_data ) || ! isset( $skin_data['mods'] ) ) {
				return;
			}

			foreach ( $skin_data['mods'] as $mod => $value ) {
				set_theme_mod( $mod, $value );
			}
		}

		/**
		 * Ckear Google fonts cache.
		 *
		 * @return void
		 */
		public function clear_fonts_cache() {
			delete_transient( 'cherry_google_fonts_url' );
		}

		/**
		 * Prevent PHP errors on import.
		 *
		 * @param  bool   $skip Default skip value.
		 * @param  array  $data Plugin data.
		 * @return bool
		 */
		public function prevent_import_errors( $skip, $data ) {

			if ( isset( $data['post_type'] ) && 'mphb_booking' === $data['post_type'] ) {
				return true;
			}

			return $skip;
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
 * Returns instance of Cherry_Data_Importer_Extensions
 *
 * @return object
 */
function cdi_extensions() {
	return Cherry_Data_Importer_Extensions::get_instance();
}

cdi_extensions();
