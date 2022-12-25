            <table class="form-table payu-api">
                <tbody>
				<tr valign="top" class="pos-settings" data-env="production">
					<th class="titledesc" scope="row">
						<label><?php _e( 'ID punktu płatności (pos_id)', 'woocommerce_payu' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_ID;
						$params = array(
								'type'  => 'text',
								'label' => '',
								'description'   => __( 'Wpisz identyfikator pos w systemie PayU, uzyskany podczas tworzenia punktu płatności.', 'woocommerce_payu' ),
						);

						woocommerce_form_field(
							    $field_key.'['.$currency.']'.'['.$key.']',
								$params,
								$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>
				<tr valign="top" class="pos-settings" data-api="classic" data-env="production">
					<th class="titledesc" scope="row">
						<label><?php _e('Klucz (MD5)', 'woocommerce_payu'); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_MD5_KEY_1;
						$params = array(
								'type'  => 'text',
								'label' => '',
								'description'   => __( 'Wpisz klucz MD5 w systemie PayU, uzyskany podczas tworzenia punktu płatności.','woocommerce_payu' ),
						);
						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>
                <tr valign="top" class="pos-settings" data-env="production">
                    <th class="titledesc" scope="row">
                        <label><?php _e('Drugi klucz (MD5)', 'woocommerce_payu'); ?></label>
                    </th>

                    <td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_MD5_KEY_2;
						$params = array(
							'type'  => 'text',
							'label' => '',
							'description'   => __( 'Wpisz drugi klucz MD5 w systemie PayU, uzyskany podczas tworzenia punktu płatności.','woocommerce_payu' ),
						);

						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
                    </td>
                </tr>
                <tr valign="top" class="pos-settings" data-api="classic" data-env="production">
                    <th class="titledesc" scope="row">
                        <label><?php _e('Klucz autoryzacji płatności (pos_auth_key)', 'woocommerce_payu'); ?></label>
                    </th>

                    <td class="forminp forminp-text">
		                <?php
						$key =  WPDesk_PayU_Settings_POS::POS_FIELD_AUTH_KEY;

		                $params = array(
			                'type'  => 'text',
			                'label' => '',
		                );

		                woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
		                ?>
                    </td>
                </tr>
				<tr valign="top" class="pos-settings" data-api="rest" data-env="production">
					<th class="titledesc" scope="row">
						<label><?php _e('Protokół OAuth - client_id', 'woocommerce_payu'); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_CLIENT_ID;

						$params = array(
								'type'  => 'text',
								'label' => '',
						);
						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>
				<tr valign="top" class="pos-settings" data-api="rest" data-env="production">
					<th class="titledesc" scope="row">
						<label><?php _e('Protokół OAuth - client_secret', 'woocommerce_payu'); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_CLIENT_SECRET;

						$params = array(
								'type'  => 'text',
								'label' => '',
						);
						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>




				<tr valign="top" class="pos-settings" data-env="sandbox">
					<th class="titledesc" scope="row">
						<label><?php _e( 'ID punktu płatności (pos_id)', 'woocommerce_payu' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_ID;
						$params = array(
								'type'  => 'text',
								'label' => '',
								'description'   => __( 'Wpisz identyfikator pos w systemie PayU, uzyskany podczas tworzenia punktu płatności.', 'woocommerce_payu' ),
						);

						woocommerce_form_field(
							    $field_key.'['.$currency.']'.'['.$key.']',
								$params,
								$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>
				<tr valign="top" class="pos-settings" data-api="classic" data-env="sandbox">
					<th class="titledesc" scope="row">
						<label><?php _e('Klucz (MD5)', 'woocommerce_payu'); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_MD5_KEY_1;
						$params = array(
								'type'  => 'text',
								'label' => '',
								'description'   => __( 'Wpisz klucz MD5 w systemie PayU, uzyskany podczas tworzenia punktu płatności.','woocommerce_payu' ),
						);
						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>
                <tr valign="top" class="pos-settings" data-env="sandbox">
                    <th class="titledesc" scope="row">
                        <label><?php _e('Drugi klucz (MD5)', 'woocommerce_payu'); ?></label>
                    </th>

                    <td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_MD5_KEY_2;
						$params = array(
							'type'  => 'text',
							'label' => '',
							'description'   => __( 'Wpisz drugi klucz MD5 w systemie PayU, uzyskany podczas tworzenia punktu płatności.','woocommerce_payu' ),
						);

						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
                    </td>
                </tr>

                <tr valign="top" class="pos-settings" data-api="classic" data-env="sandbox">
                    <th class="titledesc" scope="row">
                        <label><?php _e('Klucz autoryzacji płatności (pos_auth_key)', 'woocommerce_payu'); ?></label>
                    </th>

                    <td class="forminp forminp-text">
		                <?php
						$key =  WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_AUTH_KEY;

		                $params = array(
			                'type'  => 'text',
			                'label' => '',
		                );

		                woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
		                ?>
                    </td>
                </tr>

				<tr valign="top" class="pos-settings" data-api="rest" data-env="sandbox">
					<th class="titledesc" scope="row">
						<label><?php _e('Protokół OAuth - client_id', 'woocommerce_payu'); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_CLIENT_ID;

						$params = array(
								'type'  => 'text',
								'label' => '',
						);
						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>

				<tr valign="top" class="pos-settings" data-api="rest" data-env="sandbox">
					<th class="titledesc" scope="row">
						<label><?php _e('Protokół OAuth - client_secret', 'woocommerce_payu'); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$key = WPDesk_PayU_Settings_POS::POS_FIELD_SANDBOX_CLIENT_SECRET;

						$params = array(
								'type'  => 'text',
								'label' => '',
						);
						woocommerce_form_field(
							$field_key.'['.$currency.']'.'['.$key.']',
							$params,
							$profile[$key] ?? ''
						);
						?>
					</td>
				</tr>

                </tbody>
            </table>