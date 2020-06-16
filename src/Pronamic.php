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
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Core\Util as Core_Util;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Payments\PaymentLines;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use WPCF7_ContactForm;
use WPCF7_FormTagsManager;

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
	 * Get Pronamic payment from Contact Form 7 form.
	 *
	 * @param WPCF7_ContactForm $form Contact Form 7 form object.
	 *
	 * @return Payment
	 */
	public static function get_payment( WPCF7_ContactForm $form ) {
		$payment = new Payment();

		// Contact Form 7.
		$amount = null;
		$payment_method = null;
		$issuer = null;

		$tags_manager = WPCF7_FormTagsManager::get_instance();

		$scanned_tags = $tags_manager->get_scanned_tags();

		foreach ( $scanned_tags as $tag ) {
			switch ( $tag->type ) {
				case Tags\AmountTag::TAG:
					$amount = Tags\AmountTag::get_value( $tag->name );

					break;
				case Tags\PaymentMethodTag::TAG:
					$payment_method = Tags\PaymentMethodTag::get_value( $tag->name );

					break;
				/*
				 case Tags\IssuerTag::TAG:
					$issuer = Tags\IssuerTag::get_value( $tag->name );

					break;
				*/
			}
		}

		if ( null === $amount ) {
			return null;
		}

		$entry_id = \uniqid();

		// Title.
		$title = sprintf(
			/* translators: %s: payment data title */
			__( 'Payment for %s', 'pronamic_ideal' ),
			sprintf(
				/* translators: %s: order id */
				__( 'Contact Formk 7 Entry %s', 'pronamic_ideal' ),
				$entry_id
			)
		);

		$payment->title       = $title;
		$payment->description = sprintf(
			/* translators: %s: entry id */
			__( 'Payment for %s', 'pronamic_ideal' ),
			$entry_id
		);
		$payment->source      = 'contact-form-7';
		$payment->source_id   = $entry_id;
		$payment->method      = $payment_method;
		$payment->issuer      = null;

		/*
		 * Totals.
		 */
		$payment->set_total_amount(
			new TaxedMoney(
				$amount->get_value()
			)
		);

		/*
		 * Return.
		 */
		return $payment;

		// @todo Set additional payment details.

		// Contact.
		$contact_name = new ContactName();
		$contact_name->set_first_name( $memberpress_user->first_name );
		$contact_name->set_last_name( $memberpress_user->last_name );

		$customer = new Customer();
		$customer->set_name( $contact_name );
		$customer->set_email( $memberpress_user->user_email );
		$customer->set_user_id( $memberpress_user->ID );

		$payment->set_customer( $customer );

		/*
		 * Address.
		 * @link https://github.com/wp-premium/memberpress-business/blob/1.3.36/app/models/MeprUser.php#L1191-L1216
		 */
		$address = new Address();

		$address->set_name( $contact_name );

		$address_fields = array(
			'one'     => 'set_line_1',
			'two'     => 'set_line_2',
			'city'    => 'set_city',
			'state'   => 'set_region',
			'zip'     => 'set_postal_code',
			'country' => 'set_country_code',
		);

		foreach ( $address_fields as $field => $function ) {
			$value = $memberpress_user->address( $field, false );

			if ( empty( $value ) ) {
				continue;
			}

			call_user_func( array( $address, $function ), $value );
		}

		$payment->set_billing_address( $address );
		$payment->set_shipping_address( $address );

		/*
		 * Subscription.
		 * @link https://github.com/wp-premium/memberpress-business/blob/1.3.36/app/models/MeprTransaction.php#L603-L618
		 */
		$payment->subscription = self::get_subscription( $form );

		if ( $payment->subscription ) {
			$payment->subscription_source_id = $form->subscription_id;

			if ( $memberpress_subscription->in_trial() ) {
				$payment->set_total_amount(
					new TaxedMoney(
						$memberpress_subscription->trial_amount,
						MemberPress::get_currency(),
						null, // Calculate tax value based on tax percentage.
						$form->tax_rate
					)
				);
			}
		}

		/*
		 * Lines.
		 */
		$payment->lines = new PaymentLines();

		$line = $payment->lines->new_line();

		$line->set_id( $memberpress_product->ID );
		$line->set_name( $memberpress_product->post_title );
		$line->set_quantity( 1 );
		$line->set_unit_price( $payment->get_total_amount() );
		$line->set_total_amount( $payment->get_total_amount() );
		$line->set_product_url( get_permalink( $memberpress_product->ID ) );
	}

	/**
	 * Get Pronamic subscription from Contact Form 7 form.
	 *
	 * @param WPCF7_ContactForm $form Contact Form 7 form object.
	 *
	 * @return Subscription|null
	 */
	public static function get_subscription( WPCF7_ContactForm $form ) {
		$memberpress_product = $form->product();

		if ( $memberpress_product->is_one_time_payment() ) {
			return null;
		}

		$memberpress_subscription = $form->subscription();

		if ( ! $memberpress_subscription ) {
			return false;
		}

		// New subscription.
		$subscription                  = new Subscription();
		$subscription->interval        = $memberpress_product->period;
		$subscription->interval_period = Core_Util::to_period( $memberpress_product->period_type );

		// Frequency.
		$limit_cycles_number = (int) $memberpress_subscription->limit_cycles_num;

		if ( $memberpress_subscription->limit_cycles && $limit_cycles_number > 0 ) {
			$subscription->frequency = $limit_cycles_number;
		}

		// Amount.
		$subscription->set_total_amount(
			new TaxedMoney(
				$form->total,
				MemberPress::get_currency()
			)
		);

		return $subscription;
	}
}
