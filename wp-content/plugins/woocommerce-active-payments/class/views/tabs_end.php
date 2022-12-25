    <div class="metabox-holder">
        <div class="stuffbox">
            <h3 class="hndle"><?php esc_html_e( 'Get more WP Desk Plugins!', 'woocommerce_activepayments' ); ?></h3>

            <?php
                $fs_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/flexible-shipping-pro-woocommerce/' : 'https://www.wpdesk.net/products/flexible-shipping-pro-woocommerce/';
                $fcf_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/woocommerce-checkout-fields/' : 'https://www.wpdesk.net/products/woocommerce-checkout-fields/';
                $fi_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/faktury-woocommerce/' : 'https://www.wpdesk.net/products/flexible-invoices-woocommerce/';
            ?>

            <div class="inside">
                <div class="main">
                    <p><a href="<?php echo esc_url( $fs_link ); ?>?utm_source=active-payments-settings&utm_medium=link&utm_campaign=flexible-shipping-pro-plugin" target="_blank"><?php esc_html_e( 'Flexible Shipping', 'woocommerce_activepayments' ); ?></a> - <?php esc_html_e( 'Create shipping methods based on weight, totals and more.', 'woocommerce_activepayments' ); ?></p>

                    <p><a href="<?php echo esc_url( $fi_link ); ?>?utm_source=active-payments-settings&utm_medium=link&utm_campaign=flexible-invoices-plugin" target="_blank"><?php esc_html_e( 'Flexible Invoices', 'woocommerce_activepayments' ); ?></a> - <?php esc_html_e( 'Issue invoices for your WooCommerce orders.', 'woocommerce_activepayments' ); ?></p>

                    <p><a href="<?php echo esc_url( $fcf_link ); ?>?utm_source=active-payments-settings&utm_medium=link&utm_campaign=flexible-checkout-fields-plugin" target="_blank"><?php esc_html_e( 'Flexible Checkout Fields', 'woocommerce_activepayments' ); ?></a> - <?php esc_html_e( 'Manage WooCommerce checkout fields and add your own.', 'woocommerce_activepayments' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
