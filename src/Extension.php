<?php
/**
 * Extension
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
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
 * Copyright: 2005-2025 Pronamic
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
			[
				'name'    => __( 'Contact Form 7', 'pronamic_ideal' ),
				'version' => '1.0.0',
			]
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
		\add_filter( 'pronamic_payment_source_description_' . self::SLUG, [ $this, 'source_description' ], 10, 2 );

		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		\add_filter( 'pronamic_payment_source_text_' . self::SLUG, [ $this, 'source_text' ], 10, 2 );

		// Actions.
		\add_action( 'wpcf7_init', [ $this, 'init' ] );
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function init() {
		// Actions.
		\add_action( 'wpcf7_before_send_mail', [ $this, 'before_send_mail' ], 10, 3 );
		\add_action( 'wpcf7_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		\add_action( 'wpcf7_submit', [ $this, 'submit' ], 10, 2 );

		// Filters.
		\add_filter( 'wpcf7_collect_mail_tags', [ $this, 'collect_mail_tags' ] );
		\add_filter( 'wpcf7_mail_tag_replaced', [ $this, 'replace_mail_tags' ], 10, 4 );
		\add_filter( 'wpcf7_submission_result', [ $this, 'submission_result' ], 10, 2 );
		\add_filter( 'wpcf7_flamingo_submit_if', [ $this, 'flamingo_submission_statuses' ] );

		// Register tags.
		new Tags\AmountTag();
		new Tags\IssuerTag();
		new Tags\PaymentMethodTag();
	}

	/**
	 * Handle submit, before sending mail.
	 *
	 * @param WPCF7_ContactForm $form       Contact Form 7 form.
	 * @param bool              $abort      Whether to abort submission.
	 * @param WPCF7_Submission  $submission Form submission.
	 * @return void
	 */
	public function before_send_mail( WPCF7_ContactForm $form, &$abort, WPCF7_Submission $submission ) {
		// Get gateway.
		$value = \get_option( 'pronamic_pay_config_id' );

		if ( ! \is_numeric( $value ) ) {
			return;
		}

		$config_id = (int) $value;

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

			$submission->add_result_props(
				[
					'pronamic_pay_payment_id'     => $payment->get_id(),
					'pronamic_pay_transaction_id' => $payment->get_transaction_id(),
					'pronamic_pay_redirect_url'   => $payment->get_pay_redirect_url(),
				]
			);
		} catch ( \Exception $e ) {
			$submission->set_status( 'pronamic_pay_error' );

			$submission->set_response(
				\sprintf(
					'%s' . \str_repeat( \PHP_EOL, 2 ) . '%s',
					Plugin::get_default_error_message(),
					$e->getMessage()
				)
			);

			$abort = true;
		}
	}

	/**
	 * Submission result.
	 *
	 * @link https://github.com/rocklobster-in/contact-form-7/blob/v5.8.4/includes/submission.php#L169-L199
	 * @param array<string, mixed> $result     Submission result.
	 * @param WPCF7_Submission     $submission Submission.
	 * @return array<string, mixed>
	 */
	public function submission_result( array $result, WPCF7_Submission $submission ) {
		if ( \array_key_exists( 'pronamic_pay_redirect_url', $result ) ) {
			$result = \array_merge(
				$result,
				[
					'status'  => 'pronamic_pay_redirect',
					'message' => \__( 'Please wait while redirecting for payment', 'pronamic_ideal' ),
				]
			);
		}

		return $result;
	}

	/**
	 * Filter for which statuses Flamingo should store submissions.
	 *
	 * @param string[] $statuses Statuses.
	 * @return string[]
	 */
	public function flamingo_submission_statuses( array $statuses ): array {
		$statuses[] = 'pronamic_pay_redirect';

		return $statuses;
	}

	/**
	 * Redirect on form submit if Contact Form 7 scripts have been disabled.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @param WPCF7_ContactForm                           $form   Form.
	 * @param array{'pronamic_pay_redirect_url'?: string} $result Submission result.
	 * @return void
	 */
	public function submit( WPCF7_ContactForm $form, $result ) {
		if ( \function_exists( '\wpcf7_load_js' ) && \wpcf7_load_js() ) {
			return;
		}

		if ( \array_key_exists( 'pronamic_pay_redirect_url', $result ) ) {
			\wp_redirect( $result['pronamic_pay_redirect_url'] );

			exit;
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		\wp_register_script(
			'pronamic-pay-contact-form-7',
			plugins_url( 'js/dist/payment-form-processor.js', __DIR__ ),
			[],
			$this->get_version(),
			true
		);

		\wp_enqueue_script( 'pronamic-pay-contact-form-7' );
	}

	/**
	 * Collect mail tags.
	 *
	 * @param string[] $mail_tags Mail tags.
	 * @return string[]
	 */
	public function collect_mail_tags( $mail_tags ) {
		return \array_merge(
			$mail_tags,
			[
				'pronamic_payment_id',
				'pronamic_transaction_id',
			]
		);
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
		$submission = WPCF7_Submission::get_instance();

		if ( null === $submission ) {
			return $replaced;
		}

		$result = $submission->get_result();

		$replacements = [
			'pronamic_payment_id'     => \array_key_exists( 'pronamic_pay_payment_id', $result ) ? $result['pronamic_pay_payment_id'] : '',
			'pronamic_transaction_id' => \array_key_exists( 'pronamic_pay_transaction_id', $result ) ? $result['pronamic_pay_transaction_id'] : '',
		];

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
}
