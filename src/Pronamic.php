<?php
/**
 * Pronamic
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\MemberPress
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\Parser;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Payments\Payment;
use WPCF7_FormTagsManager;
use WPCF7_Submission;

/**
 * Pronamic class
 */
final class Pronamic {
	/**
	 * Get default gateway.
	 *
	 * @return Gateway|null
	 */
	public static function get_default_gateway() {
		$value = \get_option( 'pronamic_pay_config_id' );

		if ( ! \is_numeric( $value ) ) {
			return null;
		}

		$config_id = (int) $value;

		$gateway = Plugin::get_gateway( $config_id );

		return $gateway;
	}

	/**
	 * Get Pronamic payment from Contact Form 7 form.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @param WPCF7_Submission $submission Contact Form 7 form submission.
	 * @return Payment|null
	 */
	public static function get_submission_payment( WPCF7_Submission $submission ) {
		$gateway = self::get_default_gateway();

		if ( null === $gateway ) {
			return null;
		}

		$submission_helper = new SubmissionHelper( $submission );

		// Total.
		$currency = \strtoupper( $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_currency' ) );

		if ( '' === $currency ) {
			$currency = 'EUR';
		}

		$total = new Money( 0, $currency );

		$parser = new Parser();

		$tags = $submission_helper->get_tags_with_basetype_or_name_or_option( 'pronamic_pay_amount' );

		foreach ( $tags as $tag ) {
			$values = $submission_helper->get_values_by_tag( $tag );

			foreach ( $values as $value ) {
				try {
					$amount = $parser->parse( $value );

					$total = $total->add( $amount );
				} catch ( \Exception $e ) {
					continue;
				}
			}
		}

		if ( $total->is_zero() ) {
			return null;
		}

		// Payment.
		$payment = new Payment();

		$payment->set_total_amount( $total );

		// Check active payment method.
		$payment_method = $submission_helper->get_value_by_tag_basetype_or_name_or_option( 'pronamic_pay_method' );

		if ( ! empty( $payment_method ) ) {
			if ( ! PaymentMethods::is_active( $payment_method ) ) {
				$payment_method = strtolower( $payment_method );
			}

			// Check lowercase payment method.
			if ( ! PaymentMethods::is_active( $payment_method ) ) {
				return null;
			}
		}

		$unique_id = \time();

		// Title.
		$title = \sprintf(
			/* translators: %s: payment data title */
			\__( 'Payment for %s', 'pronamic_ideal' ),
			\sprintf(
				/* translators: %s: order id */
				\__( 'Contact Form 7 Entry @ %s', 'pronamic_ideal' ),
				$unique_id
			)
		);

		// Description.
		$description = $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_description' );

		if ( '' === $description ) {
			$description = \sprintf(
				/* translators: %s: payment number */
				\__( 'Payment %s', 'pronamic_ideal' ),
				$unique_id
			);
		}

		// Payment method.
		$issuer = $submission_helper->get_value_by_tag_basetype_or_name_or_option( 'pronamic_pay_issuer' );

		$payment->title = $title;

		$payment->set_description( $description );
		$payment->set_payment_method( $payment_method );
		$payment->set_meta( 'issuer', $issuer );
		$payment->set_source( 'contact-form-7' );

		/**
		 * Contact Form 7 form ID.
		 * 
		 * @link https://github.com/pronamic/wp-pronamic-pay-contact-form-7/issues/9
		 * @link https://github.com/rocklobster-in/contact-form-7/blob/2f278f2de975141a152e62dcf036a86533f38151/includes/submission.php#L244-L251
		 * @link https://github.com/rocklobster-in/contact-form-7/blob/2f278f2de975141a152e62dcf036a86533f38151/includes/contact-form.php#L388-L395
		 */
		$payment->set_meta( 'contact_form_7_form_id', $submission->get_contact_form()->id() );
		$payment->set_meta( 'contact_form_7_form_hash', $submission->get_contact_form()->hash() );

		// Contact.
		$contact_name = new ContactName();
		$contact_name->set_first_name( $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_first_name' ) );
		$contact_name->set_last_name( $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_last_name' ) );

		$customer = new Customer();
		$customer->set_name( $contact_name );
		$customer->set_email( $submission_helper->get_value_by_tag_name_or_option_or_basetype( 'pronamic_pay_email', 'email' ) );

		$payment->set_customer( $customer );

		// Address.
		$address = new Address();

		$address->set_name( $contact_name );

		$billing_address  = clone $address;
		$shipping_address = clone $address;

		$address_fields = [
			'line_1',
			'line_2',
			'city',
			'region',
			'postal_code',
			'country_code',
			'company_name',
			'coc_number',
		];

		foreach ( $address_fields as $field ) {
			$billing_value  = $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_billing_address_' . $field );
			$shipping_value = $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_shipping_address_' . $field );
			$address_value  = $submission_helper->get_value_by_tag_name_or_option( 'pronamic_pay_address_' . $field );

			if ( ! empty( $billing_value ) || ! empty( $address_value ) ) {
				$callback = [ $billing_address, 'set_' . $field ];

				if ( \is_callable( $callback ) ) {
					\call_user_func( $callback, empty( $billing_value ) ? $address_value : $billing_value );
				}
			}

			if ( ! empty( $shipping_value ) || ! empty( $address_value ) ) {
				$callback = [ $shipping_address, 'set_' . $field ];

				if ( \is_callable( $callback ) ) {
					\call_user_func( $callback, empty( $shipping_value ) ? $address_value : $shipping_value );
				}
			}
		}

		$payment->set_billing_address( $billing_address );
		$payment->set_shipping_address( $shipping_address );

		return $payment;
	}
}
