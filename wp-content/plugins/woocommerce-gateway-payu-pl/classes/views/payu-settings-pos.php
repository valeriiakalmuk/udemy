<tr valign="top" id="acconts-row">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?> <?php echo $this->get_tooltip_html($data); // WPCS: XSS ok. ?></label>
	</th>
	<td class="forminp">
		<fieldset>
			<table class="form-table" id="payu-accounts">
				<tbody>
					<tr valign="top">
						<td>
							<p><b><?php _e('Obsługiwane waluty', 'woocommerce_payu'); ?></b></p>
						</td>
					</tr>
					<tr valign="top">
						<td style="padding: 0 10px;">
							<div id="settings-accordion">
								<?php
								if (isset($profiles) && !empty($profiles)) {
									foreach ($profiles as $currency => $profile) {
										//$is_default_profile = array_key_first($default) === $currency;
										$is_default_profile = false;
										$is_test_mode = false;
										require 'payu-settings-pos-item.php';
									}
								}
								?>
							</div>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<select name="currency_selector" id="currency-selector">
							<?php 
								echo '<option value="">'.__('Wybierz walutę', 'woocommerce_payu').'</option>';
								foreach(WPDesk_PayU_Settings::SUPPORTED_CURRENCIES as $currency){
									echo '<option value="'.$currency.'">'.$currency.'</option>';
								}
							?>
							</select> <button class="button button-primary" id="add-new-payu-pos"> <?php _e('Dodaj nową walutę', 'woocommerce_payu'); ?> </button>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</td>
</tr>