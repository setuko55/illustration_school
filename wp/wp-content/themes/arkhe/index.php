<?php
/**
 * index.php
 */
get_header(); ?>
<main <?php Arkhe::main_attrs(); ?>>
	<!-- TOP画像表示 -->
	<div class="top-first-area">
		<img src="<?= get_stylesheet_directory_uri(); ?>/assets/img/top-blackboard.png" alt="" />
	</div>

	<div <?php Arkhe::main_body_attrs(); ?>>
	<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
			?>
				<div class="<?php Arkhe::post_content_class(); ?>">
					<?php the_content(); ?>
				<div>
			<?php
			endwhile;
		endif;
	?>
	</div>
</main>
<?php get_footer(); ?>
