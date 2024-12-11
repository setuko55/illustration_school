<?php
/**
 * フロントページテンプレート
 */
get_header(); 
require_once get_template_directory() . '/template-parts/view/contentView.php';
?>

<main <?php Arkhe::main_attrs(); ?>>
	<div <?php Arkhe::main_body_attrs(); ?>>
		<?php
			do_action( 'arkhe_start_front_main' );

			if ( is_home() ) :
				do_action( 'arkhe_before_home_content' );
				Arkhe::get_part( 'home' );
				do_action( 'arkhe_after_home_content' );
			else :
				do_action( 'arkhe_before_front_content' );
				while ( have_posts() ) :
					the_post();
					Arkhe::get_part( 'front' );
				endwhile;
				do_action( 'arkhe_after_front_content' );
			endif;

			do_action( 'arkhe_end_front_main' );
		?>

		<!-- お知らせ表示 -->
		<div class="top-image-area">
			<img class="top-image" src="<?= get_stylesheet_directory_uri() .'/assets/img/kousya.png'?>" alt="">
		</diV>
		<div class="top-content-area-back">
			<h2 class="top-h2">お知らせ一覧</h2>
			<div class="top-content-area">
				<?php
				$args = array(
					'post_type' => 'notice', 	//カスタム投稿タイプ名
					'posts_per_page' => 3 		//取得する投稿の件数
				);

				$my_query = new WP_Query( $args );

				//コンテンツの表示
				contentView('notice', $my_query); ?>
			</div>
		</div>
	

		<!-- 授業表示 -->
		<div class="top-content-area-back">
			<h2 class="top-h2">授業一覧</h2>
			<div class="top-content-area">
				<?php
				$args = array(
					'post_type' => 'lesson', 	//カスタム投稿タイプ名
					'posts_per_page' => 4 		//取得する投稿の件数
				);

				$my_query = new WP_Query( $args );
				//コンテンツの表示
				contentView('nomal', $my_query);
				?>
				
			</div>
		</div>
		
	</div>
</main>
<?php
get_footer();
