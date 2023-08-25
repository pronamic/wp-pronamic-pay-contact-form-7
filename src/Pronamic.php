<?php
/**
 * Pronamic
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\MemberPress
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Payments\Payment;
use WPCF7_FormTagsManager;
use WPCF7_Submission;

/**
 * Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.0
 * @since   1.0.0
 */
class Pronamic {
	/**
	 * Get default gateway.
	 *
	 * @return Gateway|null
	 */
	public static function get_default_gateway() {
		$config_id = \get_option( 'pronamic_pay_config_id' );

		$gateway = Plugin::get_gateway( $config_id );

		return $gateway;
	}

	/**
	 * Get submission value.
	 *
	 * @param WPCF7_Submission $submission Submission.
	 * @param string           $type       Type to search for.
	 * @return string|null
	 */
	public static function get_submission_value( WPCF7_Submission $submission, $type ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$result = null;

		$prefixed_type = 'pronamic_pay_' . $type;

		// Hidden fields.
		$hidden_fields = \filter_input( \INPUT_POST, '_wpcf7cf_hidden_group_fields' );

		if ( ! empty( $hidden_fields ) ) {
			$hidden_fields = \json_decode( stripslashes( $hidden_fields ) );
		}

		if ( ! \is_array( $hidden_fields ) ) {
			$hidden_fields = [];
		}

		// @link https://contactform7.com/tag-syntax/
		$tags = WPCF7_FormTagsManager::get_instance()->get_scanned_tags();

		foreach ( $tags as $tag ) {
			// Check if tag base type or name is requested type or tag has requested type as option.
			if ( ! \in_array( $tag->basetype, [ $type, $prefixed_type ], true ) && ! $tag->has_option( $prefixed_type ) && $prefixed_type !== $tag->name ) {
				continue;
			}

			// Check if field is not hidden.
			if ( \in_array( $tag->name, $hidden_fields, true ) ) {
				continue;
			}

			// Submission value.
			$value = $submission->get_posted_string( $tag->name );

			if ( empty( $value ) ) {
				continue;
			}

			switch ( $type ) {
				case 'amount':
					/**
					 * Contact Form 7 concatenates the field option value with user input for free text fields. We
					 * are only interested in the input value as amount.
					 *
					 * @link https://github.com/rocklobster-in/contact-form-7/blob/2cfaa472fa485c6d3366fcdd80701fdaf7f9e425/includes/submission.php#L434-L437
					 */
					if ( \wpcf7_form_tag_supports( $tag->type, 'selectable-values' ) && $tag->has_option( 'free_text' ) ) {
						$values = \WPCF7_USE_PIPE ? $tag->pipes->collect_afters() : $tag->values;

						$last_value = end( $values );

						if ( \str_starts_with( $value, $last_value . ' ' ) ) {
							$value = substr_replace( $value, '', 0, strlen( $last_value . ' ' ) );
						}
					}

					$value = Tags\AmountTag::parse_value( $value );

					// Set parsed value as result or add to existing money result.
					if ( null !== $value ) {
						$result = ( null === $result ? $value : $result->add( $value ) );
					}

					break;
				default:
					$result = $value;
			}

			// Prefer tag with option (`pronamic_pay_email`) over tag name match (e.g. `email`).
			if ( $tag->has_option( $prefixed_type ) ) {
				return $result;
			}
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $result;
	}

	/**
	 * Get Pronamic payment from Contact Form 7 form.
	 *
	 * @param WPCF7_Submission $submission Contact Form 7 form submission.
	 * @return Payment|null
	 */
	public static function get_submission_payment( WPCF7_Submission $submission ) {
		$form = $submission->get_contact_form();

		$payment = new Payment();

		// Check amount.
		$amount = self::get_submission_value( $submission, 'amount' );

		if ( null === $amount ) {
			return null;
		}

		// Check gateway.
		$gateway = self::get_default_gateway();

		if ( null === $gateway ) {
			return null;
		}

		// Check active payment method.
		$payment_method = self::get_submission_value( $submission, 'method' );

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
		$title = sprintf(
			/* translators: %s: payment data title */
			__( 'Payment for %s', 'pronamic_ideal' ),
			sprintf(
				/* translators: %s: order id */
				__( 'Contact Form 7 Entry @ %s', 'pronamic_ideal' ),
				$unique_id
			)
		);

		// Description.
		$description = self::get_submission_value( $submission, 'description' );

		if ( null === $description ) {
			$description = sprintf(
				/* translators: %s: payment number */
				__( 'Payment %s', 'pronamic_ideal' ),
				$unique_id
			);
		}

		// Payment method.
		$issuer = self::get_submission_value( $submission, 'issuer' );

		$payment->title = $title;

		$payment->set_description( $description );
		$payment->set_payment_method( $payment_method );
		$payment->set_meta( 'issuer', $issuer );
		$payment->set_source( 'contact-form-7' );

		// Total amount.
		$payment->set_total_amount( new Money( $amount->get_value() ) );

		// Contact.
		$contact_name = new ContactName();
		$contact_name->set_first_name( self::get_submission_value( $submission, 'first_name' ) );
		$contact_name->set_last_name( self::get_submission_value( $submission, 'last_name' ) );

		$customer = new Customer();
		$customer->set_name( $contact_name );
		$customer->set_email( self::get_submission_value( $submission, 'email' ) );

		$payment->set_customer( $customer );

		/*
		 * Address.
		 */
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
			$billing_value  = self::get_submission_value( $submission, 'billing_address_' . $field );
			$shipping_value = self::get_submission_value( $submission, 'shipping_address_' . $field );
			$address_value  = self::get_submission_value( $submission, 'address_' . $field );

			if ( ! empty( $billing_value ) || ! empty( $address_value ) ) {
				$callback = [ $billing_address, 'set_' . $field ];

				if ( \is_callable( $callback ) ) {
					call_user_func( $callback, empty( $billing_value ) ? $address_value : $billing_value );
				}
			}

			if ( ! empty( $shipping_value ) || ! empty( $address_value ) ) {
				$callback = [ $shipping_address, 'set_' . $field ];

				if ( \is_callable( $callback ) ) {
					call_user_func( $callback, empty( $shipping_value ) ? $address_value : $shipping_value );
				}
			}
		}

		$payment->set_billing_address( $billing_address );
		$payment->set_shipping_address( $shipping_address );

		/*
		 * Return.
		 */
		return $payment;
	}
}
