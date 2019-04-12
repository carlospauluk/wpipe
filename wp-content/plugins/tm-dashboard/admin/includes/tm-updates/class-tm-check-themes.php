<?php
/**
 * Plugin installer skin class.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}
// If class `TM_Check_Themes` doesn't exists yet.
if ( ! class_exists( 'TM_Check_Themes' ) ) {

	class TM_Check_Themes extends TM_Remote_Query{

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
		private $api_upd = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/check-update?template=%1$s&current_version=%2$s';

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $check_count = 0;

		/**
		 *.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $pattern = '/[www\.]*template\s*monster[\.com]*/';

		public function add_action() {
			/**
			 * Need for test update - set_site_transient( 'update_themes', null );
			 */
			add_action( 'pre_set_site_transient_update_themes', array( $this, 'set_version_update' ) );
		}
		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_tm_themes() {
			$themes_tm       = array();
			$themes          = wp_get_themes();
			$activate_theme  = wp_get_theme()->get_stylesheet();

			foreach ( $themes as $key => $value ) {
				$аuthor         = strtolower( $value->get( 'Author', false ) );
				$аuthor_uri     = strtolower( $value->get( 'AuthorURI' ) );
				$theme_url      = strtolower( $value->get( 'ThemeURI' ) );

				if ( preg_match( $this->pattern, $аuthor ) || preg_match( $this->pattern, $аuthor_uri ) || preg_match( $this->pattern, $theme_url ) ) {
					$stylesheet     = $value->__get( 'stylesheet' );
					$theme_root_uri = $value->__get( 'theme_root_uri' );
					$template_id    = $this->get_template_id( $stylesheet, $theme_root_uri );

					$themes_tm[ $key ] = array(
						'template_id'    => $template_id,
						'name'           => $value->get( 'Name' ),
						'slug'           => $stylesheet,
						'screenshot'     => $theme_root_uri . '/' . $stylesheet . '/' . $value->__get( 'screenshot' ),
						'activate'       => ( $activate_theme === $stylesheet ) ? true : false,
						'verificaton'    => $this->get_verified( $stylesheet ),
						'update'         => $this->get_version_update( $stylesheet ),
						'version'        => $value->get( 'Version' ),
					);
				}
			}

			return $themes_tm;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_verified( $slug ) {
			$verified_themes = get_option( 'verified_themes', array() );

			if ( ! empty( $verified_themes[ $slug ] ) ) {
				return $verified_themes[ $slug ];
			}
			return false;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_version_update( $slug ) {
			$updates = get_option( 'tm_updates_themes', array() );

			if( empty( $updates[ $slug ] ) ){
				return false;
			}

			return $updates[ $slug ]['update'];
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_template_id( $slug, $tim_path ) {
			$template_id_in_file    = get_file_data(
				$tim_path . '/' . $slug . '/' . 'style.css',
				array( 'templateID' => 'Template Id', )
			);

			if ( ! empty( $template_id_in_file['templateID'] ) ) {
				return $template_id_in_file['templateID'];
			}

			$verified_themes = get_option( 'verified_themes', array() );

			if ( ! empty( $verified_themes[ $slug ] ) ) {
				return $verified_themes[ $slug ]['product-id'];
			}

			return false;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function set_version_update() {
			if ( 1 > self::$check_count ) {
				$themes_tm          = $this->get_tm_themes();
				$db_version_update  = get_option( 'tm_updates_themes', array() );
				$new_version_update = array();

				if( empty( $themes_tm ) ) {
					return;
				}

				foreach ( $themes_tm as $key => $value) {
					$respons = $this->check_theme_update( $value['template_id'], $value['version'] );
					$new_version_update[ $key ] = array( 'version' => $value['version'], 'update' => $value['version'] );

					if( $respons && empty( $respons->error ) ){
						$new_version_update[ $key ]['update'] = $respons->version;
					}
				}

				$new_version_update = array_merge( $db_version_update, $new_version_update );
				update_option( 'tm_updates_themes', $new_version_update );

				self::$check_count++;
			}
		}

		/**
		 * .
		 *
		 * @since  1.0.0
		 * @access public
		 * @return json
		 */
		public function check_theme_update( $theme_id, $current_version ) {
			$url     = sprintf( $this->api_upd, $theme_id, $current_version );
			$respons = json_decode ( $this->get_request( $url ) );

			if ( ! empty( $respons->error ) && true === $respons->error ){
				$respons = new stdClass();
				$respons->version = $current_version;
				$respons->error   = true;

				return $respons;
			}

			return $respons;
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

	if ( ! function_exists( 'tm_save_theme' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_save_theme() {
			return TM_Check_Themes::get_instance();
		}
	}

	tm_save_theme()->add_action();
}


