<?php

class WPDesk_PayU_Settings_POS {

	const POS_FIELD_ID                    = 'pos_id';
	const POS_FIELD_MD5_KEY_1             = 'md5_key_1';
	const POS_FIELD_MD5_KEY_2             = 'md5_key_2';
	const POS_FIELD_AUTH_KEY              = 'pos_auth_key';
	const POS_FIELD_CLIENT_ID             = 'client_id';
	const POS_FIELD_CLIENT_SECRET         = 'client_secret';

	const POS_FIELD_SANDBOX_ID            = 'sandbox_pos_id';
	const POS_FIELD_SANDBOX_MD5_KEY_1     = 'sandbox_md5_key_1';
	const POS_FIELD_SANDBOX_MD5_KEY_2     = 'sandbox_md5_key_2';
	const POS_FIELD_SANDBOX_AUTH_KEY      = 'sandbox_pos_auth_key';
	const POS_FIELD_SANDBOX_CLIENT_ID     = 'sandbox_client_id';
	const POS_FIELD_SANDBOX_CLIENT_SECRET = 'sandbox_client_secret';

	/**
	 * 
	 * @var array
	 */
	private $pos;

	/**
	 * 
	 * @var string
	 */
	private $currency;

	/**
	 * 
	 * @var bool
	 */
	private $sandbox;

	public function __construct(string $currency, array $pos, bool $sandbox = false)
	{
		$this->currency = $currency;
		$this->pos      = $pos;
		$this->sandbox  = $sandbox;
	}

	public function get_supported_currency():string
	{
		return $this->currency;
	}

	public function get_pos_id():string
	{
		$result = $this->sandbox? $this->pos[self::POS_FIELD_SANDBOX_ID] : $this->pos[self::POS_FIELD_ID];   
		return strval( $result );
	}

	public function get_md5_key_1():string
	{
		$result = $this->sandbox? $this->pos[self::POS_FIELD_SANDBOX_MD5_KEY_1] : $this->pos[self::POS_FIELD_MD5_KEY_1];   
		return strval( $result );
	}

	public function get_md5_key_2():string
	{
		$result = $this->sandbox? $this->pos[self::POS_FIELD_SANDBOX_MD5_KEY_2] : $this->pos[self::POS_FIELD_MD5_KEY_2];   
		return strval( $result );
	}

	public function get_auth_key():string
	{
		$result = $this->sandbox? $this->pos[self::POS_FIELD_SANDBOX_AUTH_KEY] : $this->pos[self::POS_FIELD_AUTH_KEY];   
		return strval( $result );
	}

	public function get_client_id():string
	{
		$result = $this->sandbox? $this->pos[self::POS_FIELD_SANDBOX_CLIENT_ID] : $this->pos[self::POS_FIELD_CLIENT_ID];   
		return strval( $result );
	}

	public function get_client_secret():string
	{
		$result = $this->sandbox? $this->pos[self::POS_FIELD_SANDBOX_CLIENT_SECRET] : $this->pos[self::POS_FIELD_CLIENT_SECRET];   
		return strval( $result );
	}
	

}
