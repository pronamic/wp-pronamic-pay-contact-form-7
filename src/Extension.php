<?php
/**
 * Extension
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Pay\AbstractPluginIntegration;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use WPCF7_ContactForm;
use WPCF7_MailTag;
use WPCF7_Submission;

/**
 * Title: WordPress pay extension Contact Form 7 extension
 * Description:
 * Copyright: 2005-2022 Pronamic
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
	 * Feedback response args.
	 *
	 * @var array<string, string>
	 */
	private $feedback_args;

	/**
	 * Payment.
	 *
	 * @var Payment|null
	 */
	private $payment;

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
		\add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( $this, 'source_description' ), 10, 2 );
		\add_filter( 'pronamic_subscription_source_description_' . self::SLUG, array( $this, 'subscription_source_description' ), 10, 2 );

		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		\add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( $this, 'source_text' ), 10, 2 );
		\add_filter( 'pronamic_subscription_source_text_' . self::SLUG, array( $this, 'subscription_source_text' ), 10, 2 );

		// Actions.
		\add_action( 'wpcf7_init', array( $this, 'init' ) );
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function init() {
		// Actions.
		\add_action( 'wpcf7_before_send_mail', array( $this, 'before_send_mail' ), 10, 3 );
		\add_action( 'wpcf7_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		\add_action( 'wpcf7_mail_sent', array( $this, 'wpcf7_disabled_scripts_redirect' ) );
		\add_action( 'wpcf7_mail_failed', array( $this, 'wpcf7_disabled_scripts_redirect' ) );

		// Filters.
		\add_filter( 'pronamic_pay_subscription_amount_editable_' . self::SLUG, '__return_true' );
		\add_filter( 'wpcf7_collect_mail_tags', array( $this, 'collect_mail_tags' ) );
		\add_filter( 'wpcf7_mail_tag_replaced', array( $this, 'replace_mail_tags' ), 10, 4 );

		$this->register_tags();
	}

	/**
	 * Register tags.
	 *
	 * @return void
	 */
	public function register_tags() {
		// Amount tag.
		new Tags\AmountTag();

		// Payment method tag.
		new Tags\PaymentMethodTag();

		// Issuer tag.
		new Tags\IssuerTag();
	}

	/**
	 * Handle submit, before sending mail.
	 *
	 * @param WPCF7_ContactForm $form       Contact Form 7 form.
	 * @param bool              $abort      Whether or not to abort submission.
	 * @param WPCF7_Submission  $submission Form submission.
	 * @return void
	 */
	public function before_send_mail( WPCF7_ContactForm $form, &$abort, WPCF7_Submission $submission ) {
		// Get gateway.
		$config_id = \get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return;
		}

		try {
			// Start payment.
			$payment = Pronamic::get_submission_payment( $submission );

			// Return on invalid payment.
			if ( null === $payment ) {
				return;
			}

			$payment->config_id = $config_id;

			$payment = Plugin::start_payment( $payment );

			$this->payment = $payment;

			$this->feedback_args = array(
				'status'                    => 'pronamic_pay_redirect',
				'message'                   => __( 'Please wait while redirecting for payment', 'pronamic_ideal' ),
				'pronamic_pay_redirect_url' => $payment->get_pay_redirect_url(),
			);
		} catch ( \Exception $e ) {
			$this->feedback_args = array(
				'status'  => 'pronamic_pay_error',
				'message' => sprintf(
					'%s' . str_repeat( \PHP_EOL, 2 ) . '%s',
					Plugin::get_default_error_message(),
					$e->getMessage()
				),
			);

			$abort = true;
		}

		\add_filter( 'wpcf7_feedback_response', array( $this, 'feedback_response' ), 10, 2 );
	}

	/**
	 * Redirect when loading Contact Form 7 scripts has been disabled.
	 *
	 * @return void
	 */
	public function wpcf7_disabled_scripts_redirect() {
		if ( ! \has_filter( 'wpcf7_load_js' ) ) {
			return;
		}

		$load_js = \apply_filters( 'wpcf7_load_js', true );

		if ( false !== $load_js ) {
			return;
		}

		$feedback_args = $this->feedback_args;

		if ( ! \array_key_exists( 'pronamic_pay_redirect_url', $feedback_args ) ) {
			return;
		}

		\wp_redirect( $feedback_args['pronamic_pay_redirect_url'] );

		exit;
	}

	/**
	 * Feedback response.
	 *
	 * @param array<string, mixed> $response REST API feedback response.
	 * @param array<string, mixed> $result   Form submit result.
	 * @return array<string, string>
	 */
	public function feedback_response( $response, $result ) {
		$response = \wp_parse_args( $this->feedback_args, $response );

		return $response;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		\wp_register_script(
			'pronamic-pay-contact-form-7',
			plugins_url( 'js/dist/payment-form-processor.js', dirname( __FILE__ ) ),
			array(),
			$this->get_version(),
			true
		);

		\wp_enqueue_script( 'pronamic-pay-contact-form-7' );
	}

	/**
	 * Collect mail tags.
	 *
	 * @param string[]|null $mail_tags Mail tags.
	 * @return string[]
	 */
	public function collect_mail_tags( $mail_tags = null ) {
		if ( ! \is_array( $mail_tags ) ) {
			$mail_tags = array();
		}

		$mail_tags = \array_merge(
			$mail_tags,
			array(
				'pronamic_payment_id',
				'pronamic_transaction_id',
			)
		);

		return $mail_tags;
	}

	/**
	 * Replace mail tags.
	 *
	 * @param string        $replaced  Replaced text.
	 * @param string|null   $submitted Submitted value.
	 * @param bool          $html      Whether HTML can be used in replaced text.
	 * @param WPCF7_MailTag $mail_tag  The mail tag.
	 * @return string
	 */
	public function replace_mail_tags( $replaced, $submitted, $html, $mail_tag ) {
		// Default replacements.
		$mail_tags = $this->collect_mail_tags();

		$replacements = \array_fill_keys( \array_values( $mail_tags ), '' );

		// Payment replacements.
		if ( $this->payment instanceof Payment ) {
			$payment = $this->payment;

			$replacements = array(
				'pronamic_payment_id'     => $payment->get_id(),
				'pronamic_transaction_id' => $payment->get_transaction_id(),
			);
		}

		// Replace.
		$tag_name = $mail_tag->tag_name();

		if ( \array_key_exists( $tag_name, $replacements ) ) {
			$replaced = (string) $replacements[ $tag_name ];
		}

		return $replaced;
	}

	/**
	 * Source text.
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
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
	 * @return string
	 */
	public function subscription_source_description( $description, Subscription $subscription ) {
		return __( 'Contact Form 7 Entry', 'pronamic_ideal' );
	}
}
