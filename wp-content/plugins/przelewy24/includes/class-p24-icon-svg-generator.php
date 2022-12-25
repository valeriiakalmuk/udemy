<?php
/**
 * File that define P24_Icon_Svg_Generator class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * The class for extra gateway.
 */
class P24_Icon_Svg_Generator {

	/**
	 * Icon_set.
	 *
	 * @var array|null
	 */
	private $icon_set = null;

	/**
	 * Config.
	 *
	 * @var P24_Config_Accessor
	 */
	private $config;

	/**
	 * P24_Icon_Svg_Generator constructor.
	 *
	 * @param P24_Config_Accessor $config A valid config.
	 */
	public function __construct( P24_Config_Accessor $config ) {
		$this->icon_set = null;
		$this->config   = clone $config;
		$this->config->access_mode_to_strict();
	}

	/**
	 * Generate set.
	 */
	public function generate_set() {
		$this->icon_set = array();
			$rest_api   = new P24_Rest_Common( $this->config );
			$res        = $rest_api->payment_methods( 'pl' );
		if ( isset( $res['data'] ) ) {
			foreach ( $res['data'] as $row ) {
				$this->icon_set[ $row['id'] ] = array(
					'base'   => $row['imgUrl'],
					'mobile' => $row['mobileImgUrl'],
				);
			}
		}

		ksort( $this->icon_set, SORT_NUMERIC );
	}

	/**
	 * Get icon.
	 *
	 * @param  int  $id Id of payment method.
	 * @param bool $mobile True for mobile version.
	 * @return string|null
	 */
	public function get_icon( $id, $mobile ) {
		if ( ! isset( $this->icon_set ) ) {
			$this->generate_set();
		}

		$id = (int) $id;

		if ( array_key_exists( $id, $this->icon_set ) ) {
			$type = $mobile ? 'mobile' : 'base';
			return $this->icon_set[ $id ][ $type ];
		} else {
			return null;
		}

	}

	/**
	 * Get_all.
	 *
	 * @return array
	 */
	public function get_all() {
		if ( ! isset( $this->icon_set ) ) {
			$this->generate_set();
		}

		return $this->icon_set;
	}
}
