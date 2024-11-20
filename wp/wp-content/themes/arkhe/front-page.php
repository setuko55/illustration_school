<?php
/**
 * フロントページテンプレート
 */
get_header(); ?>

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
		<div class="top-content-area-back">
			<h2 class="top-h2">お知らせ一覧</h2>
			<div class="top-content-area">
				<?php
				$args = array(
					'post_type' => 'notice', 	//カスタム投稿タイプ名
					'posts_per_page' => 3 		//取得する投稿の件数
				);

				$my_query = new WP_Query( $args );
				?>

				<?php 
				//マグネット番号用変数
				$magnum = 0;
				while ( $my_query->have_posts() ) : $my_query->the_post(); ?>
					<div class="top-content" id="notices">
						<?php 
						//画像表示
						if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="notice-link">
								<!-- マグネット表示 -->
								<div class="magnet-area">
									<?php
									echo '<img class="magnet-'. $magnum .'" src="'. get_stylesheet_directory_uri() .'/assets/img/magnet_'.$magnum.'.png'.'" />';
									$magnum++;
									echo '<img class="magnet-'. $magnum .'" src="'. get_stylesheet_directory_uri() .'/assets/img/magnet_'.$magnum.'.png'.'" />';
									$magnum++;
									the_post_thumbnail( 'thumbnail' ); 
									?>
								</div>
							</a>
						<?php 
						endif; ?>
						<?php 
						//お知らせタイトル ?>
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
						<?php
						//投稿時間 ?>
						<p>
							<?php the_time( get_option( 'date_format' ) ); ?>
						</p>
					</div>
				<?php endwhile; ?>

				<?php wp_reset_postdata(); ?>
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
				?>

				<?php 
				//マグネット番号用変数
				$magnum = 0;
				while ( $my_query->have_posts() ) : $my_query->the_post(); ?>
					<div class="top-content" id="lesson">
						<?php 
						//画像表示
						if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>">
								<!-- マグネット表示 -->
								<div class="magnet-area">
									<?php
									the_post_thumbnail( 'thumbnail' ); 
									?>
								</div>
							</a>
						<?php 
						endif; ?>
						<?php 
						//お知らせタイトル ?>
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
						<?php
						//投稿時間 ?>
						<p>
							<?php the_time( get_option( 'date_format' ) ); ?>
						</p>
					</div>
				<?php endwhile; ?>

				<?php wp_reset_postdata(); ?>
			</div>
		</div>
		
	</div>
</main>
<?php
get_footer();
