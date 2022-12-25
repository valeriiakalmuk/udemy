<?php
/**
 * Template with tabs of order statuses config.
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
	<?php else : ?>
		<a href="?page=p24-order-status"><?php echo esc_html( __( 'Ustawienia główne' ) ); ?></a>
	<?php endif; ?>

	<?php if ( 'list' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Lista' ) ); ?></span>
	<?php elseif ( $is_active ) : ?>
		<a href="?page=p24-order-status&tab=list"><?php echo esc_html( __( 'Lista' ) ); ?></a>
	<?php else : ?>
		<span class="inactive"><?php echo esc_html( __( 'Lista' ) ); ?></span>
	<?php endif; ?>
</nav>

<?php
