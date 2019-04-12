<?php
/**
 * Plugin installer skin class.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}
// If class `TM_Update_Upgrader_Skin` doesn't exists yet.
if ( ! class_exists( 'TM_Update_Upgrader_Skin' ) ) {

	class TM_Update_Upgrader_Skin extends WP_Upgrader_Skin {

		/**
		 * Construtor for the class.
		 *
		 * @param array $args Options array.
		 */
		public function __construct( $args = array() ) {
			parent::__construct( $args );
		}

		/**
		 * Output markup after plugin installation processed.
		 */
		public function after() {}

		/**
		 *  Output header markup.
		 */
		public function header() {}

		/**
		 *  Output footer markup.
		 */
		public function footer() {}

		/**
		 *
		 * @param string $string
		 */
		public function feedback( $string ) {}

	}
}
