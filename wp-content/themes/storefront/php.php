
<?php
/*
Template Name: Projects
*/
?>

<?php

function get_products_not_reduced() {

    $posts = get_posts(array(
        'numberposts' => -1,
        'post_type'   => 'product',
        'meta_query'  => array(
            'relation'    => 'AND',
            array(
                'key'     => 'nie_aktualizuj_stanow_magazynowych',
                'value'     => '1',
                'compare'   => '==',
            ),
        ),
    ));
    $stan_mag = array();
    $list_array = array();
    if($posts) {
        foreach ($posts as $post) {
            array_push($stan_mag, $post->ID);
        }
        $args = [
            'status' => array('publish'),
            'include' => $stan_mag
        ];
        $vendor_products = wc_get_products($args);
        foreach ($vendor_products as $key => $product) {
            if ($product->is_type("variable")) {
                foreach ($product->get_children(false) as $child_id) {
                    $variation = wc_get_product($child_id);

                    if (!$variation || !$variation->exists()) {
                        continue;
                    }

                    $list_array[] = array(
                        'ID' => $product->get_id(),
                        'SKU' => $variation->get_sku(),
                    );
                }
            } else {
                $list_array[] = array(
                    'ID' => $product->get_id(),
                    'SKU' => $product->get_sku()
                );
            }
        }
    }

    return $list_array;
}

var_dump(get_products_not_reduced());