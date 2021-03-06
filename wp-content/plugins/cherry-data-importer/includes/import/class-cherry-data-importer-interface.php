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

if ( ! class_exists( 'Cherry_Data_Importer_Interface' ) ) {

	/**
	 * Define Cherry_Data_Importer_Interface class
	 */
	class Cherry_Data_Importer_Interface {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Variable for settings array.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * Importer instance
		 *
		 * @var object
		 */
		private $importer = null;

		/**
		 * Returns XML-files count
		 *
		 * @var int
		 */
		private $xml_count = null;

		/**
		 * Importer slug
		 *
		 * @var string
		 */
		public $slug = 'import';

		/**
		 * Data storage.
		 *
		 * @var array
		 */
		public $data = array();

		/**
		 * Constructor for the class
		 */
		function __construct() {

			add_action( 'admin_menu', array( $this, 'menu_page' ) );
			add_action( 'wp_ajax_cherry-data-import-chunk', array( $this, 'import_chunk' ) );
			add_action( 'wp_ajax_cherry-regenerate-thumbnails', array( $this, 'regenerate_chunk' ) );
			add_action( 'wp_ajax_cherry-data-import-get-file-path', array( $this, 'get_file_path' ) );
			add_action( 'wp_ajax_cherry-data-import-remove-content', array( $this, 'remove_content' ) );
			add_action( 'cherry_data_importer_before_messages', array( $this, 'check_server_params' ) );
			add_action( 'admin_footer', array( $this, 'advanced_popup' ) );
			add_action( 'init', array( $this, 'maybe_skip_installation' ), 20 );
		}

		/**
		 * Maybe skip demo content installation
		 *
		 * @return bool|void
		 */
		public function maybe_skip_installation() {

			if ( ! isset( $_GET['page'] ) || cdi()->slug !== $_GET['page'] ) {
				return false;
			}

			if ( ! isset( $_GET['step'] ) || '2' !== $_GET['step'] ) {
				return false;
			}

			if ( isset( $_GET['type'] ) && 'skip' === $_GET['type'] ) {

				require_once cdi()->path( 'includes/import/class-cherry-data-importer-extensions.php' );

				/**
				 * Hook before redirect on demo content installation skip
				 */
				do_action( 'cherry_data_import_before_skip_redirect' );

				wp_redirect( cdi()->page_url( array( 'step' => 4 ) ) );
				die();
			}

			return false;
		}

		/**
		 * Check user password before content replacing.
		 *
		 * @return void
		 */
		public function remove_content() {

			$this->validate_request();

			if ( empty( $_REQUEST['password'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Password is empty', 'cherry-data-importer' ),
				) );
			}

			$password = esc_attr( $_REQUEST['password'] );
			$user_id  = get_current_user_id();
			$data     = get_userdata( $user_id );

			if ( wp_check_password( $password, $data->user_pass, $user_id ) ) {
				cdi_tools()->clear_content();
				wp_send_json_success( array(
					'message' => esc_html__( 'Content successfully removed', 'cherry-data-importer' ),
					'slider'  => cdi_slider()->render( false ),
				) );
			} else {
				wp_send_json_error( array(
					'message' => esc_html__( 'Entered password is invalid', 'cherry-data-importer' ),
				) );
			}

		}

		/**
		 * PopUp installation
		 */
		public function advanced_popup() {

			if ( ! $this->is_advanced_import() ) {
				return;
			}

			if ( ! isset( $_GET['tab'] ) || 'import' !== $_GET['tab'] ) {
				return;
			}

			cdi()->get_template( 'advanced-popup.php' );
		}

		/**
		 * Show content install type after plugins installation finished by Wizard.
		 *
		 * @return void
		 */
		public function wizard_popup() {

			if ( ! isset( $_GET['step'] ) || 2 !== intval( $_GET['step'] ) ) {
				return;
			}

			cdi()->get_template( 'advanced-popup.php' );
		}

		/**
		 * Check server params and show warning message if some of them don't meet requirements
		 *
		 * @return void
		 */
		public function check_server_params() {

			$messages = '';
			$format   = esc_html__( '%1$s: %2$s required, yours - %3$s', 'cherry-data-importer' );

			foreach ( cdi_tools()->server_params() as $param => $data ) {
				$val = ini_get( $param );
				$val = (int) $val;

				if ( $val < $data['value'] ) {
					$current = sprintf(
						$format,
						$param,
						'<strong>' . $data['value'] . $data['units'] . '</strong>',
						'<strong>' . $val . $data['units'] . '</strong>'
					);

					$messages .= '<div>' . $current . '</div>';
				}

			}

			if ( empty( $messages ) ) {
				return;
			}

			$heading = '<div class="cdi-server-messages__heading">' . esc_html__( 'Some parameters from your server don\'t meet the requirements:' ) . '</div>';

			echo '<div class="cdi-server-messages">' . $heading . $messages . '</div>';
		}

		/**
		 * Returns current chunk size
		 *
		 * @return void
		 */
		public function chunk_size() {

			$size = cdi()->get_setting( array( 'import', 'chunk_size' ) );
			$size = intval( $size );

			if ( ! $size ) {
				return cdi()->chunk_size;
			} else {
				return $size;
			}

		}

		/**
		 * Init importer
		 *
		 * @return void
		 */
		public function menu_page() {

			cdi()->register_tab(
				array(
					'id'   => $this->slug,
					'name' => esc_html__( 'Import', 'cherry-data-importer' ),
					'cb'   => array( $this, 'dispatch' ),
				)
			);

		}

		/**
		 * Run Cherry importer
		 *
		 * @return void
		 */
		public function dispatch() {

			$step = ! empty( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;

			ob_start();
			cdi_tools()->get_page_title( '<h2 class="page-title">', '</h2>', true );

			wp_enqueue_script( 'cherry-data-import' );

			switch ( $step ) {
				case 2:
					$this->import_step();
					break;

				case 3:
					$this->regenerate_thumbnails();
					break;

				case 4:
					$this->import_after();
					break;

				default:
					$this->import_before();
					break;
			}

			return ob_get_clean();
		}

		/**
		 * First import step
		 *
		 * @return void
		 */
		private function import_before() {
			cdi()->get_template( 'import-before.php' );
		}

		/**
		 * Last import step
		 *
		 * @return void
		 */
		private function import_after() {
			cdi()->get_template( 'import-after.php' );
		}

		/**
		 * Show main content import step
		 *
		 * @return void
		 */
		private function import_step() {

			if ( empty( $_GET['file'] ) || 'null' === $_GET['file'] ) {
				wp_redirect( add_query_arg(
					array(
						'page' => cdi()->slug,
						'tab'  => $this->slug,
						'step' => 1,
					),
					esc_url( admin_url( 'admin.php' ) )
				) );
				die();
			}

			$importer = $this->get_importer();
			$importer->prepare_import();

			$count        = cdi_cache()->get( 'total_count' );
			$chunks_count = ceil( intval( $count ) / $this->chunk_size() );

			// Adds final step with ID and URL remapping. Sometimes it's expensice step separate it
			$chunks_count++;

			cdi_cache()->update( 'chunks_count', $chunks_count );

			cdi()->get_template( 'import.php' );

		}

		/**
		 * Process regenerate thumbnails step.
		 *
		 * @return void
		 */
		public function regenerate_thumbnails() {

			$count = wp_count_attachments();
			$count = (array) $count;
			$step  = cdi()->get_setting( array( 'import', 'regenerate_chunk_size' ) );
			$total = 0;

			foreach ( $count as $mime => $num ) {
				if ( false === strpos( $mime, 'image' ) ) {
					continue;
				}
				$total = $total + (int) $num;
			}

			wp_localize_script( 'cherry-data-import', 'CherryRegenerateData', array(
				'totalImg'   => $total,
				'totalSteps' => ceil( $total / $step ),
				'step'       => $step,
			) );

			cdi()->get_template( 'regenerate.php' );

		}

		/**
		 * Returns true if regenerate thumbnails step is required, false - if not.
		 *
		 * @return boolean
		 */
		private function is_regenerate_required() {

			$count = wp_count_attachments();
			$count = (array) $count;

			if ( empty( $count ) ) {
				return false;
			}

			if ( 0 === $total ) {
				return false;
			}

			return true;
		}

		/**
		 * Validate import-related ajax request.
		 *
		 * @return void
		 */
		private function validate_request() {

			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'cherry-data-import' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You don\'t have permissions to do this', 'cherry-data-importer' ),
				) );
			}

			if ( ! current_user_can( 'import' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You don\'t have permissions to do this', 'cherry-data-importer' ),
				) );
			}

		}

		/**
		 * Process single regenerate chunk
		 *
		 * @return void
		 */
		public function regenerate_chunk() {

			$this->validate_request();

			$required = array(
				'offset',
				'step',
				'total',
			);

			foreach ( $required as $field ) {

				if ( ! isset( $_REQUEST[ $field ] ) ) {
					wp_send_json_error( array(
						'message' => sprintf(
							esc_html__( '%s is missing in request', 'cherry-data-importer' ), $field
						),
					) );
				}

			}

			$offset  = (int) $_REQUEST['offset'];
			$step    = (int) $_REQUEST['step'];
			$total   = (int) $_REQUEST['total'];
			$is_last = ( $total * $step <= $offset + $step ) ? true : false;

			$attachments = get_posts( array(
				'post_type'   => 'attachment',
				'numberposts' => $step,
				'offset'      => $offset,
			) );

			if ( ! empty( $attachments ) ) {
				foreach ( $attachments as $attachment ) {

					$id       = $attachment->ID;
					$file     = get_attached_file( $id );
					$metadata = wp_generate_attachment_metadata( $id, $file );

					wp_update_attachment_metadata( $id, $metadata );
				}
			}

			$data = array(
				'action'   => 'cherry-regenerate-thumbnails',
				'offset'   => $offset + $step,
				'step'     => $step,
				'total'    => $total,
				'isLast'   => $is_last,
				'complete' => round( ( $offset + $step ) * 100 / ( $total * $step ) ),
			);

			if ( $is_last ) {
				$data['redirect'] = cdi()->page_url( array( 'tab' => $this->slug, 'step' => 4 ) );
			}

			wp_send_json_success( $data );
		}

		/**
		 * Process single chunk import
		 *
		 * @return void
		 */
		public function import_chunk() {

			$this->validate_request();

			if ( empty( $_REQUEST['chunk'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Chunk number is missing in request', 'cherry-data-importer' ),
				) );
			}

			$chunk     = intval( $_REQUEST['chunk'] );
			$chunks    = cdi_cache()->get( 'chunks_count' );
			$processed = cdi_cache()->get( 'processed_summary' );

			require_once cdi()->path( 'includes/import/class-cherry-data-importer-extensions.php' );

			switch ( $chunk ) {

				case $chunks:

					// Process last step (remapping and finalizing)
					$this->remap_all();
					cdi_cache()->clear_cache();
					flush_rewrite_rules();

					$redirect = cdi()->page_url(
						array(
							'tab'  => $this->slug,
							'step' => $this->is_regenerate_required() ? 3 : 4,
						)
					);

					/**
					 * Hook on last import chunk
					 */
					do_action( 'cherry_data_import_finish' );

					$data = array(
						'isLast'    => true,
						'complete'  => 100,
						'processed' => $processed,
						'redirect'  => $redirect,
					);

					break;

				default:

					// Process regular step
					$offset   = $this->chunk_size() * ( $chunk - 1 );
					$importer = $this->get_importer();

					$importer->chunked_import( $this->chunk_size(), $offset );

					/**
					 * Hook on last import chunk
					 */
					do_action( 'cherry_data_import_chunk', $chunk );

					$data = array(
						'action'    => 'cherry-data-import-chunk',
						'chunk'     => $chunk + 1,
						'complete'  => round( ( $chunk * 100 ) / $chunks ),
						'processed' => $processed,
					);

					break;
			}

			wp_send_json_success( $data );
		}

		/**
		 * Return importer object
		 *
		 * @return object
		 */
		public function get_importer() {

			if ( null !== $this->importer ) {
				return $this->importer;
			}

			require_once cdi()->path( 'includes/import/class-cherry-wxr-importer.php' );

			$options = array();
			$file    = null;

			if ( isset( $_REQUEST['file'] ) ) {
				$file = cdi_tools()->esc_path( esc_attr( $_REQUEST['file'] ) );
			}

			if ( ! $file || ! file_exists( $file ) ) {
				$file = cdi()->get_setting( array( 'xml', 'path' ) );
			}

			if ( is_array( $file ) ) {
				$file = $file[0];
			}

			return $this->importer = new Cherry_WXR_Importer( $options, $file );
		}

		/**
		 * Remap all required data after installation completed
		 *
		 * @return void
		 */
		public function remap_all() {

			require_once cdi()->path( 'includes/import/class-cherry-data-importer-remap-callbacks.php' );

			/**
			 * Attach all posts remapping related callbacks to this hook
			 *
			 * @param  array Posts remapping data. Format: old_id => new_id
			 */
			do_action( 'cherry_data_import_remap_posts', cdi_cache()->get( 'posts', 'mapping' ) );

			/**
			 * Attach all terms remapping related callbacks to this hook
			 *
			 * @param  array Terms remapping data. Format: old_id => new_id
			 */
			do_action( 'cherry_data_import_remap_terms', cdi_cache()->get( 'term_id', 'mapping' ) );

			/**
			 * Attach all comments remapping related callbacks to this hook
			 *
			 * @param  array COmments remapping data. Format: old_id => new_id
			 */
			do_action( 'cherry_data_import_remap_comments', cdi_cache()->get( 'comments', 'mapping' ) );

			/**
			 * Attach all posts_meta remapping related callbacks to this hook
			 *
			 * @param  array posts_meta data. Format: new_id => related keys array
			 */
			do_action( 'cherry_data_import_remap_posts_meta', cdi_cache()->get( 'posts_meta', 'requires_remapping' ) );

			/**
			 * Attach all terms meta remapping related callbacks to this hook
			 *
			 * @param  array terms meta data. Format: new_id => related keys array
			 */
			do_action( 'cherry_data_import_remap_terms_meta', cdi_cache()->get( 'terms_meta', 'requires_remapping' ) );

		}

		/**
		 * Get welcome message for importer starter page
		 *
		 * @return string
		 */
		public function get_welcome_message() {

			$files = $this->get_xml_count();

			if ( 0 === $files ) {
				$message = __( 'Upload XML file with demo content', 'cherry-data-importer' );
			}

			if ( 1 === $files ) {
				$message = __( 'We found 1 XML file with demo content in your theme, install it?', 'cherry-data-importer' );
			}

			if ( 1 < $files ) {
				$message = sprintf(
					__( 'We found %s XML files in your theme. Please select one of them to install', 'cherry-data-importer' ),
					$files
				);
			}

			return '<div class="cdi-message">' . $message . '</div>';

		}

		/**
		 * Get available XML count
		 *
		 * @return int
		 */
		public function get_xml_count() {

			if ( null !== $this->xml_count ) {
				return $this->xml_count;
			}

			$files = cdi()->get_setting( array( 'xml', 'path' ) );

			if ( ! $files ) {
				$this->xml_count = 0;
			} elseif ( ! is_array( $files ) ) {
				$this->xml_count = 1;
			} else {
				$this->xml_count = count( $files );
			}

			return $this->xml_count;
		}

		/**
		 * Returns HTML-markup of import files select
		 *
		 * @return string
		 */
		public function get_import_files_select( $before = '<div>', $after = '</div>' ) {

			$files = cdi()->get_setting( array( 'xml', 'path' ) );

			if ( ! $files && ! is_array( $files ) ) {
				return;
			}

			if ( 1 >= count( $files ) ) {
				return;
			}

			$wrap_format = '<select name="import_file">%1$s</select>';
			$item_format = '<option value="%1$s" %3$s>%2$s</option>';
			$selected    = 'selected="selected"';

			$result = '';

			foreach ( $files as $name => $file ) {
				$result .= sprintf( $item_format, cdi_tools()->secure_path( $file ), $name, $selected );
				$selected = '';
			}

			return $before . sprintf( $wrap_format, $result ) . $after;

		}

		/**
		 * Retuns HTML markup for import file uploader
		 *
		 * @param  string $before HTML markup before input.
		 * @param  string $after  HTML markup after input.
		 * @return string
		 */
		public function get_import_file_input( $before = '<div>', $after = '</div>' ) {

			if ( ! cdi()->get_setting( array( 'xml', 'use_upload' ) ) ) {
				return;
			}

			$result = '<div class="import-file">';
			$result .= '<input type="hidden" name="upload_file" class="import-file__input">';
			$result .= '<input type="text" name="upload_file_nicename" class="import-file__placeholder">';
			$result .= '<button class="cdi-btn primary" id="cherry-file-upload">';
			$result .= esc_html__( 'Upload File', 'cherry-data-importer' );
			$result .= '</button>';

			$result .= '</div>';

			return $before . $result . $after;

		}

		/**
		 * Check if advanced import is allowed
		 *
		 * @since  1.1.0
		 * @return boolean
		 */
		public function is_advanced_import() {
			$advanced = cdi()->get_setting( array( 'advanced_import' ) );
			return ! empty( $advanced );
		}

		/**
		 * Show advanced import block.
		 *
		 * @since  1.1.0
		 * @return null
		 */
		public function advanced_import() {

			if ( ! $this->is_advanced_import() ) {
				return;
			}

			$advanced = cdi()->get_setting( array( 'advanced_import' ) );

			foreach ( $advanced as $slug => $item ) {
				$this->data['advanced-item'] = $item;
				$this->data['advanced-slug'] = $slug;
				cdi()->get_template( 'import-advanced.php' );
			}

		}

		/**
		 * Show password form if is replace installation type.
		 *
		 * @since  1.1.0
		 * @return null
		 */
		public function remove_content_form() {

			if ( ! isset( $_GET['type'] ) || 'replace' !== $_GET['type'] ) {
				return;
			}

			if ( ! current_user_can( 'delete_users' ) ) {
				esc_html_e(
					'You don\'t have permissions to replace content, please re-enter with admiistrator account',
					'cherry-data-importer'
				);
				return;
			}

			cdi()->get_template( 'remove-content-form.php' );

		}

		/**
		 * Retrieve XML file path by URL
		 *
		 * @return string
		 */
		public function get_file_path() {

			$this->validate_request();

			if ( ! isset( $_REQUEST['file'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'XML file not passed', 'cherry-data-importer' ),
				) );
			}

			$path = str_replace( home_url( '/' ), ABSPATH, esc_url( $_REQUEST['file'] ) );

			wp_send_json_success( array(
				'path' => cdi_tools()->secure_path( $path ),
			) );

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
 * Returns instance of Cherry_Data_Importer_Interface
 *
 * @return object
 */
function cdi_interface() {
	return Cherry_Data_Importer_Interface::get_instance();
}
