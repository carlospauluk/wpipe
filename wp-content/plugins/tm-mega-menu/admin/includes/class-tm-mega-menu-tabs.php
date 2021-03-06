<?php
/**
 * Define default mega menu item tabs callbacks
 *
 * @package   tm_mega_menu
 * @author    TemplateMonster
 * @license   GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}

if ( ! class_exists( 'tm_mega_menu_item_tabs' ) ) {

	/**
	 * Add default tabs callbcks via method of this class
	 */
	class tm_mega_menu_item_tabs {

		/**
		 * Get mega menu tab content
		 *
		 * @since  1.0.0
		 *
		 * @param  int    $id    menu item ID
		 * @param  string $title menu item title
		 * @param  int    $depth menu item depth
		 * @param  array  $meta  menu item meta
		 * @return string        tab HTML
		 */
		public function mega_menu( $id, $title, $depth, $meta ) {

			if ( 0 < $depth ) {
				return '<em>' . __( 'Mega Menus can only be created on top level menu items.', 'tm-mega-menu' ) . '</em>';
			}

			global $tm_mega_menu_total_columns;

			$widget_manager = new tm_mega_menu_widget_manager();

			$all_widgets = $widget_manager->get_available_widgets();

			$meta[ 'type' ] = isset( $meta[ 'type' ] ) ? $meta[ 'type' ] : '';

			$return = '<label class="menu_enable"><input class="toggle_menu" type="checkbox" name="type" value="megamenu" ' . checked( $meta[ 'type' ], 'megamenu', false )  . '>' . __( 'Enable Mega Menu for current item', 'tm-mega-menu' ) . '</label><select id="widget_selector"><option value="disabled">' . __( 'Select a Widget to add to the panel', 'tm-mega-menu' ) . '</option>';

			foreach ( $all_widgets as $widget ) {
				$return .= '<option value="' . $widget[ 'value' ] . '">' . $widget[ 'text' ] . '</option>';
			}

			$return .= '</select><div id="widgets">';

			$panel_widgets = $widget_manager->get_widgets_for_menu_id( $id );

			if ( ! count( $panel_widgets ) ) {

				$return .= '<div class="message no_widgets">' . __( 'No widgets found', 'tm-mega-menu' ) . '<br><br><i>' . __( 'Use the Widget Selector (top right) to add a Widget to this panel.', 'tm-mega-menu' ) . '</i></div>';

			} else {

				foreach ( $panel_widgets as $widget ) {
					$return .= $widget_manager->get_widget_html( $widget[ 'title' ], $widget[ 'widget_id' ], $widget[ 'mega_columns' ], $tm_mega_menu_total_columns );
				}

			}

			$return .= '</div>';

			return $return;
		}

		/**
		 * Get settings tab content
		 *
		 * @since  1.0.0
		 *
		 * @param  int    $id    menu item ID
		 * @param  string $title menu item title
		 * @param  int    $depth menu item depth
		 * @param  array  $meta  menu item meta
		 * @return string        tab HTML
		 */
		public function settings( $id, $title, $depth, $meta ) {

			$default_fields = array(
				'subitems' => array(
					'name' => __( 'Subitems behavior', 'tm-mega-menu' ),
					'desc' => __( 'Standard subitems behavior', 'tm-mega-menu' ),
					'type' => 'heading',
					'std'  => ''
				),
				'sub-items-to-cols' => array(
					'name' => __( 'Group sub items to columns', 'tm-mega-menu' ),
					'desc' => __( 'Group standard submenu items to columns', 'tm-mega-menu' ),
					'type' => 'checkbox',
					'std'  => ''
				),
				'sub-cols-num' => array(
					'name'    => __( 'Container Columns number', 'tm-mega-menu' ),
					'desc'    => __( 'Submenu container columns number', 'tm-mega-menu' ),
					'type'    => 'select',
					'std'     => '4',
					'options' => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'6' => '6',
					)
				),
				'item-layout' => array(
					'name' => __( 'Layout and position', 'tm-mega-menu' ),
					'desc' => __( 'Define layout and position options for current mega menu item', 'tm-mega-menu' ),
					'type' => 'heading',
					'std'  => ''
				),
				'item-submenu-position' => array(
					'name'    => __( 'Submenu position', 'tm-mega-menu' ),
					'desc'    => __( 'Select submenu position for current menu item', 'tm-mega-menu' ),
					'type'    => 'radio',
					'std'     => 'fullwidth',
					'options' => array(
						'fullwidth'        => __( 'Full Width', 'tm-mega-menu' ),
						'left-parent'      => __( 'Left Edge of Parent Item', 'tm-mega-menu' ),
					)
				),
				'item-width-fullscreen' => array(
					'name' => __( 'Set item width for 1200', 'tm-mega-menu' ),
					'desc' => __( 'Set item width for and more (100%, 400px)', 'tm-mega-menu' ),
					'type' => 'text',
					'std'  => '100%'
				),
				'item-width-desktop' => array(
					'name' => __( 'Set item width for 980-1199', 'tm-mega-menu' ),
					'desc' => __( 'Set item width for and more (100%, 400px)', 'tm-mega-menu' ),
					'type' => 'text',
					'std'  => '100%'
				),
				'item-width-tablet' => array(
					'name' => __( 'Set item width for 768-979', 'tm-mega-menu' ),
					'desc' => __( 'Set item width for and more (100%, 400px)', 'tm-mega-menu' ),
					'type' => 'text',
					'std'  => '100%'
				),
				'item-hide-mobile' => array(
					'name' => __( 'Hide submenu on mobile', 'tm-mega-menu' ),
					'desc' => __( 'Hide this submenu item on mobile version', 'tm-mega-menu' ),
					'type' => 'checkbox',
					'std'  => ''
				)
			);

			$fields = apply_filters( 'tm_mega_menu_settings_tab_fields', $default_fields );

			$return = $this->_build_tab_fields( $fields, $meta, $id, $depth );

			return $return;
		}

		/**
		 * Get media tab content
		 *
		 * @since  1.0.0
		 *
		 * @param  int    $id    menu item ID
		 * @param  string $title menu item title
		 * @param  int    $depth menu item depth
		 * @param  array  $meta  menu item meta
		 * @return string        tab HTML
		 */
		public function media( $id, $title, $depth, $meta ) {

			$default_fields = array(
				'item-icon' => array(
					'name'  => __( 'Item icon', 'tm-mega-menu' ),
					'desc'  => __( 'Select FontAwesome item icon', 'tm-mega-menu' ),
					'type'  => 'icon-picker',
					'depth' => 10,
					'std'   => ''
				),
				'item-arrow' => array(
					'name'  => __( 'Item arrow', 'tm-mega-menu' ),
					'desc'  => __( 'Select FontAwesome icon for arrow', 'tm-mega-menu' ),
					'type'  => 'icon-picker',
					'depth' => 10,
					'std'   => ''
				),
				'item-hide-text' => array(
					'name' => __( 'Hide menu link text', 'tm-mega-menu' ),
					'desc' => __( 'Hide this menu item link text', 'tm-mega-menu' ),
					'type' => 'checkbox',
					'std'  => ''
				),
				'item-hide-arrow' => array(
					'name' => __( 'Hide sub-menu arrow', 'tm-mega-menu' ),
					'desc' => __( 'Hide sub-menu indicator arrow', 'tm-mega-menu' ),
					'type' => 'checkbox',
					'std'  => ''
				)
			);
			$fields = apply_filters( 'tm_mega_menu_media_tab_fields', $default_fields );

			$return = $this->_build_tab_fields( $fields, $meta, $id, $depth );

			return $return;
		}

		/**
		 * Build tab fields set
		 *
		 * @since  1.0.0
		 *
		 * @param  array  $fields  fields definition array
		 * @param  array  $meta    existing menu meta array
		 * @param  int    $id      current menu item ID
		 * @param  int    $depth   current menu item depth
		 * @return html            settings markup
		 */
		private function _build_tab_fields( $fields, $meta, $id, $depth ) {

			$return = '';

			foreach ( $fields as $field_id => $field ) {

				$field = wp_parse_args( $field, array(
					'name'    => '',
					'desc'    => '',
					'type'    => '',
					'std'     => '',
					'depth'   => 10,
					'options' => array()
				) );
				if ( $depth > $field[ 'depth' ] || !$field[ 'type' ] ) {
					continue;
				}
				$this_meta = isset( $meta[$field_id] ) ? $meta[$field_id] : $field[ 'std' ];

				if ( 'heading' == $field[ 'type' ] ) {
					$return .= '<div class="field-settings-row_"><h3>' . $field[ 'name' ] . '</h3><span class="row-desc_">' . $field[ 'desc' ] . '</span></div>';
				} elseif ( 'submit' == $field[ 'type' ] ) {
					$return .= '<div class="save-settings-wrap_"><a class="button-primary_ ' . esc_attr( $field_id ) . '" href="#">' . $field[ 'name' ] . '</a></div>';
				} else {
					$return .= '<div class="field-settings-row_"><div class="row-heading_"><label for="' . $field_id  . '">' . $field[ 'name' ] . '</label><span class="row-desc_">' . $field[ 'desc' ] . '</span></div><div class="row-control_">' . $this->_field_control( $field_id, $field, $this_meta, $id ) . '</div></div>';
				}
				unset( $field );
			}
			return $return;
		}

		/**
		 * Get single field cotrol
		 *
		 * @since  1.0.0
		 *
		 * @param  int    $field_id current field ID
		 * @param  array  $field    field params array
		 * @param  mixed  $meta     current menu meta val
		 * @param  int    $id       current menu item ID
		 * @return html             settings markup
		 */
		private function _field_control( $field_id, $field, $meta, $id ) {

			$return = '';

			switch ( $field[ 'type' ] ) {
				case 'text':
					$return = '<input type="text" name="' . $field_id . '" id="' . $field_id . '" value="' . $meta . '">';
					break;

				case 'checkbox':
					$return = '<input type="checkbox" name="' . $field_id . '" id="' . $field_id . '" value="' . $field_id . '" ' . checked( $meta, $field_id, false ) . '>';
					break;

				case 'icon-picker':
					$return  = '<div class="input-group iconpicker-container"><span class="input-group-addon"></span><input type="text" data-init-script="icon-picker" data-placement="bottomLeft" name="' . $field_id . '" id="' . $field_id . '" value="' . $meta . '"></div>';
					break;

				case 'radio':
					if ( !is_array( $field[ 'options' ] ) ) {
						$return = '';
						break;
					}

					$return = '<div class="radio-group_">';
					foreach ( $field[ 'options' ] as $value => $label ) {
						$return .= '<label for="' . $field_id . '-' . str_replace( ' ', '-', $value ) . '"><input type="radio" name="' . $field_id . '" id="' . $field_id . '-' . str_replace( ' ', '-', $value ) . '" value="' . $value . '" ' . checked( $meta, $value, false ) . '> ' . $label . '</label>';
					}
					$return .= '</div>';
					break;

				case 'select':
					$return = '<select name="' . $field_id . '" id="' . $field_id . '">';
						foreach ( $field[ 'options' ] as $val => $label ) {
							$return .= '<option value="' . $val . '" ' . selected( $meta, $val, false ) . '>' . $label . '</option>';
						}
					$return .= '</select>';
					break;

				default:
					ob_start();
					do_action( 'tm_mega_menu_custom_tab_field_controls', $field_id, $field, $meta, $id );
					$return = ob_get_clean();
					break;
			}
			return $return;
		}
	}
}