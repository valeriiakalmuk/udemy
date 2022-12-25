<?php
if ( ! function_exists( 'woocommerce_form_field' ) ) {
	$wc_template_functions = trailingslashit( dirname( __FILE__) ) . '../../../woocommerce/includes/wc-template-functions.php';
	if ( file_exists( $wc_template_functions ) ) {
		include_once( $wc_template_functions );
	}
}
?>
	<p><?php esc_html_e( 'Fees configuration for payment methods.', 'woocommerce_activepayments' ); ?></p>

	<form action="" method="post">
		<?php wp_nonce_field( 'save_settings', $this->plugin->get_namespace() ); ?>
		<?php settings_fields( 'woocommerce_activepayments_settings_fees' ); ?>

 		<?php if (!empty($_POST['option_page']) && $_POST['option_page'] === 'woocommerce_activepayments_settings_fees') : ?>
			<div id="message" class="updated fade"><p><strong><?php esc_html_e( 'Settings saved.', 'woocommerce_activepayments' ); ?></strong></p></div>
		<?php endif; ?>

        <table class="active-payments-fees wc_input_table widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Payment Method', 'woocommerce_activepayments' ); ?></th>
					<th class="sort"><?php esc_html_e( 'Enable', 'woocommerce_activepayments' ); ?></th>
					<th><?php esc_html_e( 'Fee Title', 'woocommerce_activepayments' ); ?>  <?php echo wc_help_tip( esc_html__( 'Enter fee title, used in the checkout and order summary.', 'woocommerce_activepayments' ) ); ?></th>
                    <?php $calc_taxes = get_option('woocommerce_calc_taxes') == 'yes' ? true : false; ?>
                    <?php if ( $calc_taxes ) : ?>
                        <th><?php esc_html_e( 'Tax Class', 'woocommerce_activepayments' ); ?> <?php echo wc_help_tip( esc_html__( 'Select tax class for calculating fee amount.', 'woocommerce_activepayments' ) ); ?></th>
                    <?php endif; ?>
					<th><?php esc_html_e( 'Min Order Total', 'woocommerce_activepayments' ); ?> <?php echo wc_help_tip( esc_html__( 'Enter Minimum Order Total (including shipping).', 'woocommerce_activepayments' ) ); ?></th>
					<th><?php esc_html_e( 'Max Order Total', 'woocommerce_activepayments' ); ?> <?php echo wc_help_tip( esc_html__( 'Enter Maximum Order Total (including shipping)', 'woocommerce_activepayments' ) ); ?></th>
					<th><?php esc_html_e( 'Type', 'woocommerce_activepayments' ); ?> <?php echo wc_help_tip( esc_html__( 'Select fixed value or percentage of order total.', 'woocommerce_activepayments' ) ); ?></th>
					<th><?php esc_html_e( 'Amount', 'woocommerce_activepayments' ); ?> <?php echo wc_help_tip( esc_html__( 'Enter fixed value or percent. Based value for calculation is depended on shop tax settings (with or without tax). Calculated value is always without tax.', 'woocommerce_activepayments' ) ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $paymentGateways as $paymentGateway ): ?>
					<?php $title = $paymentGateway->get_title(); ?>
					<?php if ( $paymentGateway->enabled == 'yes'): ?>
						<tr>
                            <td class="title"><?php echo esc_html( $title ); ?></td>
							<?php $this->ap_settings_fees_row( $ap_options_fees, $paymentGateway->id ); ?>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="submit"><input type="submit" value="<?php esc_attr_e( 'Save Changes', 'woocommerce_activepayments' ); ?>" class="button button-primary" id="submit" name=""></p>
	</form>
<script type="text/javascript">
	var tiptip_args = {
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 200
	};
	jQuery( '.tips, .help_tip, .woocommerce-help-tip' ).tipTip( tiptip_args );
</script>
