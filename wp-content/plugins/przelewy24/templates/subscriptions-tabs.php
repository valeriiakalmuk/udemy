<?php
/**
 * Template with tabs of multi currency config.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $tab ) ) {
	throw new LogicException( 'The variable $tab is not set.' );
}

if ( ! isset( $is_active ) ) {
	throw new LogicException( 'The variable $is_active is not set.' );
}

?>

<nav class="p24-horizontal-tab-menu">

	<?php if ( 'main' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Ustawienia główne' ) ); ?></span>
	<?php elseif ( $is_active ) : ?>
		<a href="?page=p24-subscription"><?php echo esc_html( __( 'Ustawienia główne' ) ); ?></a>
	<?php else : ?>
		<span class="inactive"><?php echo esc_html( __( 'Ustawienia główne' ) ); ?></span>
	<?php endif; ?>

	<?php if ( 'list' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Aktywne' ) ); ?></span>
	<?php elseif ( $is_active ) : ?>
		<a href="?page=p24-subscription&tab=list"><?php echo esc_html( __( 'Aktywne' ) ); ?></a>
	<?php else : ?>
		<span class="inactive"><?php echo esc_html( __( 'Aktywne' ) ); ?></span>
	<?php endif; ?>

	<?php if ( 'inactive' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Nieaktywne' ) ); ?></span>
	<?php elseif ( $is_active ) : ?>
		<a href="?page=p24-subscription&tab=inactive"><?php echo esc_html( __( 'Nieaktywne' ) ); ?></a>
	<?php else : ?>
		<span class="inactive"><?php echo esc_html( __( 'Nieaktywne' ) ); ?></span>
	<?php endif; ?>
</nav>

<?php
