<?php
/**
 * File that define P24_Status_Decorator class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to add additional order statuses.
 */
class P24_Status_Provider {

	const ADDITIONAL_STATUSES_KEY = 'woocommerce_przelewy24_additional_statuses';

	/**
	 * Adding_error.
	 *
	 * @var string|null
	 */
	private $adding_error;

	/**
	 * Proposed_values.
	 *
	 * @var array|null
	 */
	private $proposed_values;

	/**
	 * Get config for internal use.
	 *
	 * @return array
	 */
	private static function get_config() {
		$data = get_option( self::ADDITIONAL_STATUSES_KEY );
		if ( $data && is_array( $data ) ) {
			return $data;
		} else {
			return array();
		}
	}

	/**
	 * Get formatted config.
	 *
	 * @return array
	 */
	public static function get_formatted_config() {
		$data = self::get_config();
		$ret  = array();
		foreach ( $data as $one ) {
			if ( isset( $one['code'] ) && isset( $one['label'] ) ) {
				$ret[] = $one;
			}
		}

		return $ret;
	}

	/**
	 * Get config for select.
	 *
	 * @param string $base_status Base status.
	 * @return array
	 */
	public static function get_config_for_select( $base_status ) {
		/* The _x function do not accept variables. We have to use this switch. */
		switch ( $base_status ) {
			case 'Pending payment':
				$default = _x( 'Pending payment', 'Order status', 'woocommerce' );
				break;
			case 'Processing':
				$default = _x( 'Processing', 'Order status', 'woocommerce' );
				break;
			default:
				$default = 'No translation for ' . $base_status;
		}
		$data = self::get_formatted_config();
		$ret  = array(
			'' => $default,
		);
		foreach ( $data as $one ) {
			$ret[ $one['code'] ] = $one['label'];
		}

		return $ret;
	}

	/**
	 * Try add new status.
	 *
	 * @param array $status New array with status.
	 */
	public function try_add_new( $status ) {
		if ( ! $status ) {
			$this->adding_error = null;
		} elseif ( ! $status['code'] || ! $status['label'] ) {
			/* Someone has hand crafted request. No need for better description. */
			$this->adding_error    = __( 'Błąd przy dodawaniu nowego statusu.' );
			$this->proposed_values = $status;
		} elseif ( preg_match( '/^wc-/', $status['code'] ) ) {
			$this->adding_error    = __( 'Prefiks wc- dla kodu jest niedozwolony.' );
			$this->proposed_values = $status;
		} elseif ( ! preg_match( '/^[a-z\\-]+$/', $status['code'] ) ) {
			$this->adding_error    = __( 'Kod powinien składać się tylko z małych liter, bez znaków diakrytycznych.' );
			$this->proposed_values = $status;
		} elseif ( preg_match( '/^pending|processing|on-hold|completed|cancelled|refunded|failed$/', $status['code'] ) ) {
			$this->adding_error    = __( 'Kod jest używany wewnętrznie przez WooCommerce, nie można go użyć.' );
			$this->proposed_values = $status;
		} else {
			$data   = self::get_formatted_config();
			$data[] = $status;
			update_option( self::ADDITIONAL_STATUSES_KEY, $data );
		}
	}

	/**
	 * Get additional valid statuses.
	 *
	 * @param string $prefix Optional prefix.
	 * @return array
	 */
	public function get_additional_valid_statuses( $prefix ) {
		$statuses = array();
		$data     = $this->get_config();
		foreach ( $data as $one ) {
			if ( ! isset( $one['code'] ) || ! isset( $one['label'] ) ) {
				continue;
			}

			$statuses[ $prefix . $one['code'] ] = $one['label'];
		}

		return $statuses;
	}

	/**
	 * Get additional valid statuse codes.
	 *
	 * @param string $prefix Optional prefix.
	 * @return array
	 */
	public function get_additional_valid_statuse_codes( $prefix ) {
		$full = $this->get_additional_valid_statuses( $prefix );

		return array_keys( $full );
	}

	/**
	 * Add description for status.
	 *
	 * @param  array $defaults Default WooCommerce statuses.
	 * @return array
	 */
	public function status_description_list( $defaults ) {
		$new = array();

		$data = $this->get_config();
		foreach ( $data as $one ) {
			if ( ! isset( $one['code'] ) || ! isset( $one['label'] ) ) {
				continue;
			}

			$new[ $one['code'] ] = $this->status_description( $one, $defaults );
		}

		return $new;
	}

	/**
	 * Prep_status_description.
	 *
	 * @param array $one One record from P24 status config.
	 * @param array $defaults Default sttuses.
	 * @return array
	 */
	public function status_description( $one, $defaults ) {
		$new          = $defaults['wc-processing'];
		$new['label'] = $one['label'];

		$rx              = '/[^\\<]+(\\<.*)/';
		$new_label_count = array();
		foreach ( $new['label_count'] as $k => $v ) {
			if ( is_string( $v ) && preg_match( $rx, $v, $m ) ) {
				$new_label_count[ $k ] = $one['label'] . ' ' . $m[1];
			} else {
				$new_label_count[ $k ] = $v;
			}
		}
		$new['label_count'] = $new_label_count;

		return $new;
	}

	/**
	 * Get adding error.
	 *
	 * @return string
	 */
	public function get_adding_error() {
		return (string) $this->adding_error;
	}

	/**
	 * Get proposed code if error.
	 *
	 * @return string
	 */
	public function get_proposed_code_if_error() {
		if ( $this->adding_error ) {
			return $this->proposed_values['code'];
		} else {
			return '';
		}
	}

	/**
	 * Get proposed label if error.
	 *
	 * @return string
	 */
	public function get_proposed_label_if_error() {
		if ( $this->adding_error ) {
			return $this->proposed_values['label'];
		} else {
			return '';
		}
	}
}
