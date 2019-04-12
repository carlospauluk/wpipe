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

// If class `Tm_Themes_Forms` doesn't exists yet.
if ( ! class_exists( 'Tm_Themes_Forms' ) ) {

	/**
	 * Tm_Themes_Forms class.
	 */
	class Tm_Themes_Forms{

		/**
		 * A reference to an instance of `Cherry_Interface_Builder` class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $builder = null;

		private $button_element = '<span class="loader-wrapper"><span class="loader"></span></span><span class="dashicons dashicons-yes icon icon-success"></span><span class="dashicons dashicons-no icon icon-error"></span>';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			tm_dashboard()->get_core()->init_module( 'cherry-js-core' );
			$this->builder = tm_dashboard()->get_core()->init_module( 'cherry-interface-builder' );
		}

		public function get_theme_info_block( $args ) {
			$current_version     = $args['version'];
			$version_update      = $args['update'];
			$disabled            = version_compare( $current_version, $version_update, '<' ) ? false : true ;
			$update_button_class = $disabled ? 'disabled': 'tm-update-theme utb-js' ;

			$this->builder->reset_structure();
			$this->builder->register_control( array(
				'check-theme-' . $args['slug']  => array(
					'type'          => 'button',
					'style'         => 'success',
					'view_wrapping' => false,
					'disabled'      => $disabled,
					'content'       => '<span class="text">' . esc_html__( 'Update', 'tm-dashboard' ) . '</span>' . $this->button_element,
					'form'          => $args['slug'],
					'child_class'   => 'updater-theme-button ' . $update_button_class,
				),
				/*'backup-theme'  => array(
					'type'          => 'button',
					'style'         => 'primary',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Backup', 'tm-dashboard' ) . $this->button_element,
					'form'          => $args['slug'],
					'child_class'   => 'updater-theme-button utb-js',
				),
				'restore-theme'  => array(
					'type'          => 'button',
					'style'         => 'warning',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Restore', 'tm-dashboard' ) . $this->button_element,
					'form'          => $args['slug'],
					'child_class'   => 'updater-theme-button utb-js',
				),*/
			) );

			$this->builder->register_html( array(
				'view_wrapping' => false,
				'id'            => 'update-version',
				'html'          => '<input type="hidden" name="update-version" value="' . $version_update . '"><input type="hidden" name="version" value="' . $current_version . '">',
			) );

			$buttons = $this->builder->render( false );

			return sprintf(
				'<table class="tm-updates__theme-info-table">
					<tr><td>%1$s</td><td class="current-version">%2$s</td></tr>
					<tr><td>%3$s</td><td>%4$s</td></tr>
				</table>
				%5$s',
				esc_html__( 'Theme version:', 'tm-dashboard' ),
				$current_version,
				esc_html__( 'Updates available:', 'tm-dashboard' ),
				$version_update,
				$buttons
			);
		}

		public function get_verificaton_block( $args ) {
			$form_hidden_fields = array();
			$form_fields        = array();
			$notice             = array(
				'id'   => 'tm-updater-notice',
				'html' => esc_html__( 'In order to get your theme updatings please enter the order ID and your theme ID.', 'tm-dashboard' ),
			);

			if ( $args['template_id'] ) {
				$form_hidden_fields['product-id'] = array(
					'view_wrapping' => false,
					'html'          => '<input type="hidden" name="product-id" value="' . $args['template_id'] . '">',
				);
				$notice             = array(
					'id'   => 'tm-updates-notice',
					'html' => esc_html__( 'In order to get your theme updatings please enter the order ID.', 'tm-dashboard' ),
				);
			} else {
				$form_fields['product-id'] = array(
					'type'          => 'text',
					'value'         => '',
					'view_wrapping' => false,
					'placeholder'   => esc_html__( 'Template ID', 'tm-dashboard' ),
					'class'         => '',
					'label'         => '',
				);
			}

			$form_fields['order-id'] = array(
				'type'          => 'text',
				'value'         => '',
				'view_wrapping' => false,
				'placeholder'   => esc_html__( 'Order ID', 'tm-dashboard' ),
				'class'         => '',
				'label'         => '',
			);
			$form_fields[ 'verified-theme-' . $args['slug'] ] = array(
				'type'          => 'button',
				'style'         => 'primary',
				'view_wrapping' => false,
				'content'       => '<span class="text">' . esc_html__( 'Submit', 'tm-dashboard' ) . '</span>' . $this->button_element,
				'form'          => $args['slug'],
				'child_class'   => 'updater-theme-button utb-js verified-theme',
			);
			$form_hidden_fields['theme-slug'] = array(
				'view_wrapping' => false,
				'html'          => '<input type="hidden" name="slug" value="' . $args['slug'] . '">',
			);

			$this->builder->reset_structure();

			$this->builder->register_html( $notice );
			$this->builder->register_control( $form_fields );
			$this->builder->register_html( $form_hidden_fields );

			return $this->builder->render( false );
		}
	}
}
