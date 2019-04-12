<?php
/**
 * Update/Install Plugin/Theme administration panel.
 *
 * @package    TM_Dashboard
 * @subpackage Views
 * @author     Cherry Team
 * @version    1.0.0
 * @license    GPL-3.0+
 * @copyright  2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<div class="tm-updates">
	<h1 class="tm-updates__title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<div class="cherry-ui-kit cherry-section">
		<?php
			//inclide files from admin/includes/tm-updates
			require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-themes-list.php' );

			$themes_list = new Tm_Themes_List();
			$themes_list->render();
		?>
	</div>
</div>
