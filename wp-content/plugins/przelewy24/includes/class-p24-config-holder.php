<?php
/**
 * File that define P24_Config_Holder class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Simple class that hold config.
 *
 * The active currency and style of booleans are unknown to this class.
 * The accessor is external.
 */
class P24_Config_Holder {
	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Identification of merchant.
	 *
	 * @var string
	 */
	public $merchant_id;

	/**
	 * Identification of store.
	 *
	 * It may be the same as $merchant_id.
	 *
	 * @var string
	 */
	public $shop_id;

	/**
	 * Salt or CRC key.
	 *
	 * @var string
	 */
	public $salt;

	/**
	 * Mode of operation.
	 *
	 * @var string
	 */
	public $p24_operation_mode;

	/**
	 * Longer description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Key to API.
	 *
	 * @var string
	 */
	public $p24_api;

	/**
	 * Methods to put on peyment selection page.
	 *
	 * @var string
	 */
	public $p24_paymethods_super_first;

	/**
	 * Activate the Onclick.
	 *
	 * @var bool
	 */
	public $p24_oneclick;

	/**
	 * Option to pay in shop via card.
	 *
	 * @var bool
	 */
	public $p24_payinshop;

	/**
	 * Option to accept p24 terms in shop.
	 *
	 * @var bool
	 */
	public $p24_acceptinshop;

	/**
	 * Select payment methods in shop on checkout page.
	 *
	 * @var bool
	 */
	public $p24_show_methods_checkout;

	/**
	 * Select payment methods in shop on confirmation page.
	 *
	 * @var bool
	 */
	public $p24_show_methods_confirmation;

	/**
	 * User graphic list of pay options.
	 *
	 * @var bool
	 */
	public $p24_graphics;

	/**
	 * Comma separated list of promoted pay options.
	 *
	 * @var string
	 */
	public $p24_paymethods_first;

	/**
	 * Comma separated list of additional methods.
	 *
	 * @var string
	 */
	public $p24_paymethods_second;

	/**
	 * Wait for transaction result.
	 *
	 * @var bool
	 */
	public $p24_wait_for_result;

	/**
	 * Use special statuses for orders.
	 *
	 * @var bool
	 */
	public $p24_use_special_status;

	/**
	 * Special pending status.
	 *
	 * @var string
	 */
	public $p24_custom_pending_status;

	/**
	 * Special processing status.
	 *
	 * @var string
	 */
	public $p24_custom_processing_status;

	/**
	 * Enable selected currency.
	 *
	 * @var bool
	 */
	public $sub_enabled;

	/**
	 * Enable P24NOW promoted.
	 *
	 * @var bool
	 */
	public $p24_custom_promote_p24;

	/**
	 * Enable p24 alternative button.
	 *
	 * @var bool
	 */
	public $p24_add_to_alternative_button;
}
