<?php
/**
 * Template for active multi currency.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $is_active ) ) {
	throw new LogicException( 'The variable $is_active is not set.' );
}

if ( ! isset( $days_to_renew ) ) {
	throw new LogicException( 'The variable $days_to_renew is not set.' );
}

if ( ! isset( $page_id ) ) {
	/* Force defined null. */
	$page_id = null;
}

?>

<h1><?php echo esc_html( __( 'Moduł subskrypcji' ) ); ?></h1>

<form method="post">
	<table>
		<tr>
			<?php $field_id = 'p24_subscriptions_active_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Aktywuj subskrypcje' ) ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="p24_subscriptions_active" <?php echo $is_active ? esc_attr( 'checked' ) : ''; ?> id="<?php echo esc_attr( $field_id ); ?>" value="1" />
			</td>
		</tr>

		<tr>
			<?php $field_id = 'p24_subscription_page_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Strona subskrypcji użytkownika' ) ); ?></label>
			</th>
			<td>
				<select name="p24_subscription_page" id="<?php echo esc_attr( $field_id ); ?>">
					<option value="nothing"><?php echo esc_html( __( 'Brak akcji' ) ); ?></option>
					<?php if ( $page_id ) : ?>
					<option value="delete"><?php echo esc_html( __( 'Skasuj' ) ); ?></option>
					<option value="force"><?php echo esc_html( __( 'Ponownie wygeneruj' ) ); ?></option>
					<?php else : ?>
					<option value="generate"><?php echo esc_html( __( 'Wygeneruj' ) ); ?></option>
					<?php endif; ?>
				</select>
				<?php if ( $page_id ) : ?>
				<a href="<?php echo esc_attr( get_permalink( $page_id ) ); ?>"><?php echo esc_html( __( 'Link do obecnej strony' ) ); ?></a>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<?php $field_id = 'p24_subscriptions_days_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Liczba dni na odnowienie subskrypcji' ) ); ?></label>
			</th>
			<td>
				<input type="number" name="p24_subscriptions_days" id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo (int) $days_to_renew; ?>" />
			</td>
		</tr>

		<tr>
			<td></td>
			<td colspan="2">
				<input type="hidden" name="p24_action_type_field" value="update_subscription" />
				<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
				<input type="submit" value="<?php echo esc_html( __( 'Zapisz' ) ); ?>" />
			</td>
		</tr>
	</table>
</form>
<?php
