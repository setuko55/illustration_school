<?php
/**
 * ヘッダー用テンプレート
 */
$move_gnav_under = Arkhe::get_setting( 'move_gnav_under' );
$logo_pos        = $move_gnav_under ? 'center' : 'left';
?>
<header id="header" class="l-header" <?php Arkhe::header_attrs( array( 'logo_pos' => $logo_pos ) ); ?>>
	<?php /* Arkhe::get_part( 'header/header_bar' ); ?>
	<div class="l-header__body l-container">
		<?php Arkhe::get_part( 'header/btn/drawer' ); ?>
		<div class="l-header__left">
			<?php do_action( 'arkhe_header_left_content' ); ?>
		</div>
		<div class="l-header__center">
			<?php Arkhe::get_part( 'header/logo' ); ?>
		</div>
		<div class="l-header__right">
			<?php
				if ( ! $move_gnav_under ) :
					Arkhe::get_part( 'header/gnav' );
				endif;
				do_action( 'arkhe_header_right_content' );
			?>
		</div>
		<?php Arkhe::get_part( 'header/btn/search' ); ?>
		<?php Arkhe::get_part( 'header/drawer_menu' ); ?>
	</div>
	*/ ?>

	<div class="header-logo-area">
		<img src="<?= get_stylesheet_directory_uri(); ?>/assets/img/logo.png" />
	</div>
	<div class="header-link-area">
		<a href="">授業</a>
		<a href="">成長日記</a>
		<a href="">職員室</a>
		<a href="">図書室</a>
	</div>
	<div class="header-user-area">
		<img src="" />
	</div>
</header>
<?php if ( $move_gnav_under ) : ?>
	<div class="l-headerUnder" <?php if ( Arkhe::get_setting( 'fix_gnav' ) )  echo ' data-fix="1"'; ?>>
		<div class="l-headerUnder__inner l-container">
			<?php Arkhe::get_part( 'header/gnav' ); ?>
		</div>
	</div>
<?php endif; ?>
