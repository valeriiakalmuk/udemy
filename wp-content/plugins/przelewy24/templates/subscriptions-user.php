<?php
/**
 * Template with tabs of multi currency config.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $user ) ) {
	throw new LogicException( 'The variable $user is not set.' );
}
if ( ! isset( $subscriptions ) ) {
	throw new LogicException( 'The variable $subscriptions is not set.' );
}
if ( ! isset( $cards ) ) {
	throw new LogicException( 'The variable $cards is not set.' );
}
if ( ! isset( $files ) ) {
	throw new LogicException( 'The variable $files is not set.' );
}
if ( ! isset( $inactive ) ) {
	throw new LogicException( 'The variable $inactive is not set.' );
}

?>

<?php if ( $subscriptions ) : ?>
<form method="POST">
<table class="p24-border">
	<tr>
		<th><?php echo esc_html( __( 'Ważna do' ) ); ?></th>
		<th><?php echo esc_html( __( 'Subskrypcja' ) ); ?></th>
		<th><?php echo esc_html( __( 'Karta' ) ); ?></th>
	</tr>

	<?php foreach ( $subscriptions as $one ) : ?>
		<tr>
			<td><?php echo esc_html( $one->valid_to ); ?></td>
			<td><?php echo esc_html( $one->subscription_title ); ?></td>
			<td>
				<select name="card_for_subscription[<?php echo esc_attr( $one->record_id ); ?>]">
					<option value="">
						<?php echo esc_html( __( 'Brak podpiętej karty' ) ); ?>
					</option>
					<?php foreach ( $cards as $card ) : ?>
					<option value="<?php echo esc_attr( $card->custom_key ); ?>" 
											<?php
											if ( $one->card_ref === $card->custom_key ) :
												?>
						selected="selected" <?php endif; ?>>
						<?php echo esc_html( $card->custom_value['type'] ); ?>
						<?php echo esc_html( $card->custom_value['mask'] ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<td></td>
		<td colspan="2">
			<input type="hidden" name="p24_action_type_field" value="user_subscriptions" >
			<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
			<input type="submit" value="<?php echo esc_html( __( 'Zapisz' ) ); ?>" />
		</td>
	</tr>
</table>
</form>

	<?php if ( $files ) : ?>
		<h3><?php echo esc_html( __( 'Dostępne pliki' ) ); ?></h3>
		<ul>
		<?php foreach ( $files as $file ) : ?>
		<li>
			<a href="/?p24&download&subscription_id=<?php echo esc_attr( $file['parent_id'] ); ?>&file_name=<?php echo esc_attr( $file['name_url'] ); ?>"><?php echo esc_html( $file['name'] ); ?></a>
		</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

<?php else : ?>
<div><?php echo esc_html( __( 'Nie masz aktywnych subskrypcji.' ) ); ?></div>
<?php endif; ?>

<?php if ( $inactive ) : ?>
	<h3><?php echo esc_html( __( 'Nieaktywne subskrypcje' ) ); ?></h3>
	<table class="p24-border">
		<tr>
			<th><?php echo esc_html( __( 'Subskrypcja' ) ); ?></th>
		</tr>

		<?php foreach ( $inactive as $one ) : ?>
			<tr>
				<td><?php echo esc_html( $one->subscription_title ); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>

<?php
