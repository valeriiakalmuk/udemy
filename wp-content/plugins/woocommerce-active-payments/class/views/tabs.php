<div class="active-payments wrap">
    <h2 class="nav-tab-wrapper">
       <?php foreach ( $tabs as $key => $tab ): ?>
           <a class="nav-tab <?php if ( $current_tab === $key): ?>nav-tab-active<?php endif; ?>" href="<?php echo esc_url( admin_url( $tab['page'] ) ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
       <?php endforeach; ?>
    </h2>

    <h2><?php esc_html_e( 'Active Payments', 'woocommerce_activepayments' ); ?></h2>
