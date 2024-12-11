<?php

/**
 * 記事の表示形式を設定
 * 
 * 
 */
function contentView(string $mode, $my_query){
    switch($mode){
        case 'notice':
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
                    <a class="content-title" href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                    <?php
                    //投稿時間 ?>
                    <p class="date-time">
                        <?php the_time( get_option( 'date_format' ) ); ?>
                    </p>
                </div>
            <?php endwhile;
            break;
        case 'nomal':
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
                    <a class="content-title" href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                    <?php
                    //投稿時間 ?>
                    <p class="date-time">
                        <?php the_time( get_option( 'date_format' ) ); ?>
                    </p>
                </div>
            <?php endwhile; ?>

            <?php wp_reset_postdata();
            break;
    } 
}
?>