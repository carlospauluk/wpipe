<?php
/**
 * BuddyPress - Groups Cover Image Header.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires before the display of a group's header.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_group_header' ); ?>
<a id="header-cover-image" href="<?php echo esc_url( bp_get_group_permalink() ); ?>"></a>

<div id="cover-image-container">
	<div id="item-header-cover-image">
		<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
		<?php endif; ?>

		<div id="item-header-content">

			<div id="item-buttons"><?php

				/**
				 * Fires in the group header actions section.
				 *
				 * @since 1.2.6
				 */
				do_action( 'bp_group_header_actions' ); ?></div><!-- #item-buttons -->

			<?php

			/**
			 * Fires before the display of the group's header meta.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_before_group_header_meta' ); ?>

			<div id="item-meta">
				<?php

				/**
				 * Fires after the group header actions section.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_group_header_meta' ); ?>

				<span class="highlight"><?php bp_group_type(); ?></span>
				<span class="activity"><?php printf( esc_html__( 'active ', 'monstroid2' ) . '%s', bp_get_group_last_active() ); ?></span>

				<?php bp_group_description(); ?>

			</div>
		</div><!-- #item-header-content -->

		<div id="item-actions">
			<?php if ( bp_group_is_visible() ) : ?>

				<h6><?php esc_html_e( 'Group Admins', 'monstroid2' ); ?></h6>

				<?php bp_group_list_admins();

				/**
				 * Fires after the display of the group's administrators.
				 *
				 * @since 1.1.0
				 */
				do_action( 'bp_after_group_menu_admins' );

				if ( bp_group_has_moderators() ) :

					/**
					 * Fires before the display of the group's moderators, if there are any.
					 *
					 * @since 1.1.0
					 */
					do_action( 'bp_before_group_menu_mods' ); ?>

					<h3><?php esc_html_e( 'Group Mods' , 'monstroid2' ); ?></h3>
					<?php bp_group_list_mods();
					/**
					 * Fires after the display of the group's moderators, if there are any.
					 *
					 * @since 1.1.0
					 */
					do_action( 'bp_after_group_menu_mods' );

				endif;

			endif; ?>

		</div><!-- #item-actions -->

	</div><!-- #item-header-cover-image -->
</div><!-- #cover-image-container -->

<?php

/**
 * Fires after the display of a group's header.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_group_header' );

/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
do_action( 'template_notices' ); ?>
