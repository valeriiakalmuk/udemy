<?php
/**
 * Template for email about subscription cancelation.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $email_heading ) ) {
	throw new LogicException( 'The variable $email_heading is not set.' );
}

if ( ! isset( $email ) ) {
	throw new LogicException( 'The variable $email is not set.' );
}

if ( ! isset( $subscription_title ) ) {
	throw new LogicException( 'The variable $subscription_title is not set.' );
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php

$text_align = is_rtl() ? 'right' : 'left';

?>

	<h2>
		<?php echo esc_html( __( 'Anulowanie subskrypcji' ) ); ?>
		<?php echo wp_kses_post( $subscription_title ); ?>
	</h2>

	<div style="margin-bottom: 40px;">
		<?php echo esc_html( __( 'Twoja subskrypcja została anulowana.' ) ); ?>
		<dl>
			<dt><?php echo esc_html( __( 'Nazwa subskrypcji:' ) ); ?></dt>
			<dd><?php echo wp_kses_post( $subscription_title ); ?></dd>
		</dl>
		<?php echo esc_html( __( 'W celu uzyskania szczegułów, prosimy skontaktować się z obsługą sklepu.' ) ); ?>
	</div>

<?php

do_action( 'woocommerce_email_footer', $email );
