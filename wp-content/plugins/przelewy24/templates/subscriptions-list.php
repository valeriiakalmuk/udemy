<?php
/**
 * Template for active multi currency.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $list ) ) {
	throw new LogicException( 'The variable $list is not set.' );
}

?>

<h1><?php echo esc_html( __( 'Moduł subskrypcji' ) ); ?></h1>

<form method="post">
	<table class="p24-border">
		<tr>
		<tr>
			<th><?php echo esc_html( __( 'Użytkownik' ) ); ?></th>
			<th><?php echo esc_html( __( 'E-Mail' ) ); ?></th>
			<th><?php echo esc_html( __( 'Ważna do' ) ); ?></th>
			<th><?php echo esc_html( __( 'Subskrypcje' ) ); ?></th>
			<th><?php echo esc_html( __( 'Aktywna' ) ); ?></th>
		</tr>

		<?php foreach ( $list as $one ) : ?>
		<tr>
			<td><?php echo esc_html( $one->user_nicename ); ?></td>
			<td><?php echo esc_html( $one->user_email ); ?></td>
			<td><?php echo esc_html( $one->valid_to ); ?></td>
			<td><?php echo esc_html( $one->subscription_title ); ?></td>
			<td>
				<input type="checkbox" checked name="preserve[]" value="<?php echo esc_attr( $one->record_id ); ?>">
				<input type="hidden" name="displayed[]" value="<?php echo esc_attr( $one->record_id ); ?>">
			</td>
		</tr>
<?php endforeach; ?>
	</table>
	<p>
		<?php wp_nonce_field( 'p24_subscription' ); ?>
		<button><?php echo esc_html( __( 'Edytuj' ) ); ?></button>
	</p>
</form>

<div>
	<a href="?p24&subscription_csv"><?php echo esc_html( __( 'Pobierz CSV' ) ); ?></a>
</div>

<?php
