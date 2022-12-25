<?php
/**
 * File that define P24_Subscription_Admin class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Subscription_Admin
 */
class P24_Subscription_Admin {

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * The constructor.
	 *
	 * @param P24_Core $plugin_core The P24_Core instance.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Try serve csv.
	 */
	public function try_serve_csv() {
		/* User credenitals should be checked and high enough. */
		wp_verify_nonce( null );
		if ( isset( $_GET['p24'] ) && isset( $_GET['subscription_csv'] ) ) {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=p24_subscriptions.csv' );
			$header = array(
				__( 'Użytkownik' ),
				__( 'E-Mail' ),
				__( 'Ważna do' ),
				__( 'Subskrypcje' ),
			);
			$list   = P24_Subscription_Db::get_active_list();
			$stdout = fopen( 'php://output', 'w' );
			fputcsv( $stdout, $header );
			foreach ( $list as $one ) {
				$array = (array) $one;
				fputcsv( $stdout, $array );
			}
			exit();
		}
	}

	/**
	 * Add P24 subscription tab.
	 *
	 * @param array $tabs Provided tabs.
	 * @return array Extended tabs.
	 */
	public function add_subscription_tab( $tabs ) {
		$new_tabs = array(
			/* This one has to be first. */
			'general'          => $tabs['general'],
			'p24_subscription' => array(
				'label'  => __( 'Ustawienia subskrypcji' ),
				'target' => 'p24_subscription_options',
				'class'  => ( 'show_if_p24_subscription' ),
			),
		);

		return $new_tabs + $tabs;
	}

	/**
	 * Parse cancellation request.
	 */
	public static function parse_cancellation_request() {
		$ok = wp_verify_nonce( isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : null, 'p24_subscription' );
		if ( $ok ) {
			self::parse_cancellation_request_internal( $_POST );
		}
	}

	/**
	 * Parse cancellation request internal.
	 *
	 * @param array $data The post data.
	 */
	private static function parse_cancellation_request_internal( $data ) {
		if ( ! isset( $data['displayed'] ) ) {
			/* Nothing to do. */
			return;
		}
		$a = $data['displayed'];
		if ( isset( $data['preserve'] ) ) {
			$b = $data['preserve'];
		} else {
			$b = array();
		}
		$todel = array_diff( $a, $b );
		foreach ( $todel as $one ) {
			$one = (int) $one;
			if ( $one ) {
				$ok = P24_Subscription_Db::end_subscription( $one );
				if ( $ok ) {
					$data = P24_Subscription_Db::get_extended_data_for_single( $one );
					self::send_email_about_cancellation( $data );
				}
			}
		}
	}

	/**
	 * Send email about cancellation.
	 *
	 * @param array $data Data from database.
	 */
	private static function send_email_about_cancellation( $data ) {
		if ( ! $data ) {
			return;
		}

		$mailer  = WC()->mailer();
		$subject = __( 'Twoja subskrypcja została anulowana', 'przelewy24' );
		$args    = array(
			'email_heading'      => $subject,
			'subscription_title' => $data['subscription_title'],
			'email'              => $mailer,
		);
		$headers = 'Content-Type: text/html';
		$dir     = __DIR__ . '/../../emails/';
		$content = wc_get_template_html( 'subscription-cancellation.php', $args, $dir, $dir );
		$mailer->send( $data['user_email'], $subject, $content, $headers );
	}

	/**
	 * Bind events.
	 */
	public function bind_core_events() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_subscription_tab' ) );
		add_action( 'admin_init', array( $this, 'try_serve_csv' ) );
	}
}
