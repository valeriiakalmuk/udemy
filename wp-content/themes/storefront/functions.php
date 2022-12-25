<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme = wp_get_theme('storefront');
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if (!isset($content_width)) {
    $content_width = 980; /* pixels */
}

$storefront = (object)array(
    'version' => $storefront_version,

    /**
     * Initialize all the things.
     */
    'main' => require 'inc/class-storefront.php',
    'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if (class_exists('Jetpack')) {
    $storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if (storefront_is_woocommerce_activated()) {
    $storefront->woocommerce = require 'inc/woocommerce/class-storefront-woocommerce.php';
    $storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

    require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

    require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
    require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
    require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if (is_admin()) {
    $storefront->admin = require 'inc/admin/class-storefront-admin.php';

    require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if (version_compare(get_bloginfo('version'), '4.7.3', '>=') && (is_admin() || is_customize_preview())) {
    require 'inc/nux/class-storefront-nux-admin.php';
    require 'inc/nux/class-storefront-nux-guided-tour.php';
    require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */


function add_avaible_sizes($limit)
{
    $timestamp = time() - 86400;
    $ids = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
//        'meta_query' => array(
//            'relation' => 'OR',
//            array(
//                'key' => '_attrs_last_sync',
//                'value' => $timestamp,
//                'compare' => '<=',
//            ), array(
//                'key' => '_attrs_last_sync',
//                'compare' => 'NOT EXISTS',
//            )),
    ));
    if (!empty($ids)) {
        foreach ($ids as $id) {
            if (wc_get_product($id)) {
                $children = wc_get_product($id)->get_children();
                if ($children) {
                    $avaible_sizes = [];
                    foreach ($children as $child) {
                        $variation = new WC_Product_variation($child);
                        if ($variation->get_stock_quantity() > 0 && $variation->get_variation_attributes()['attribute_pa_rozmiar']) {
                            array_push($avaible_sizes, $variation->get_variation_attributes()['attribute_pa_rozmiar']);
                        }
                    }
                    $attrs = get_post_meta($id, '_product_attributes', true);
                    if (!empty($avaible_sizes)) {
                        foreach ($avaible_sizes as $avaible_size) {
                            $term_value = get_term_by('slug', $avaible_size, 'pa_rozmiar');
                            wp_set_object_terms($id, $term_value->name, 'pa_dostepne-rozmiary', true);
                            $thedata = array('pa_dostepne-rozmiary' => array(
                                'name' => 'pa_dostepne-rozmiary',
                                'value' => $term_value->name,
                                'is_visible' => '0',
                                'is_taxonomy' => '1',
                                'is_variation' => '0',
                            ));
                            update_post_meta($id, '_product_attributes', array_merge($attrs, $thedata));
                        }
                    } else {
                        $temp_product_attributes = [];
                        foreach ($attrs as $attr) {
                            foreach ($attr as $pt_k => $pt_v) {
                                if ($pt_v !== 'pa_dostepne-rozmiary') {
                                    $temp_product_attributes[$pt_k] = $pt_v;
                                }
                            }
                            $arr[$attr['name']] = $temp_product_attributes;
                        }
                        update_post_meta($id, '_product_attributes', $arr);
                    }
                }
                $product = wc_get_product($id);
                $product->update_meta_data('_attrs_last_sync', time(), true);
                $product->save();
                echo 'attr rebuilt';
            }
        }
    } else {
        echo 'all products rebuilt';
    }
}

function add_sizes_table($limit)
{
    $product_ids = get_posts(array(
        'post_type' => 'product',
        'numberposts' => $limit,
        'post_status' => 'publish',
        'fields' => 'ids',
    ));
    if (!empty($product_ids)) {
        foreach ($product_ids as $product_id) {
            if (wc_get_product($product_id)) {
                $sex = wc_get_product($product_id)->get_attribute('pa_plec');
                $producer = wc_get_product($product_id)->get_attribute('pa_producent');
                $query = strtolower($producer . ' ' . $sex);
                $post_id = get_page_by_title($query, OBJECT, 'sizes');
                if ($post_id) {
                    update_field('sizes_table', [$post_id->ID], $product_id);
                }
                else {
                    echo 'Brak tabeli rozmiarowej dla '. $query;
                }
            }
        }
    } else {
        echo 'all sizes added';
    }
}


add_action('init', function () {
    if (isset($_GET['rebuild_attr'])) {
        add_avaible_sizes(1);
        exit;
    }
    if (isset($_GET['sizes_table'])) {
        add_sizes_table(20);
        exit;
    }
});

function action_woocommerce_order_status_changed( $order_id, $from, $to, $instance ) {
    $order = new WC_Order( $order_id );
    $method = $order->get_payment_method();

    if(!is_admin() && $method == 'payu' && $from == 'pending' && $to =='processing') {
        $order->update_status('wc-on-hold');
    }

    file_put_contents(ABSPATH . "wp-content/themes/storefront/log.txt", json_encode([
                $order_id,
        $order->get_payment_method(),
            $from,
            $to,
            is_admin()
        ]
    ). "\r\n", FILE_APPEND);
};

// add the action




add_action('acf/input/admin_head', 'my_acf_admin_head');

function my_acf_admin_head() {

    ?>
    <script type="text/javascript">
        (function($) {

            $(document).ready(function(){
                $('#acf-group_6218a911357a6').insertBefore( $('#product_catdiv') );

            });

        })(jQuery);
    </script>
    <style type="text/css">
        .acf-field #wp-content-editor-tools {
            background: transparent;
            padding-top: 0;
        }
    </style>
    <?php

}

function my_custom_function() {
    
}
add_filter( 'woocommerce_after_cart_totals', 'my_custom_function', 20 );

add_action( 'woocommerce_checkout_create_order_shipping_item', 'action_wc_checkout_create_order_shipping_item', 10, 4 );
function action_wc_checkout_create_order_shipping_item( $item, $package_key, $package, $order ) {
    echo "Test123";
    if ( isset($_POST['wc_pickup_store']) && ! empty($_POST['wc_pickup_store']) ) {
        // Get carrier number

        $item->set_method_title( sprintf( '%s: %s %s', __("Custom Carrier", "woocommerce"), sanitize_text_field($_POST['carrier_name']), 'test' ) );
    }
}



add_action( 'wp_enqueue_scripts', 'add_my_script' );
function add_my_script() {
    wp_register_script(
        'parent-theme-script',
        get_template_directory_uri() . '/assets/js/script.js',
        array('jquery')
    );

    wp_enqueue_script('parent-theme-script');
}

add_action('woocommerce_checkout_update_order_meta', 'orderpickup', 30, 1 );

function orderpickup($order_id)
{
    $order = wc_get_order($order_id);
    $note = $order->get_customer_note();
    $store_name = wps_get_post_meta($order_id, '_shipping_pickup_stores');
    if($store_name != '') {
        $order->set_customer_note('Odbiór w sklepie: ' . $store_name . "\n" . $note);
        $order->save();
    }
}

add_action('woocommerce_register_form', 'add_registration_privacy_policy', 11);

function add_registration_privacy_policy()
{

    woocommerce_form_field('privacy_policy', array(
        'type' => 'checkbox',
        'required' => true,
        'label' => 'Wyrażam zgodę na przetwarzanie moich danych osobowych w celu założenia konta użytkownika i realizacji zamówień w ramach sklepu internetowego London Shoes przez Jarosława Bojaruńca, prowadzącego działalność gospodarczą pod nazwą P.H.U. "Boyar" Jarosław Bojaruniec, z siedzibą w Olsztynie przy Placu Kazimierza Pułaskiego 7/16, 10-515 Olsztyn, NIP: 7391288350, REGON: 510401069. Informujemy, że zgodnie z Ustawą z dnia 29 sierpnia 1997r. o ochronie danych osobowych Dz.U. z 2016r. poz. 922) każdy Kupujący ma prawo wglądu do swoich danych, ich poprawiania, zarządzania, zaprzestania przetwarzania oraz zażądania ich usunięcia. Podanie danych jest dobrowolne, ale brak zgody uniemożliwia założenie konta i realizację zamówień',
    ));
    woocommerce_form_field('reg', array(
        'type' => 'checkbox',
        'required' => true,
        'label' => 'Zapoznałem się z treścią <a href="/regulamin">Regulaminu</a> internetowego London Shoes i akceptuję jego postanowienia.',
    ));
}


add_action ('woocommerce_register_form', 'wp_help_new_woo_registration_field');
function helpwp_new_woo_registration_field () {
    woocommerce_form_field (
        'favorite group',
        array (
            'type' => 'text',
            'required' => true, // this adds an asterisk
            'label' => 'What is your favorite musical group?'
        ),
        (isset ($_POST ['favorite_group']) ? $_POST ['favorite_group']: '')
);
}


// Displaying quantity setting fields on admin product pages
add_action( 'woocommerce_product_options_pricing', 'wc_qty_add_product_field' );
function wc_qty_add_product_field() {
    global $product_object;

    $values = $product_object->get_meta('_qty_args');

    echo '</div><div class="options_group quantity hide_if_grouped">
    <style>div.qty-args.hidden { display:none; }</style>';

    woocommerce_wp_checkbox( array( // Checkbox.
        'id'            => 'qty_args',
        'label'         => __( 'Quantity settings', 'woocommerce' ),
        'value'         => empty($values) ? 'no' : 'yes',
        'description'   => __( 'Enable this to show and enable the additional quantity setting fields.', 'woocommerce' ),
    ) );

    echo '<div class="qty-args hidden">';

    woocommerce_wp_text_input( array(
        'id'                => 'qty_min',
        'type'              => 'number',
        'label'             => __( 'Minimum Quantity', 'woocommerce-max-quantity' ),
        'placeholder'       => '',
        'desc_tip'          => 'true',
        'description'       => __( 'Set a minimum allowed quantity limit (a number greater than 0).', 'woocommerce' ),
        'custom_attributes' => array( 'step'  => 'any', 'min'   => '0'),
        'value'             => isset($values['qty_min']) && $values['qty_min'] > 0 ? (int) $values['qty_min'] : 0,
    ) );

    woocommerce_wp_text_input( array(
        'id'                => 'qty_max',
        'type'              => 'number',
        'label'             => __( 'Maximum Quantity', 'woocommerce-max-quantity' ),
        'placeholder'       => '',
        'desc_tip'          => 'true',
        'description'       => __( 'Set the maximum allowed quantity limit (a number greater than 0). Value "-1" is unlimited', 'woocommerce' ),
        'custom_attributes' => array( 'step'  => 'any', 'min'   => '-1'),
        'value'             => isset($values['qty_max']) && $values['qty_max'] > 0 ? (int) $values['qty_max'] : -1,
    ) );

    woocommerce_wp_text_input( array(
        'id'                => 'qty_step',
        'type'              => 'number',
        'label'             => __( 'Quantity step', 'woocommerce-quantity-step' ),
        'placeholder'       => '',
        'desc_tip'          => 'true',
        'description'       => __( 'Optional. Set quantity step  (a number greater than 0)', 'woocommerce' ),
        'custom_attributes' => array( 'step'  => 'any', 'min'   => '1'),
        'value'             => isset($values['qty_step']) && $values['qty_step'] > 1 ? (int) $values['qty_step'] : 1,
    ) );

    echo '</div>';
}

// Show/hide setting fields (admin product pages)
add_action( 'admin_footer', 'product_type_selector_filter_callback' );
function product_type_selector_filter_callback() {
    global $pagenow, $post_type;

    if( in_array($pagenow, array('post-new.php', 'post.php') ) && $post_type === 'product' ) :
        ?>
        <script>
            jQuery(function($){
                if( $('input#qty_args').is(':checked') && $('div.qty-args').hasClass('hidden') ) {
                    $('div.qty-args').removeClass('hidden')
                }
                $('input#qty_args').click(function(){
                    if( $(this).is(':checked') && $('div.qty-args').hasClass('hidden')) {
                        $('div.qty-args').removeClass('hidden');
                    } else if( ! $(this).is(':checked') && ! $('div.qty-args').hasClass('hidden')) {
                        $('div.qty-args').addClass('hidden');
                    }
                });
            });
        </script>
    <?php
    endif;
}

// Save quantity setting fields values
add_action( 'woocommerce_admin_process_product_object', 'wc_save_product_quantity_settings' );
function wc_save_product_quantity_settings( $product ) {
    if ( isset($_POST['qty_args']) ) {
        $values = $product->get_meta('_qty_args');

        $product->update_meta_data( '_qty_args', array(
            'qty_min' => isset($_POST['qty_min']) && $_POST['qty_min'] > 0 ? (int) wc_clean($_POST['qty_min']) : 0,
            'qty_max' => isset($_POST['qty_max']) && $_POST['qty_max'] > 0 ? (int) wc_clean($_POST['qty_max']) : -1,
            'qty_step' => isset($_POST['qty_step']) && $_POST['qty_step'] > 1 ? (int) wc_clean($_POST['qty_step']) : 1,
        ) );
    } else {
        $product->update_meta_data( '_qty_args', array() );
    }
}

// The quantity settings in action on front end
add_filter( 'woocommerce_quantity_input_args', 'filter_wc_quantity_input_args', 99, 2 );
function filter_wc_quantity_input_args( $args, $product ) {
    if ( $product->is_type('variation') ) {
        $parent_product = wc_get_product( $product->get_parent_id() );
        $values  = $parent_product->get_meta( '_qty_args' );
    } else {
        $values  = $product->get_meta( '_qty_args' );
    }

    if ( ! empty( $values ) ) {
        // Min value
        if ( isset( $values['qty_min'] ) && $values['qty_min'] > 1 ) {
            $args['min_value'] = $values['qty_min'];

            if( ! is_cart() ) {
                $args['input_value'] = $values['qty_min']; // Starting value
            }
        }

        // Max value
        if ( isset( $values['qty_max'] ) && $values['qty_max'] > 0 ) {
            $args['max_value'] = $values['qty_max'];

            if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
                $args['max_value'] = min( $product->get_stock_quantity(), $args['max_value'] );
            }
        }

        // Step value
        if ( isset( $values['qty_step'] ) && $values['qty_step'] > 1 ) {
            $args['step'] = $values['qty_step'];
        }
    }
    return $args;
}

