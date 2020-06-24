<?php
/**
 * Pronamic
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\MemberPress
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Pay\Core\Gateway;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Payments\Payment;
use WPCF7_ContactForm;
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
		$value = null;

		$prefixed_type = 'pronamic_pay_' . $type;

		// @link https://contactform7.com/tag-syntax/
		$tags = WPCF7_FormTagsManager::get_instance()->get_scanned_tags();

		foreach ( $tags as $tag ) {
			// Check if tag base type equals requested tag or tag has requested option.
			if ( ! \in_array( $tag->basetype, array( $type, $prefixed_type ), true ) && ! $tag->has_option( $prefixed_type ) ) {
				continue;
			}

			$value = trim( \filter_input( \INPUT_POST, $tag->name, \FILTER_SANITIZE_STRING ) );

			// Check empty value.
			if ( empty( $value ) ) {
				$value = null;

				continue;
			}

			/*
			 * Try to get value from piped field values.
			 *
			 * @link https://contactform7.com/selectable-recipient-with-pipes/
			 */
			if ( $tag->pipes instanceof WPCF7_Pipes ) {
				$pipes = \array_combine( $tag->pipes->collect_afters(), $tag->pipes->collect_befores() );

				$pipe_value = \array_search( $value, $pipes );

				if ( false !== $pipe_value ) {
					$value = $pipe_value;
				}
			}

			// Parse value.
			switch ( $type ) {
				case 'amount':
					return Tags\AmountTag::parse_value( $value );

					break;
			}

			// Prefer tag with option (`pronamic_pay_email`) over tag name match (e.g. `email`).
			if ( $tag->has_option( $prefixed_type ) ) {
				return $value;
			}
		}

		return $value;
	}

	/**
	 * Get Pronamic payment from Contact Form 7 form.
	 *
	 * @param WPCF7_Submission $submission Contact Form 7 form submission.
	 *
	 * @return Payment
	 */
	public static function get_submission_payment( WPCF7_Submission $submission ) {
		$form = $submission->get_contact_form();

		$payment = new Payment();

		$amount = self::get_submission_value( 'amount' );

		if ( null === $amount ) {
			return null;
		}

		$gateway = Pronamic::get_default_gateway();

		if ( null === $gateway ) {
			return null;
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
				/* translators: %s: entry id */
				__( 'Payment %s', 'pronamic_ideal' ),
				$unique_id
			);
		}

		// Payment method.
		$payment_method = self::get_submission_value( 'method' );
		$issuer         = self::get_submission_value( 'issuer' );

		if ( empty( $payment_method ) && ( null !== $issuer || $gateway->payment_method_is_required() ) ) {
			$payment_method = PaymentMethods::IDEAL;
		}

		$payment->title       = $title;
		$payment->description = $description;
		$payment->source      = 'contact-form-7';
		$payment->method      = $payment_method;
		$payment->issuer      = $issuer;

		/*
		 * Totals.
		 */
		$payment->set_total_amount(
			new TaxedMoney(
				$amount->get_value()
			)
		);

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

		$billing_address = clone $address;
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
				call_user_func( array( $billing_address, 'set_' . $field ), empty( $billing_value ) ? $address_value : $billing_value );
			}

			if ( ! empty( $shipping_value ) || ! empty( $address_value ) ) {
				call_user_func( array( $shipping_address, 'set_' . $field ), empty( $shipping_value ) ? $address_value : $shipping_value );
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
