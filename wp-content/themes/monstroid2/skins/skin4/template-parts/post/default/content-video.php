<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'posts-list__item card' ); ?>>

	<?php $utility       = monstroid2_utility()->utility;
	$blog_featured_image = get_theme_mod( 'blog_featured_image', monstroid2_theme()->customizer->get_default( 'blog_featured_image' ) );
	$cats_visible        = monstroid2_is_meta_visible( 'blog_post_categories', 'loop' );
	?>

	<?php if ( 'small' === $blog_featured_image ) : ?>
		<div class="post-featured-content">
			<?php do_action( 'cherry_post_format_video', array( 'width' => 886, 'height' => 668 ) ); ?>
		</div><!-- .post-featured-content -->
	<?php endif; ?>

	<div class="post-list__item-content">
		<?php if ( 'small' !== $blog_featured_image ) : ?>
			<div class="post-featured-content">
				<?php do_action( 'cherry_post_format_video', array( 'width' => 886, 'height' => 668 ) ); ?>

				<?php $utility->meta_data->get_terms( array(
					'visible'   => $cats_visible,
					'type'      => 'category',
					'before'    => '<div class="post__cats">',
					'after'     => '</div>',
					'echo'      => true,
				) );
				?>
			</div><!-- .post-featured-content -->
		<?php endif; ?>

		<?php if ( 'small' === $blog_featured_image ) :
			$utility->meta_data->get_terms( array(
				'visible'   => $cats_visible,
				'type'      => 'category',
				'before'    => '<div class="post__cats">',
				'after'     => '</div>',
				'echo'      => true,
			) );
		endif; ?>

		<header class="entry-header">
			<?php monstroid2_sticky_label(); ?>

			<?php $title_html = ( is_single() ) ? '<h3 %1$s>%4$s</h3>' : '<h5 %1$s><a href="%2$s" rel="bookmark">%4$s</a></h5>';

			$utility->attributes->get_title( array(
				'class' => 'entry-title',
				'html'  => $title_html,
				'echo'  => true,
			) );
			?>
		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php $blog_content = get_theme_mod( 'blog_posts_content', monstroid2_theme()->customizer->get_default( 'blog_posts_content' ) );
			$length             = ( 'full' === $blog_content ) ? -1 : 55;
			$content_visible    = ( 'none' !== $blog_content ) ? true : false;
			$content_type       = ( 'full' !== $blog_content ) ? 'post_excerpt' : 'post_content';

			$utility->attributes->get_content( array(
				'visible'      => $content_visible,
				'length'       => $length,
				'content_type' => $content_type,
				'echo'         => true,
			) );
			?>
		</div><!-- .entry-content -->

		<?php get_template_part( 'skins/skin4/template-parts/content-entry-meta-loop' ); ?>

		<footer class="entry-footer">
			<?php $btn_text = get_theme_mod( 'blog_read_more_text', monstroid2_theme()->customizer->get_default( 'blog_read_more_text' ) );
			$btn_visible    = $btn_text ? true : false;

			$utility->attributes->get_button( array(
				'visible' => $btn_visible,
				'class'   => 'post-btn btn',
				'text'    => $btn_text,
				'html'    => '<a href="%1$s" %3$s>%4$s</a>',
				'echo'    => true,
			) );
			?>

			<?php monstroid2_share_buttons( 'loop' ); ?>
		</footer><!-- .entry-footer -->
	</div><!-- .post-list__item-content -->

</article><!-- #post-## -->
