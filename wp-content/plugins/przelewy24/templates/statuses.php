<?php
/**
 * Template for active multi currency.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $statuses ) ) {
	throw new LogicException( 'The variable $statuses is not set.' );
}
if ( ! isset( $error ) ) {
	throw new LogicException( 'The variable $error is not set.' );
}
if ( ! isset( $new_code ) ) {
	throw new LogicException( 'The variable $new_code is not set.' );
}
if ( ! isset( $new_label ) ) {
	throw new LogicException( 'The variable $new_label is not set.' );
}

?>

	<h1><?php echo esc_html( __( 'Statusy' ) ); ?></h1>

	<?php if ( $statuses ) : ?>
	<h2><?php echo esc_html( __( 'Obecnie wprowadzone statusy:' ) ); ?></h2>

	<table class="p24-border">
		<tr>
			<th><?php echo esc_html( __( 'Kod' ) ); ?></th>
			<th><?php echo esc_html( __( 'Nazwa' ) ); ?></th>
		</tr>
		<?php foreach ( $statuses as $new ) : ?>
		<tr>
			<td><?php echo esc_html( $new['code'] ); ?></td>
			<td><?php echo esc_html( $new['label'] ); ?></td>
		</tr>
		<?php endforeach ?>
	</table>
	<?php endif; ?>

	<h2><?php echo esc_html( __( 'Wprowadź nowy status:' ) ); ?></h2>
	<form method="post" class="p24-label-form">
		<?php if ( $error ) : ?>
		<p class="p24-error">
			<?php echo esc_html( $error ); ?>
		</p>
		<?php endif; ?>
		<div>
		<label>
			<?php echo esc_html( __( 'Kod:' ) ); ?>
			<input name="p24-status-new-code" required value="<?php echo esc_attr( $new_code ); ?>">
		</label>
		</div>
		<div>
		<label>
			<?php echo esc_html( __( 'Nazwa:' ) ); ?>
			<input name="p24-status-new-label" required value="<?php echo esc_attr( $new_label ); ?>">
		</label>
		</div>
		<div>
		<input type="hidden" name="p24_action_type_field" value="add_status" />
		<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
		<input type="submit" value="<?php echo esc_html( __( 'Dodaj' ) ); ?>" />
		</div>
	</form>
<?php

