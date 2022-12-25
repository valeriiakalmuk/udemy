<?php
/**
 * Przelewy24 comunication class
 *
 * @author Przelewy24 Sp. z o.o.
 * @copyright DialCom24 Sp. z o.o.
 * @version 1.1
 * @since 2014-04-29
 */

/**
 *
 * Communication protol version
 * @var double
 */
define('P24_VERSION', '3.2');
if (class_exists('Przelewy24Class', false)!=true) {
class Przelewy24Class {
    /**
     * Config.
     * @var P24_Config_Accessor
     */
    private $config;

    /**
     * Live system URL address
     * @var string
     */
    private static $hostLive    = 'https://secure.przelewy24.pl/';
    /**
     * Sandbox system URL address
     * @var string
     */
    private static $hostSandbox = 'https://sandbox.przelewy24.pl/';
    /**
     * Use Live (false) or Sandbox (true) enviroment
     * @var bool
     */
    private $testMode           = false;
    /**
     * Merchant Id
     * @var int
     */
    private $merchantId         = 0;
    /**
     * Merchant posId
     * @var int
     */
    private $posId              = 0;
    /**
     * API Key (from P24 panel)
     * @var string
     */
    private $api                = '';
    /**
     * Array of POST data
     * @var array
     */
    private $postData           = array();

    /**
     * The class to validate messages.
     *
     * @var P24_Message_Validator
     */
    private $message_validator;

    /**
     * Przelewy24Class constructor.
     * @param P24_Config_Accessor $config
     */
    public function __construct( P24_Config_Accessor $config ) {
        $this->config = clone $config;
        $this->config->access_mode_to_strict();

        #TODO Refactor this out.
        $this->message_validator = new P24_Message_Validator();

        $config->access_mode_to_strict();
        $this->posId      = (int) trim( $this->config->get_shop_id() );
        $this->merchantId = (int) trim( $this->config->get_merchant_id() );
        if ($this->merchantId === 0) {
			$this->merchantId = $this->posId;
		}
        $this->testMode = $this->config->is_p24_operation_mode( 'sandbox' );

        $this->addValue('p24_merchant_id', $this->merchantId);
        $this->addValue('p24_pos_id', $this->posId);
        $this->addValue('p24_api_version', P24_VERSION);

        $this->api = $this->config->get_p24_api();

        return true;
    }

    /**
     * Returns host URL
     */
    public function getHost() {
        return self::getHostStatic($this->testMode);
    }

    public static function getHostStatic($testMode) {
        if ($testMode) return self::$hostSandbox;
        return self::$hostLive;
    }

    /**
     * Add value do post request
     *
     * @param string $name Argument name
     * @param mixed $value Argument value
     */
    public function addValue($name, $value) {
        if ($this->validateField($name, $value))
            $this->postData[$name] = $value;
    }

    /**
     * Redirects or returns URL to a P24 payment screen
     *
     * @param string $token Token
     * @param bool $redirect If set to true redirects to P24 payment screen. If set to false function returns URL to redirect to P24 payment screen
     * @return string URL to P24 payment screen
     */
    public function trnRequest($token, $redirect = true) {
        $url=$this->getHost().'trnRequest/'.$token;
        if($redirect) {
            header('Location: '.$url);
            return '';
        }
        return $url;
    }

	/**
	 * Verify rest transaction.
	 *
	 * @return bool
	 */
	private function trn_verify_rest() {
		$payload = array(
			'merchantId' => (int) $this->merchantId,
			'posId'      => (int) $this->posId,
			'sessionId'  => $this->postData['p24_session_id'],
			'amount'     => (int) $this->postData['p24_amount'],
			'currency'   => $this->postData['p24_currency'],
			'orderId'    => (int) $this->postData['p24_order_id'],
		);

		$api_rest = new P24_Rest_Transaction( $this->config );
		return $api_rest->verify_bool( $payload );
	}

    /**
     * @param string $field
     * @param mixed &$value
     * @return boolean
     */
    public function validateField($field, &$value) {
        return $this->message_validator->validate_field($field, $value);
    }

    /**
     * Filter value.
     *
     * @param string           $field The name of field.
     * @param string|float|int $value The value to test.
     * @return bool|string
     */
    private function filterValue($field, $value) {
        return $this->message_validator->filter_value($field, $value);
    }

    /**
     * Check if mandatory fields are set.
     *
     * @param $fieldsArray
     *
     * @return bool
     * @throws Exception
     */
    public static function checkMandatoryFieldsForAction($fieldsArray) {
        $keys = array_keys($fieldsArray);

        static $mandatory=array(
            'p24_merchant_id','p24_pos_id','p24_api_version','p24_session_id','p24_amount',//all
            'p24_currency','p24_description','p24_country','p24_url_return','p24_currency','p24_email');//register/direct

        for ($i=0; $i<count($mandatory); $i++) {
            if(!in_array($mandatory[$i], $keys)) {
                throw new Exception('Field '.$mandatory[$i].' is required for request!');
            }
        }
        return true;
    }

    /**
     * Parse and validate POST response data from Przelewy24
     * @return array|false
     */
    public function parseStatusResponse() {
        if (isset($_POST['p24_session_id'], $_POST['p24_order_id'], $_POST['p24_merchant_id'], $_POST['p24_pos_id'], $_POST['p24_amount'], $_POST['p24_currency'], $_POST['p24_method']/*, $_POST['p24_statement']*/, $_POST['p24_sign'])) {
            $session_id  = $this->filterValue('p24_session_id', $_POST['p24_session_id']);
            $merchant_id = $this->filterValue('p24_merchant_id', $_POST['p24_merchant_id']);
            $pos_id      = $this->filterValue('p24_pos_id', $_POST['p24_pos_id']);
            $order_id    = $this->filterValue('p24_order_id', $_POST['p24_order_id']);
            $amount      = $this->filterValue('p24_amount', $_POST['p24_amount']);
            $currency    = $this->filterValue('p24_currency', $_POST['p24_currency']);
            $method      = $this->filterValue('p24_method', $_POST['p24_method']);

            if ($merchant_id!=$this->merchantId || $pos_id!=$this->posId) return false;

            return array(
                'p24_session_id'  => $session_id,
                'p24_order_id'    => $order_id,
                'p24_amount'      => $amount,
                'p24_currency'    => $currency,
                'p24_method'      => $method,
            );
        }
        return null;
    }

    public function trn_verify_ex_rest( $data = null ) {
        $response = $this->parseStatusResponse();
        if ($response === null) return null;
        elseif ($response) {
            if ($data!=null) {
                foreach ($data as $field => $value) {
                    if ($response[$field]!=$value) return false;
                }
            }
            $this->postData=array_merge($this->postData,$response);

            return $this->trn_verify_rest();
        }
        return false;
    }

	/**
	 * Zwraca listę kanałów płatności, którymi można płacić inną walutą niż PLN
	 */
	public static function getChannelsNonPln() {
		return array(66,92,124,140,145,152,218);
	}

	/**
	 * Zwraca listę kanałów płatności ratalnej
	 */
	public static function getChannelsRaty() {
		return array(72,129,136);
	}

	/**
	 * Zwraca minimalną kwotę dla płatności ratalnych
	 */
	public static function getMinRatyAmount() {
		return 300;
	}

	/**
	 * Zwraca listę kanałów płatności kartą
	 *
	 */
	public static function getChannelsCard(): array {
		return array(140,142,145,218);
	}

    /**
     * Zwraca listę kanałów płatności [id => etykieta,]
     *
     * @param bool $only24at7 płatności, które są w tej chwili aktywne - usuwa z wyników te nienatychmiastowe
     * @param string $currency ogranicza listę metod płatności do dostępnych dla wskazanej waluty
     * @param string $lang Etykiety kanałów płatności w wybranym języku
     * @return bool
     */
    public function availablePaymentMethods($only24at7 = true, $currency = 'PLN', $lang = 'pl')
    {
        if (empty($this->api)) {
            return false;
        }
        $rest_api = new P24_Rest_Common( $this->config );
        $res      = $rest_api->payment_methods( $lang );
        if ( isset( $res['data'] ) ) {
            $banks = $res['data'];
            if ($only24at7) {
                $there_is_218 = false;
                foreach ($banks as $key => $bank) {
                    if (218 === $bank['id']) {
                        $there_is_218 = true;
                    }
                    if (!$bank['status'] || 1000 === $bank['id']) {
                        unset($banks[$key]);
                    }
                }
            }

            if ($currency !== 'PLN') {
                foreach ($banks as $key => $bank) {
                    if (!isset($there_is_218) && 218 === $bank['id']) {
                        $there_is_218 = true;
                    }
                    if (!in_array($bank['id'], $this->getChannelsNonPln())) {
                        unset($banks[$key]);
                    }
                }
                if (!isset($there_is_218)) {
                    $there_is_218 = false;
                }
            }

            if (!isset($there_is_218)) {
                $there_is_218 = false;
                foreach ($banks as $bank) {
                    if (218 === $bank['id']) {
                        $there_is_218 = true;
                        break;
                    }
                }
            }

            // filter method 142 and 145 when there is 218
            if ($there_is_218) {
                foreach ($banks as $key => $bank) {
                    if (in_array($bank['id'], array(142, 145))) {
                        unset($banks[$key]);
                    }
                }
            }

            return $banks;
        }

        return false;
    }

	public function availablePaymentMethodsSimple($only24at7 = true, $currency = 'PLN', $lang = 'pl') {
		$all = $this->availablePaymentMethods($only24at7, $currency, $lang);
		$result = array();
		if (is_array($all) && sizeof($all) > 0) {
			foreach ($all as $item) {
				$result[$item['id']] = $item['name'];
			}
		} else {
			$result = $all;
		}
		return $result;
	}
}
}
