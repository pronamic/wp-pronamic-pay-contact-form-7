<?php
/**
 * Amount tag
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\Parser;
use WPCF7_FormTag;
use WPCF7_Validation;

/**
 * Amount tag class
 */
class AmountTag {
	/**
	 * Form tag.
	 */
	const TAG = 'pronamic_pay_amount';

	/**
	 * Amount constructor.
	 */
	public function __construct() {
		\wpcf7_add_form_tag( self::TAG, [ $this, 'handler' ], true );
		\wpcf7_add_form_tag( self::TAG . '*', [ $this, 'handler' ], true );

		\add_filter( 'wpcf7_validate_' . self::TAG, [ $this, 'validate' ], 10, 2 );
		\add_filter( 'wpcf7_validate_' . self::TAG . '*', [ $this, 'validate' ], 10, 2 );
		\add_filter( 'wpcf7_messages', [ $this, 'messages' ] );
	}

	/**
	 * Form tag handler.
	 *
	 * @param WPCF7_FormTag $tag Form tag.
	 * @return string
	 */
	public function handler( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$error = \wpcf7_get_validation_error( $tag->name );

		$class = \wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

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
			'size'     => $tag->get_size_option( '8' ),
			'tabindex' => $tag->get_option( 'tabindex', 'signed_int', true ),
			'type'     => 'text',
			'value'    => \wpcf7_get_hangover( $tag->name, $default_value ),
		];

		if ( $tag->has_option( 'readonly' ) ) {
			$attributes['readonly'] = 'readonly';
		}

		$html = \sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input %2$s>%3$s</span>',
			\sanitize_html_class( $tag->name ),
			\wpcf7_format_atts( $attributes ),
			$error
		);

		return $html;
	}

	/**
	 * Parse value.
	 *
	 * @param string $value Value to parse.
	 * @return Money|null
	 */
	public static function parse_value( $value ) {
		$parser = new Parser();

		try {
			$amount = $parser->parse( $value );
		} catch ( \Exception $e ) {
			return null;
		}

		return $amount;
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

		// Parse input.
		$amount = self::parse_value( $value );

		if ( null === $amount ) {
			$result->invalidate( $tag, \wpcf7_get_message( 'invalid_pronamic_pay_amount' ) );
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
				'invalid_pronamic_pay_amount' => [
					'description' => __( 'Input amount is invalid.', 'pronamic_ideal' ),
					'default'     => __( 'The input amount is invalid.', 'pronamic_ideal' ),
				],
			]
		);
	}
}
