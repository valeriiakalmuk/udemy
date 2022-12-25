<?php
/**
 * Template for active multi currency.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $field_name ) ) {
	throw new LogicException( 'The variable $field_name is not set.' );
}

if ( ! isset( $deleted ) ) {
	throw new LogicException( 'The variable $deleted is not set.' );
}

?>

<h1><?php echo esc_html( __( 'Kasowanie ustawień Przelewy24' ) ); ?></h1>

<?php if ( $deleted ) : ?>

<div>
	<strong><?php echo esc_html( __( 'Ustawienia zostały skasowane.' ) ); ?></strong>
</div>

<?php else : ?>

	<?php $field_id = $field_name . wp_rand(); ?>

<form method="post">
	<table>
		<tr>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Potwierdź skasowanie ustawień Przelewy24' ) ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="yes">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
				<input type="submit" value="<?php echo esc_html( __( 'Wykonaj' ) ); ?>">
			</td>
		</tr>
	</table>
</form>

<script>
	jQuery(function () {
		let $ = jQuery;
		let $confirm = $('#<?php echo esc_attr( $field_id ); ?>');
		let $button = $confirm.parents('form').find('input[type=submit]');
		$button.prop('disabled', true);
		$confirm.on('change', function () {
			let canProcess = $confirm.prop('checked');
			$button.prop('disabled', !canProcess);
		});
	});
</script>

<?php endif; ?>

<?php
