<?php
/**
 * File that define P24_Icon_Html_Generator class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * The class for bank icons.
 */
class P24_Icon_Html_Generator {

	/**
	 * Icon generator set.
	 *
	 * @var P24_Icon_Svg_Generator $svg_gnerator
	 */
	private $svg_gnerator;

	/**
	 * P24_Icon_Html_Generator constructor.
	 *
	 * @param P24_Icon_Svg_Generator $svg_generator SVG generator.
	 */
	public function __construct( P24_Icon_Svg_Generator $svg_generator ) {
		$this->svg_gnerator = $svg_generator;
	}

	/**
	 * Get bank icon html.
	 *
	 * @param int    $bank_id Bank id.
	 * @param string $bank_name Bank name.
	 * @param string $text Bank description.
	 * @param string $cc_id CC id data.
	 * @param string $class Additional class.
	 * @param string $onclick On click JavaScript code.
	 * @param bool   $with_input Generate additional form.
	 * @return string
	 */
	public function get_bank_html( $bank_id, $bank_name, $text = '', $cc_id = '', $class = '', $onclick = '', $with_input = false ) {
		$bank_id    = sanitize_text_field( $bank_id );
		$bank_name  = sanitize_text_field( $bank_name );
		$text       = sanitize_text_field( $text );
		$cc_id      = sanitize_text_field( $cc_id );
		$class      = sanitize_text_field( $class );
		$onclick    = sanitize_text_field( $onclick );
		$with_input = sanitize_text_field( $with_input );
		$css        = $this->get_logo_css( $bank_id, true );

		return '<a class="bank-box bank-item ' . $class . '" data-id="' . $bank_id . '" data-cc="' . $cc_id . '" onclick="' . $onclick . '">' .
			( empty( $cc_id ) ? '' : '<span class="removecc" ' .
				' title="' . __( 'Usuń zapamiętaną kartę', 'przelewy24' ) . ' ' . $bank_name . ' ' . $text . '" ' .
				' onclick="arguments[0].stopPropagation(); if (confirm(\'' . __( 'Czy na pewno?', 'przelewy24' ) . '\')) removecc(' . $cc_id . ')"></span>' ) .
			'<div class="bank-logo" style="' . $css . '">' .
			( empty( $text ) ? '' : "<span>{$text}</span>" ) .
			'</div><div class="bank-name">' . $bank_name . '</div>' . ( $with_input ? "<input style='display:none;' name='selected_banks[]' value='" . $bank_id . "' type='checkbox'/>" : '' ) . '</a>';
	}

	/**
	 * Get pay now html due to lack of proper template machine.
	 *
	 * @param int    $bank_id The id of a bank.
	 * @param string $bank_name The name of a bank.
	 *
	 * @return string
	 */
	public function get_pay_now_html( $bank_id = 266, $bank_name = '' ) {
		$css  = $this->get_logo_css( $bank_id, true );
		$logo = sprintf( '<div class="align-center bank-logo"  style="%s"></div>', $css );
		$text = sprintf( '<div class="bank-name ">%s</div>', $bank_name );

		return sprintf(
			'<a class="box-wrapper" data-id="%s"><div id="p24-now-box" class="%s">%s%s</div></a>',
			$bank_id,
			'payments-extra-promoted extra-promoted-box text-center',
			$logo,
			$text
		);
	}

	/**
	 * Get logo CSS.
	 *
	 * @param int  $bank_id The id of a bank.
	 * @param bool $mobile True if a mobile  icon is requested.
	 * @return string
	 */
	private function get_logo_css( $bank_id, $mobile ) {
		$bg_image = $this->svg_gnerator->get_icon( $bank_id, $mobile );
		if ( $bg_image ) {
			$css = "background-image: url('$bg_image')";
		} else {
			$css = '';
		}

		return $css;
	}
}
