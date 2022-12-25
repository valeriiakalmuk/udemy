<?php
    /** @var bool $is_without_any_settings */
    $is_without_any_settings = $args['is_without_any_settings'];
?>
	<form action="" method="post">
		<?php settings_fields( 'woocommerce_activepayments_settings' ); ?>

 		<?php if ( isset($_POST['option_page']) && $_POST['option_page'] === 'woocommerce_activepayments_settings'): ?>
			<div id="message" class="updated fade"><p><strong><?php esc_html_e( 'Settings saved.', 'woocommerce_activepayments' ); ?></strong></p></div>
		<?php endif; ?>

        <?php
            $plugin_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/flexible-shipping-pro-woocommerce/' : 'https://www.wpdesk.net/products/flexible-shipping-pro-woocommerce/';
            $utm = '?utm_source=active-payments-settings&utm_medium=link&utm_campaign=active-payments-fs-pro-main-link'
        ?>

		<p><?php esc_html_e( 'Select which payments methods will be available for shipping methods.', 'woocommerce_activepayments' ); ?> <?php printf( wp_kses( esc_html__( 'Active Payments works great with <a href="%s" target="_blank">Flexible Shipping for WooCommerce</a>.', 'woocommerce_activepayments' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $plugin_link . $utm ) ); ?></p>

		<table class="active-payments-main widefat">
    		<colgroup></colgroup>
    		<?php for($i = 0; $i < count($args['paymentGateways']); $i++): ?>
    		<colgroup></colgroup>
    		<?php endfor; ?>
			<thead>

				<tr>
					<th width="250"></th>
					<?php foreach ( $args['paymentGateways'] as $payment ): ?>
						<th><?php echo esc_html__( $payment->get_title(), 'woocommerce_activepayments' ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $args['shippingMethods'] as $method ): ?>
					<?php $title = $method->get_title(); ?>
					<?php if ($method->enabled == 'yes'): ?>

						<?php if (!empty($title) && !in_array($method->id, array('flat_rate', 'table_rate_shipping', 'kurjerzy_shipping_method', 'flexible_shipping')) ): ?>
							<tr>
								<th><?php echo esc_html( $title ); ?></th>
								<?php foreach ($args['paymentGateways'] as $payment): ?>
								<td>
    								    <label for="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $payment->id ); ?>">
    								        <input type="checkbox" <?php if ($this->getSettingValue('pm_' . md5($method->id . '_' . $payment->id), $is_without_any_settings) != ''): ?>checked="checked"<?php endif; ?> id="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $payment->id ); ?>" name="payment_method[<?php echo esc_attr( $method->id ); ?>][<?php echo esc_attr( $payment->id ); ?>]" value="1" />
    								    </label>
    				            </td>
								<?php endforeach; ?>
							</tr>
						<?php endif; ?>

						<?php if ($method->id == 'flat_rate'): ?>
						    <?php $options = ActivePayments::get_options_from_gateway($method); ?>
							<?php foreach ($options as $methodOption): ?>
								<?php //$x = new WC_Shipping_Flat_Rate();

									$fname = @trim(reset(explode('|', $methodOption)));
									$fname_id = ActivePayments::generate_flat_id_from_title($fname);
								?>
									<tr>
										<th><?php if (!empty($title)): ?><?php echo esc_html( $title ); ?> - <?php endif; ?><?php echo esc_html( $fname ); ?></th>
										<?php foreach ($args['paymentGateways'] as $payment): ?>
                                        <td>
                                            <label for="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $fname_id ); ?>_<?php echo esc_attr( $payment->id ); ?>">
                                                <input type="checkbox" <?php if ($this->getSettingValue('pm_' . md5($method->id . '_' . $payment->id . '_' . $fname_id), $is_without_any_settings) != ''): ?>checked="checked"<?php endif; ?> id="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $fname_id ); ?>_<?php echo esc_attr( $payment->id ); ?>" name="payment_method[<?php echo esc_attr( $method->id ); ?>:<?php echo esc_attr( $fname_id ); ?>][<?php echo esc_attr( $payment->id ); ?>]" value="1" />
                                            </label>
                                        </td>
										<?php endforeach; ?>
									</tr>
							<?php endforeach; ?>

						<?php elseif($method->id == 'table_rate_shipping'): // table rate shipping intergration ?>
							<?php foreach ($args['shippingTableMethods'] as $stMethod): ?>
								<tr>
									<th><?php echo esc_html( $stMethod['title'] ); ?></th>
									<?php foreach ($args['paymentGateways'] as $payment): ?>
                                    <td>
                                        <label for="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $stMethod['identifier'] ); ?>_<?php echo esc_attr( $payment->id ); ?>">
                                            <input type="checkbox" <?php if ($this->getSettingValue('pm_' . md5($method->id . '_' . $payment->id . '_' . $stMethod['identifier']), $is_without_any_settings) != ''): ?>checked="checked"<?php endif; ?> id="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $stMethod['identifier'] ); ?>_<?php echo esc_attr( $payment->id ); ?>" name="payment_method[<?php echo esc_attr( $method->id ); ?>:<?php echo esc_attr( $stMethod['identifier'] ); ?>][<?php echo esc_attr( $payment->id ); ?>]" value="1" />
                                        </label>
                                    </td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>

						<?php elseif($method->id == 'flexible_shipping'): /* flexible shipping intergration */ ?>
							<?php foreach ($args['shippingFSMethods'] as $stMethod): ?>
								<tr>
									<th><?php echo esc_html( $stMethod['title'] ); ?></th>
									<?php foreach ($args['paymentGateways'] as $payment): ?>
                                    <td>
                                        <label for="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $stMethod['identifier'] ); ?>_<?php echo esc_attr( $payment->id );?>">
                                            <input type="checkbox" <?php if ($this->getSettingValue('pm_' . md5($method->id . '_' . $payment->id . '_' . $stMethod['identifier']), $is_without_any_settings) != ''): ?>checked="checked"<?php endif; ?> id="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $stMethod['identifier'] ); ?>_<?php echo esc_attr( $payment->id );?>" name="payment_method[<?php echo esc_attr( $method->id ); ?>:<?php echo esc_attr( $stMethod['identifier'] ); ?>][<?php echo esc_attr( $payment->id );?>]" value="1" />
                                        </label>
                                    </td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>

							<?php foreach ($args['shippingFSMethods_woo'] as $stMethod): ?>
								<tr>
									<th><?php echo esc_html( $stMethod['title'] ); ?></th>
									<?php foreach ($args['paymentGateways'] as $payment): ?>
                                    <td>
                                        <label for="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $stMethod['identifier'] ); ?>_<?php echo esc_attr( $payment->id );?>">
                                            <input type="checkbox" <?php if ($this->getSettingValue('pm_' . md5($method->id . '_' . $payment->id . '_' . $stMethod['identifier']), $is_without_any_settings) != ''): ?>checked="checked"<?php endif; ?> id="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $stMethod['identifier'] ); ?>_<?php echo esc_attr( $payment->id );?>" name="payment_method[<?php echo esc_attr( $method->id ); ?>:<?php echo esc_attr( $stMethod['identifier'] ); ?>][<?php echo esc_attr( $payment->id ); ?>]" value="1" />
                                        </label>
                                    </td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>

						<?php elseif($method->id == 'kurjerzy_shipping_method'): // kurJerzy intergration ?>
							<?php //foreach ($args['shippingTableMethods'] as $stMethod): ?>
							    <?php foreach($method->couriers as $courier): ?>
    								<tr>
    									<th>kurJerzy - <?php echo esc_html( $courier ); ?></th>
    									<?php foreach ($args['paymentGateways'] as $payment): ?>
                                        <td>
                                            <label for="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $courier ); ?>_<?php echo esc_attr( $payment->id );?>">
                                                <input type="checkbox" <?php if ($this->getSettingValue('pm_' . md5($method->id . '_' . $courier . '_' . $payment->id ), $is_without_any_settings) != ''): ?>checked="checked"<?php endif; ?> id="payment_method_<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $courier ); ?>_<?php echo esc_attr( $payment->id ); ?>" name="payment_method[<?php echo esc_attr( $method->id ); ?>_<?php echo esc_attr( $courier ); ?>][<?php echo esc_attr( $payment->id ); ?>]" value="1" />
                                            </label>
                                        </td>
    									<?php endforeach; ?>
    								</tr>
								<?php endforeach ?>
							<?php //endforeach; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr>
					<th><?php esc_html_e( 'Disable Payment Method', 'woocommerce_activepayments' ); ?> <?php echo wc_help_tip( esc_html__( 'Disable payment method above entered cart total (shipping costs excluded)', 'woocommerce_activepayments' ) ); ?></th>
					<?php foreach ($args['paymentGateways'] as $payment): ?>
						<td><input class="amount" type="number" step="any" min="0" name="payment_method[<?php echo esc_attr( $payment->id ); ?>][amount]" value="<?php echo esc_attr( $this->getSettingValue( 'pm_' . md5($payment->id . '_amount') ) ); ?>" /></td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>

		<p class="submit"><input type="submit" value="<?php esc_attr_e( 'Save Changes', 'woocommerce_activepayments' ); ?>" class="button button-primary" id="submit" name=""></p>
	</form>
