<?php
/**
 * Payment method tag
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Extensions\ContactForm7\Pronamic;
use WPCF7_FormTag;
use WPCF7_Validation;

/**
 * Payment method tag class
 */
final class PaymentMethodTag {
	/**
	 * Form tag.
	 */
	const TAG = 'pronamic_pay_method';

	/**
	 * Payment method tag constructor.
	 */
	public function __construct() {
		\wpcf7_add_form_tag( self::TAG, [ $this, 'handler' ], true );
		\wpcf7_add_form_tag( self::TAG . '*', [ $this, 'handler' ], true );

		\add_filter( 'wpcf7_validate_' . self::TAG, [ $this, 'validate' ], 10, 2 );
		\add_filter( 'wpcf7_validate_' . self::TAG . '*', [ $this, 'validate' ], 10, 2 );
		\add_filter( 'wpcf7_messages', [ $this, 'messages' ] );

		\add_action( 'wpcf7_admin_init', [ $this, 'add_tag_generator' ], 60 );
	}

	/**
	 * Form tag handler.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @param WPCF7_FormTag $tag Form tag.
	 * @return string
	 */
	public function handler( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$gateway = Pronamic::get_default_gateway();

		if ( null === $gateway ) {
			return '';
		}

		$error = \wpcf7_get_validation_error( $tag->name );

		$class = \wpcf7_form_controls_class( $tag->type, 'wpcf7-select' );

		if ( $error ) {
			$class .= ' wpcf7-not-valid';
		}

		$value = (string) reset( $tag->values );

		$default_value = $tag->get_default_option( $value );
		$default_value = \is_array( $default_value ) ? \implode( ', ', $default_value ) : $default_value;

		$attributes = [
			'class'    => $tag->get_class_option( $class ),
			'id'       => $tag->get_id_option(),
			'name'     => $tag->name,
			'tabindex' => $tag->get_option( 'tabindex', 'signed_int', true ),
			'value'    => \wpcf7_get_hangover( $tag->name, $default_value ),
		];

		if ( $tag->has_option( 'readonly' ) ) {
			$attributes['readonly'] = 'readonly';
		}

		// Payment method options.
		$options = [];

		/*
		 * Search payment method values in tag pipes.
		 *
		 * @link https://contactform7.com/selectable-recipient-with-pipes/
		 */
		$pipes = [];

		if ( $tag->pipes instanceof \WPCF7_Pipes ) {
			$combined = \array_combine( $tag->pipes->collect_afters(), $tag->pipes->collect_befores() );

			if ( false !== $combined ) {
				$pipes = $combined;
			}
		}

		$payment_methods = $gateway->get_payment_methods(
			[
				'status' => [ '', 'active' ],
			]
		);

		foreach ( $payment_methods as $payment_method ) {
			$value = $payment_method->get_id();
			$label = $payment_method->get_name();

			if ( ! empty( $tag->values ) && ! \array_key_exists( $value, $pipes ) ) {
				continue;
			}

			$options[] = \sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				\esc_attr( $value ),
				\selected( $attributes['value'], $value, false ),
				\esc_html( $label )
			);
		}

		$html = \sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
			\sanitize_html_class( $tag->name ),
			\wpcf7_format_atts( $attributes ),
			\implode( '', $options ),
			$error
		);

		return $html;
	}

	/**
	 * Validate field input.
	 *
	 * @param WPCF7_Validation $result Validation result.
	 * @param WPCF7_FormTag    $tag    Form tag.
	 * @return WPCF7_Validation
	 */
	public function validate( $result, $tag ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = array_key_exists( $tag->name, $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST[ $tag->name ] ) ) : '';

		$value = trim( $value );

		// Check required.
		if ( $tag->is_required() && empty( $value ) ) {
			$result->invalidate( $tag, \wpcf7_get_message( 'invalid_required' ) );

			return $result;
		}

		return $result;
	}

	/**
	 * Contact Form 7 messages.
	 *
	 * @link https://github.com/rocklobster-in/contact-form-7/blob/v5.8.4/includes/contact-form-template.php#L219
	 * @param array<string, array{'description': string, 'default': string}> $messages Messages.
	 * @return array<string, array{'description': string, 'default': string}>
	 */
	public function messages( $messages ) {
		return \array_merge(
			$messages,
			[
				'invalid_pronamic_pay_method_required' => [
					'description' => __( 'Payment method required.', 'pronamic_ideal' ),
					'default'     => __( 'The payment method is invalid.', 'pronamic_ideal' ),
				],
			]
		);
	}

	/**
	 * Add tag generator.
	 *
	 * @return void
	 */
	public function add_tag_generator() {
		$tag_generator = \WPCF7_TagGenerator::get_instance();

		$tag_generator->add( self::TAG, \__( 'payment method', 'pronamic_ideal' ), [ $this, 'tag_generator' ] );
	}

	/**
	 * Tag generator.
	 *
	 * @param \WPCF7_ContactForm   $form Contact form.
	 * @param array<string, mixed> $args Arguments.
	 * @return void
	 */
	public function tag_generator( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameter is used in include.
		$form,
		$args
	) {
		require __DIR__ . '/../../views/payment-method-tag-generator.php';
	}
}
