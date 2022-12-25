<?php

class WPDesk_PayU_Settings {

	
	const SUPPORTED_CURRENCIES = ['PLN', 'EUR', 'GBP', 'USD', 'DKK', 'NOK', 'SEK'];
	const DEFAULT_CURRENCY     = 'PLN';

	const FORM_FIELD_PAGE_TITLE                                 = 'page_title';
	const FORM_FIELD_GATEWAY_TITLE                              = 'payu_title';
	const FORM_FIELD_GATEWAY_ENABLED                            = 'enabled';
	const FORM_FIELD_GATEWAY_CHECKOUT_TITLE                     = 'title';
	const FORM_FIELD_GATEWAY_CHECKOUT_DESCRIPTION               = 'description';
	const FORM_FIELD_GATEWAY_API_VERSION                        = 'api_version';
	const FORM_FIELD_GATEWAY_TEST_MODE                          = 'testmode';
	const FORM_FIELD_GATEWAY_SANDBOX                            = 'sandbox';
	const FORM_FIELD_GATEWAY_CHECK_SIG                          = 'check_sig';
	const FORM_FIELD_GATEWAY_POS                                = 'pos';
	const FORM_FIELD_GATEWAY_IA_TITLE                           = 'payu_ia_title';
	const FORM_FIELD_GATEWAY_IA_ENABLED                         = 'payu_ia_enabled';
	const FORM_FIELD_GATEWAY_IA_CHECKOUT_TITLE                  = 'ia_title';
	const FORM_FIELD_GATEWAY_IA_CHECKOUT_DESCRIPTION            = 'ia_description';
	const FORM_FIELD_GATEWAY_SUBSCRIPTIONS_TITLE                = 'payu_subscriptions_title';
	const FORM_FIELD_GATEWAY_SUBSCRIPTIONS_ENABLED              = 'payu_subscriptions_enabled';
	const FORM_FIELD_GATEWAY_SUBSCRIPTIONS_CHECKOUT_TITLE       = 'subscriptions_title';
	const FORM_FIELD_GATEWAY_SUBSCRIPTIONS_CHECKOUT_DESCRIPTION = 'subscriptions_description';
	const FORM_FIELD_RETURNS_TITLE                              = 'returns_title';
	const FORM_FIELD_RETURN_ERROR                               = 'return_error';
	const FORM_FIELD_RETURN_OK                                  = 'return_ok';
	const FORM_FIELD_RETURN_REPORTS                             = 'return_reports';

	/**
	 * 
	 * @var array
	 */
	private $settings;

	/**
	 * 
	 * @var array
	 */
	private $pos;

	public function __construct( array $settings ) {
		if( isset($settings[self::FORM_FIELD_GATEWAY_POS]) && is_serialized( $settings[self::FORM_FIELD_GATEWAY_POS] )){
			$settings[self::FORM_FIELD_GATEWAY_POS] = unserialize($settings[self::FORM_FIELD_GATEWAY_POS]);
		}
		$this->settings = $settings;
	}

	public function get_page_title():string
	{
		return strval( $this->settings[self::FORM_FIELD_PAGE_TITLE] ?? '' );
	}

	public function get_gateway_title():string
	{
		return strval( $this->settings[self::FORM_FIELD_GATEWAY_TITLE] ?? '' );
	}

	public function is_gateway_enabled():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_ENABLED]) && $this->settings[self::FORM_FIELD_GATEWAY_ENABLED] === 'yes';
	}

	public function is_ia_gateway_enabled():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_IA_ENABLED]) && $this->settings[self::FORM_FIELD_GATEWAY_IA_ENABLED] === 'yes';
	}

	public function is_subscription_gateway_enabled():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_ENABLED]) && $this->settings[self::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_ENABLED] === 'yes';
	}

	public function get_gateway_checkout_title():string
	{
		return strval($this->settings[self::FORM_FIELD_GATEWAY_CHECKOUT_TITLE] ?? '');
	}

	public function get_gateway_checkout_description():string
	{
		return strval($this->settings[self::FORM_FIELD_GATEWAY_CHECKOUT_DESCRIPTION] ?? '');
	}

	public function is_rest_api():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_API_VERSION]) && 'classic_api' !== $this->settings[self::FORM_FIELD_GATEWAY_API_VERSION];
	}

	public function is_test_mode():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_TEST_MODE]) && 'yes' === $this->settings[self::FORM_FIELD_GATEWAY_TEST_MODE];
	}

	public function is_sandbox():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_SANDBOX]) && 'yes' === $this->settings[self::FORM_FIELD_GATEWAY_SANDBOX];
	}

	public function is_check_sig():bool
	{
		return isset($this->settings[self::FORM_FIELD_GATEWAY_CHECK_SIG]) && 'yes' === $this->settings[self::FORM_FIELD_GATEWAY_CHECK_SIG];
	}

	public function get_pos_from_currency( string $currency ):WPDesk_PayU_Settings_POS
	{
		if( !isset( $this->settings[self::FORM_FIELD_GATEWAY_POS][$currency] ) ){
			throw new RuntimeException( sprintf(__( 'Punkt POS dla wybranej waluty %1$s nie istnieje' ,'woocommerce_payu' ), $currency ));
		}

		if( !isset( $this->pos[$currency] ) ){
			$this->pos[$currency] = new WPDesk_PayU_Settings_POS( $currency, $this->settings[self::FORM_FIELD_GATEWAY_POS][$currency], $this->is_sandbox() );
		}

		return $this->pos[$currency];
	}

	public function is_currency_suported( string $currency ):bool
	{
		return isset( $this->settings[self::FORM_FIELD_GATEWAY_POS][$currency] );
	}

	public function get_gateway_ia_checkout_title():string
	{
		return strval($this->settings[self::FORM_FIELD_GATEWAY_IA_CHECKOUT_TITLE] ?? '');
	}

	public function get_gateway_ia_checkout_description():string
	{
		return strval($this->settings[self::FORM_FIELD_GATEWAY_IA_CHECKOUT_DESCRIPTION] ?? '');
	}

	public function get_gateway_subscriptions_checkout_title():string
	{
		return strval($this->settings[self::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_CHECKOUT_TITLE] ?? '');
	}

	public function get_gateway_subscriptions_checkout_description():string
	{
		return strval($this->settings[self::FORM_FIELD_GATEWAY_SUBSCRIPTIONS_CHECKOUT_DESCRIPTION] ?? '');
	}
	

}