// Ajax add to cart, set "min quantity" as quantity on shop and archives pages
add_filter( 'woocommerce_loop_add_to_cart_args', 'filter_loop_add_to_cart_quantity_arg', 10, 2 );
function filter_loop_add_to_cart_quantity_arg( $args, $product ) {
    $values  = $product->get_meta( '_qty_args' );

    if ( ! empty( $values ) ) {
        // Min value
        if ( isset( $values['qty_min'] ) && $values['qty_min'] > 1 ) {
            $args['quantity'] = $values['qty_min'];
        }
    }
    return $args;
}

// The quantity settings in action on front end (For variable productsand their variations)
add_filter( 'woocommerce_available_variation', 'filter_wc_available_variation_price_html', 10, 3);
function filter_wc_available_variation_price_html( $data, $product, $variation ) {
    $values  = $product->get_meta( '_qty_args' );

    if ( ! empty( $values ) ) {
        if ( isset( $values['qty_min'] ) && $values['qty_min'] > 1 ) {
            $data['min_qty'] = $values['qty_min'];
        }

        if ( isset( $values['qty_max'] ) && $values['qty_max'] > 0 ) {
            $data['max_qty'] = $values['qty_max'];

            if ( $variation->managing_stock() && ! $variation->backorders_allowed() ) {
                $data['max_qty'] = min( $variation->get_stock_quantity(), $data['max_qty'] );
            }
        }
    }

    return $data;
}

$ceny = basename($_SERVER['REQUEST_URI']);
if($ceny == "?update-ceny") {
    echo "ffff";
}


//add_filter( 'cron_schedules', 'isa_add_every_five_minutes' );
function isa_add_every_five_minutes( $schedules ) {
    $schedules['every_five_minutes'] = array(
        'interval'  => 60 * 2,
        'display'   => __( 'Every 5 Minutes', 'textdomain' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'isa_add_every_five_minutes' ) ) {
    wp_schedule_event( time(), 'every_five_minutes', 'isa_add_every_five_minutes' );
}

// Hook into that action that'll fire every five minutes
//add_action( 'isa_add_every_five_minutes', 'every_five_minutes_event_func' );
function every_five_minutes_event_func() {
    $email_subject = "Testing a cron event";
    $email_content = "This is an automatic WordPress email for testing a cron event.";
    wp_mail( 'valeriia.kalmuk@marketingmatch.pl', $email_subject, $email_content );
}

