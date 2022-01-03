<?php
/**
 * Pronamic
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
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
use WPCF7_Pipes;
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
	 * @param string $type Type to search for.
	 * @return mixed
	 */
	public static function get_submission_value( $type ) {
		$result = null;

		$prefixed_type = 'pronamic_pay_' . $type;

		// Hidden fields.
		$hidden_fields = \filter_input( \INPUT_POST, '_wpcf7cf_hidden_group_fields' );

		if ( ! empty( $hidden_fields ) ) {
			$hidden_fields = \json_decode( stripslashes( $hidden_fields ) );
		}

		if ( ! \is_array( $hidden_fields ) ) {
			$hidden_fields = array();
		}

		// @link https://contactform7.com/tag-syntax/
		$tags = WPCF7_FormTagsManager::get_instance()->get_scanned_tags();

		foreach ( $tags as $tag ) {
			// Check if tag base type or name is requested type or tag has requested type as option.
			if ( ! \in_array( $tag->basetype, array( $type, $prefixed_type ), true ) && ! $tag->has_option( $prefixed_type ) && $prefixed_type !== $tag->name ) {
				continue;
			}

			// Check if field is not hidden.
			if ( \in_array( $tag->name, $hidden_fields, true ) ) {
				continue;
			}

			$value = trim( \filter_input( \INPUT_POST, $tag->name, \FILTER_SANITIZE_STRING ) );

			if ( 'checkbox' === $tag->basetype ) {
				$value = \filter_input( \INPUT_POST, $tag->name, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY );
			}

			// Check empty value.
			if ( empty( $value ) ) {
				continue;
			}

			$values = (array) $value;

			// Loop values.
			foreach ( $values as $value ) {
				/*
				 * Try to get value from piped field values.
				 *
				 * @link https://contactform7.com/selectable-recipient-with-pipes/
				 */
				if ( $tag->pipes instanceof WPCF7_Pipes ) {
					// Make multidimensional array with pipe options by value.
					$options = array();

					$labels = $tag->pipes->collect_befores();

					foreach ( $tag->pipes->collect_afters() as $key => $after ) {
						// Make sure array for value exists.
						if ( ! \array_key_exists( $after, $options ) ) {
							$options[ $after ] = array();
						}

						// Add option to value array.
						$options[ $after ][] = $labels[ $key ];
					}

					// Search for value in options.
					foreach ( $options as $after => $labels ) {
						if ( false !== \array_search( $value, $labels, true ) ) {
							$value = $after;

							break;
						}
					}
				}

				// Parse value.
				switch ( $type ) {
					case 'amount':
						// Handle free text input.
						if ( $tag->has_option( 'free_text' ) && end( $tag->values ) === $value ) {
							$free_text_name = sprintf( '%s_free_text', $tag->name );

							$value = trim( \filter_input( \INPUT_POST, $free_text_name, \FILTER_SANITIZE_STRING ) );
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
			}

			// Prefer tag with option (`pronamic_pay_email`) over tag name match (e.g. `email`).
			if ( $tag->has_option( $prefixed_type ) ) {
				return $result;
			}
		}

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
		$amount = self::get_submission_value( 'amount' );

		if ( null === $amount ) {
			return null;
		}

		// Check gateway.
		$gateway = self::get_default_gateway();

		if ( null === $gateway ) {
			return null;
		}

		// Check active payment method.
		$payment_method = self::get_submission_value( 'method' );

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
		$description = self::get_submission_value( 'description' );

		if ( null === $description ) {
			$description = sprintf(
				/* translators: %s: payment number */
				__( 'Payment %s', 'pronamic_ideal' ),
				$unique_id
			);
		}

		// Payment method.
		$issuer = self::get_submission_value( 'issuer' );

		if ( empty( $payment_method ) && ( null !== $issuer || $gateway->payment_method_is_required() ) ) {
			$payment_method = PaymentMethods::IDEAL;
		}

		$payment->title = $title;

		$payment->set_description( $description );
		$payment->set_payment_method( $payment_method );
		$payment->set_meta( 'issuer', $issuer );
		$payment->set_source( 'contact-form-7' );

		// Total amount.
		$payment->set_total_amount( new Money( $amount->get_value() ) );

		// Contact.
		$contact_name = new ContactName();
		$contact_name->set_first_name( self::get_submission_value( 'first_name' ) );
		$contact_name->set_last_name( self::get_submission_value( 'last_name' ) );

		$customer = new Customer();
		$customer->set_name( $contact_name );
		$customer->set_email( self::get_submission_value( 'email' ) );

		$payment->set_customer( $customer );

		/*
		 * Address.
		 */
		$address = new Address();

		$address->set_name( $contact_name );

		$billing_address  = clone $address;
		$shipping_address = clone $address;

		$address_fields = array(
			'line_1',
			'line_2',
			'city',
			'region',
			'postal_code',
			'country_code',
			'company_name',
			'coc_number',
		);

		foreach ( $address_fields as $field ) {
			$billing_value  = self::get_submission_value( 'billing_address_' . $field );
			$shipping_value = self::get_submission_value( 'shipping_address_' . $field );
			$address_value  = self::get_submission_value( 'address_' . $field );

			if ( ! empty( $billing_value ) || ! empty( $address_value ) ) {
				$callback = array( $billing_address, 'set_' . $field );

				if ( \is_callable( $callback ) ) {
					call_user_func( $callback, empty( $billing_value ) ? $address_value : $billing_value );
				}
			}

			if ( ! empty( $shipping_value ) || ! empty( $address_value ) ) {
				$callback = array( $shipping_address, 'set_' . $field );

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
