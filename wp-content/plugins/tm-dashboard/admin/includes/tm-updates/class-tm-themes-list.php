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

// If class `Tm_Themes_List` doesn't exists yet.
if ( ! class_exists( 'Tm_Themes_List' ) ) {

	/**
	 * Tm_Themes_List class.
	 */
	class Tm_Themes_List extends Tm_Themes_Forms{

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of TM_Check_Themes class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $check_themes = null;

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->check_themes = new TM_Check_Themes();
		}
		/**
		 * Html render.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function render() {
			$themes_tm         = $this->check_themes->get_tm_themes();

			if( empty( $themes_tm ) ) {
				printf( esc_html__( 'You don`t have themes from the company %1$sTemplateMonsters%2$s', 'tm-dashboard' ), '<a href="//www.templatemonster.com" target="_blank" >', '</a>' );
				return;
			}

			$themes_list       = '';
			$current_theme     = '';

			foreach ( $themes_tm as $key => $value ) {
				$image_src  = $value['screenshot'];
				$html_block = $value['verificaton'] ? $this->get_theme_info_block( $value ) : $this->get_verificaton_block( $value ) ;
				$notification_html = ( version_compare( $value['version'], $value['update'], '<' ) ) ? apply_filters( 'tm_update_image_notification', '' ) : '' ;

				$theme_form = sprintf(
					'<div class="tm-updates__theme">
						<div class="tm-updates__theme-image">
							%5$s
							<img src="%2$s" alt="%3$s">
						</div>
						<form class="cherry-form tm-updates__theme-form " id="%1$s" name="%1$s" accept-charset="utf-8"autocomplete="on" enctype="application/x-www-form-urlencoded" method="get">
						<h3 class="tm-updates__theme-name">%3$s</h3>
							<div class="tm-updates__theme-form-controls">
								%4$s
							</div>
						</form>
					</div>',
					$value['slug'],
					esc_url( $image_src ),
					$value['name'],
					$html_block,
					$notification_html
				);

				if ( $value[ 'activate' ] ){
					$current_theme = '<h2 class="tm-updates__title">' . esc_html__( 'Currently Active Theme', 'tm-dashboard' ) . '</h2>';
					$current_theme .= $theme_form;
				} else{
					$themes_list .= $theme_form;
				}
			}

			$themes_list_title = $themes_list ? '<h2 class="tm-updates__title">' . esc_html__( 'Available Themes', 'tm-dashboard' ) . '</h2>' : '' ;

			echo $current_theme . $themes_list_title . $themes_list;
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
}
