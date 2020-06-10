<?php
/**
 * Extension
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use GFUserData;
use Pronamic\WordPress\Pay\AbstractPluginIntegration;
use Pronamic\WordPress\Pay\Extensions\ContactForm7\Tags\AmountTag;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;

/**
 * Title: WordPress pay extension Contact Form 7 extension
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.0
 * @since   1.0.0
 */
class Extension extends AbstractPluginIntegration {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'contact-form-7';

	/**
	 * Construct Contact Form 7 plugin integration.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'    => __( 'Contact Form 7', 'pronamic_ideal' ),
				'version' => '1.0.0',
			)
		);

		// Dependencies.
		$dependencies = $this->get_dependencies();

		$dependencies->add( new ContactForm7Dependency() );
	}

	/**
	 * Setup plugin integration.
	 *
	 * @return void
	 */
	public function setup() {
		add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( $this, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_subscription_source_description_' . self::SLUG, array( $this, 'subscription_source_description' ), 10, 2 );

		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( $this, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_subscription_source_text_' . self::SLUG, array( $this, 'subscription_source_text' ), 10, 2 );

		// Actions.
		add_action( 'wpcf7_init', array( $this, 'init' ) );
	}

	/**
	 * Initialize
	 */
	public function init() {
		// Admin.
		if ( is_admin() ) {
			new Admin();
		}

		// Actions.
		\add_action( 'wpcf7_submit', array( $this, 'handle_submit' ), 99, 2 );

		// Filters.
		\add_filter( 'pronamic_pay_subscription_amount_editable_' . self::SLUG, '__return_true' );

		$this->register_tags();
	}

	/**
	 * Register tags.
	 */
	public function register_tags() {
		// Amount tag.
		$amount = new AmountTag();

		// Payment method tag.
		//$payment_method = new PaymentMethodTag();
	}

	/**
	 * Handle submit.
	 *
	 * @param $form
	 * @param array $result Result.
	 *
	 * @return void
	 */
	public function handle_submit( $form, $result ) {
		// Return early for invalid fields.
		if ( isset( $result['invalid_fields'] ) && ! empty( $result['invalid_fields'] ) ) {
			return;
		}

		// Get gateway.
		$config_id = \get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return false;
		}

		// Start payment.
		$payment = Pronamic::get_payment( $form );

		// Return on invalid payment.
		if ( null === $payment ) {
			return;
		}

		$payment->config_id = $config_id;
		$payment->method    = null;
		$payment->issuer    = null;

		$error = null;

		try {
			$payment = Plugin::start_payment( $payment );
		} catch ( \Exception $e ) {
			echo \esc_html( Plugin::get_default_error_message() );
			echo \esc_html( $e->getMessage() );

			exit;
		}

		// Redirect.
		$gateway->redirect( $payment );
	}

	/**
	 * Source text.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function source_text( $text, Payment $payment ) {
		return __( 'Contact Form 7', 'pronamic_ideal' );
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Description.
	 * @param Payment $payment     Payment.
	 *
	 * @return string
	 */
	public function source_description( $description, Payment $payment ) {
		return __( 'Contact Form 7 Entry', 'pronamic_ideal' );
	}

	/**
	 * Subscription source text.
	 *
	 * @param string       $text         Source text.
	 * @param Subscription $subscription Subscription.
	 *
	 * @return string
	 */
	public function subscription_source_text( $text, Subscription $subscription ) {
		return __( 'Contact Form 7', 'pronamic_ideal' );
	}

	/**
	 * Subscription source description.
	 *
	 * @param string       $description  Description.
	 * @param Subscription $subscription Subscription.
	 *
	 * @return string
	 */
	public function subscription_source_description( $description, Subscription $subscription ) {
		return __( 'Contact Form 7 Entry', 'pronamic_ideal' );
	}
}
