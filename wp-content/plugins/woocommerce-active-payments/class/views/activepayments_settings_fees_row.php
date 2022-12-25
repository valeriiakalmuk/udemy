<td class="checkbox">
    <?php
    $params = array(
        'id' 		=> 'payment_fees[' . $method_id . '][enabled]',
        'type'  	=> 'checkbox',
        'label' 	=> false,
    );
    if ( !isset( $ap_options_fees[$method_id]['enabled'] ) ) {
        $ap_options_fees[$method_id]['enabled'] = '0';
    }
    woocommerce_form_field(
        $params['id'],
        $params,
        $ap_options_fees[$method_id]['enabled']
    );
    ?>
</td>
<td>
    <?php

    $fee_title = !empty($ap_options_fees[$method_id]['title'])? $ap_options_fees[$method_id]['title'] : '';

    if ( !isset( $ap_options_fees[$method_id]['title'] ) ) {
        $ap_options_fees[$method_id]['title'] = '';
    }

    $icl_language_code = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_bloginfo('language');

    $params = array(
	    'id' 		=> 'payment_fees[' . $method_id . '][title]',
	    'type'  	=> 'text',
	    'label' 	=> false,
    );

    if( $this->is_default_language() ) {
	    woocommerce_form_field(
		    $params['id'],
		    $params,
		    $fee_title
	    );
    }else{
	    $fee_title_translated = $fee_title;
	    if ( function_exists( 'icl_t' ) ) {
		    $fee_title_translated = icl_t('woocommerce_activepayments', 'title-'.$method_id, $fee_title);
	    }
    	echo '<input type="hidden" value="'.$fee_title.'" name="'.$params['id'].'">';
		echo '<input type="text" class="input-text" value="'.$fee_title_translated.'" disabled>';
    }

    ?>
</td>
<?php $calc_taxes = get_option('woocommerce_calc_taxes') == 'yes' ? true : false; ?>
<?php if ( $calc_taxes ) : ?>
    <td>
        <?php
        $tax_options = array(
            ''  => esc_html__( 'Standard', 'woocommerce' )
        );

        $tax_classes = WC_Tax::get_tax_classes();

        if ( ! empty( $tax_classes ) )
            foreach ( $tax_classes as $class ) {
                $tax_options[ sanitize_title( $class ) ] = esc_html( $class );
            }

        $tax_options['-none-'] = esc_html__( 'None', 'woocommerce_activepayments' );


        $params = array(
            'id' 		=> 'payment_fees[' . $method_id . '][tax_class]',
            'type'  	=> 'select',
            'label' 	=> false,
            'options'	=> $tax_options
        );
        if ( !isset( $ap_options_fees[$method_id]['tax_class'] ) ) {
            $ap_options_fees[$method_id]['tax_class'] = '';
        }
        woocommerce_form_field(
            $params['id'],
            $params,
            $ap_options_fees[$method_id]['tax_class']
        );
        ?>
    </td>
<?php endif; ?>
<td>
    <?php
    $params = array(
        'id' 		=> 'payment_fees[' . $method_id . '][min_order_total]',
        'type'  	=> 'text',
        'label' 	=> false,
        'custom_attributes' => array(
            'to_number' => 1,
            'step'		=> 'any'
        )
    );
    if ( !isset( $ap_options_fees[$method_id]['min_order_total'] ) ) {
        $ap_options_fees[$method_id]['min_order_total'] = '';
    }
    woocommerce_form_field(
        $params['id'],
        $params,
        $ap_options_fees[$method_id]['min_order_total']
    );
    ?>
</td>
<td>
    <?php
    $params = array(
        'id' 		=> 'payment_fees[' . $method_id . '][max_order_total]',
        'type'  	=> 'text',
        'label' 	=> false,
        'custom_attributes' => array(
            'to_number' => 1,
            'step'		=> 'any'
        )
    );
    if ( !isset( $ap_options_fees[$method_id]['max_order_total'] ) ) {
        $ap_options_fees[$method_id]['max_order_total'] = '';
    }
    woocommerce_form_field(
        $params['id'],
        $params,
        $ap_options_fees[$method_id]['max_order_total']
    );
    ?>
</td>
<td>
    <?php
    $params = array(
        'id' 		=> 'payment_fees[' . $method_id . '][type]',
        'type'  	=> 'select',
        'label' 	=> false,
        'options'	=> array(
            'fixed' 	=> esc_html__( 'Fixed', 'woocommerce_activepayments' ),
            'percent' 	=> esc_html__( 'Percent', 'woocommerce_activepayments' ),
        )
    );
    if ( !isset( $ap_options_fees[$method_id]['type'] ) ) {
        $ap_options_fees[$method_id]['type'] = '';
    }
    woocommerce_form_field(
        $params['id'],
        $params,
        $ap_options_fees[$method_id]['type']
    );
    ?>
</td>
<td>
    <?php
    $params = array(
        'id' 		=> 'payment_fees[' . $method_id . '][amount]',
        'type'  	=> 'text',
        'label' 	=> false,
        'custom_attributes' => array(
            'to_number' => 1,
            'step'		=> 'any'
        )
    );
    if ( !isset( $ap_options_fees[$method_id]['amount'] ) ) {
        $ap_options_fees[$method_id]['amount'] = '';
    }
    woocommerce_form_field(
        $params['id'],
        $params,
        $ap_options_fees[$method_id]['amount']
    );
    ?>
</td>
