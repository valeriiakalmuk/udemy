<?php

class Przelewy24Helpers
{
    public static function getBankTxt(&$checkedCounter, $bank_id, $bank_name, $text = '', $cc_id = '', $class = '', $onclick = '')
    {
        $checkedCounter++;

        $bank_id = sanitize_text_field($bank_id);
        $bank_name = sanitize_text_field($bank_name);
        $text = sanitize_text_field($text);
        $cc_id = sanitize_text_field($cc_id);
        $class = sanitize_text_field($class);
        $onclick = sanitize_text_field($onclick);

        return
            '
                    <li onclick="' . $onclick . '">' .
            '<div class="input-box  bank-item" data-id="' . (int)$bank_id . '" data-cc="' . $cc_id . '" data-text="' . $text . '" >' .
            '<label for="przelewy_method_id_' . $bank_id . '-' . $cc_id . '">' .
            '<input id="przelewy_method_id_' . $bank_id . '-' . $cc_id . '" name="payment_method_id" ' .
            ' class="radio" type="radio" ' . ($checkedCounter == 1 ? 'checked="checked"' : '') . ' />' .
            '<span>' . $bank_name . '</span></label>' .
            (empty($cc_id) ? '' : '<span class="removecc" ' .
                ' title="' . __('Usuń zapamiętaną kartę', 'przelewy24') . ' ' . $bank_name . ' ' . $text . '" ' .
                ' onclick="arguments[0].stopPropagation(); if (confirm(\'' . __('Czy na pewno?', 'przelewy24') . '\')) removecc(' . $cc_id . ')"></span>') .
            '</div></li>
                    ';
    }

    public static function getExtraPromotedTxt(&$checkedCounter, $bank_id, $bank_name, $text = '', $cc_id = '', $class = '', $onclick = '')
    {
        $checkedCounter++;

        $bank_id = sanitize_text_field($bank_id);
        $bank_name = sanitize_text_field($bank_name);
        $text = sanitize_text_field($text);
        $cc_id = sanitize_text_field($cc_id);
        $class = sanitize_text_field($class);
        $onclick = sanitize_text_field($onclick);

        return
            '
                    <li onclick="' . $onclick . '">' .
            '<div class="input-box bank-box bank-item" data-id="' . (int)$bank_id . '" data-cc="' . $cc_id . '" data-text="' . $text . '" >' .
            '<label for="przelewy_method_id_' . $bank_id . '-' . $cc_id . '">' .
            '<input id="przelewy_method_id_' . $bank_id . '-' . $cc_id . '" name="payment_method_id" ' .
            ' class="radio" type="radio" ' . ($checkedCounter == 1 ? 'checked="checked"' : '') . ' />' .
            '<span>' . $bank_name . '</span></label>' .
            (empty($cc_id) ? '' : '<span class="removecc" ' .
                ' title="' . __('Usuń zapamiętaną kartę', 'przelewy24') . ' ' . $bank_name . ' ' . $text . '" ' .
                ' onclick="arguments[0].stopPropagation(); if (confirm(\'' . __('Czy na pewno?', 'przelewy24') . '\')) removecc(' . $cc_id . ')"></span>') .
            '</div></li>
                    ';
    }

    public static function checkoutOrderProcessed($order_id, $posted)
    {
        if (empty($_POST) || empty($_POST['selected_banks']) || !is_array($_POST['selected_banks'])) {
            return false;
        }

        return false;
    }

    /**
     * Set custom data.
     *
     * @param string $data_type Data type.
     * @param int $data_id Data id.
     * @param string $key Key.
     * @param string|object|array $value Value.
     * @return bool True on success.
     */
    public static function setCustomData($data_type, $data_id, $key, $value)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_p24_data';
        if ($key != 'oneclick') {
            $wpdb->delete($table_name, ['data_type' => $data_type, 'data_id' => $data_id, 'custom_key' => $key], ['%s', '%d', '%s']);
        }

        if (empty($value)) return false;

        if (is_object($value) || is_array($value)) $value = json_encode($value);

        return (bool) $wpdb->insert($table_name, array(
            'data_type' => $data_type,
            'data_id' => $data_id,
            'custom_key' => $key,
            'custom_value' => $value,
        ), array('%s', '%d', '%s', '%s'));
    }

}
