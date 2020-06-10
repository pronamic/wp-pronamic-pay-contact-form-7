<?php
/**
 * Amount form tag.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7\Tags;

use Pronamic\WordPress\Money\Parser;
use function wpcf7_form_controls_class;
use function wpcf7_format_atts;
use function wpcf7_get_hangover;
use function wpcf7_get_validation_error;
use function wpcf7_support_html5;

/**
 * Amount.
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
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
		\wpcf7_add_form_tag( self::TAG, array( $this, 'handler' ), true );

		// Filters.
		\add_filter( 'wpcf7_validate_' . self::TAG, array( $this, 'validate' ), 10, 2 );
		\add_filter( 'wpcf7_messages', array( $this, 'messages' ) );
	}

	/**
	 * Form tag handler.
	 *
	 * @param object $tag Form tag.
	 *
	 * @return string
	 */
	public function handler( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

		if ( $error ) {
			$class .= ' wpcf7-not-valid';
		}

		$value = (string) reset( $tag->values );

		$attributes = array(
			'class'        => $tag->get_class_option( $class ),
			'id'           => $tag->get_id_option(),
			'name'         => $tag->name,
			'size'         => $tag->get_size_option( '8' ),
			'tabindex'     => $tag->get_option( 'tabindex', 'signed_int', true ),
			'type'         => 'text',
			'value'        => wpcf7_get_hangover( $tag->name, $tag->get_default_option( $value ) ),
		);

		if ( $tag->has_option( 'readonly' ) ) {
			$attributes['readonly'] = 'readonly';
		}

		$html = \sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
			\sanitize_html_class( $tag->name ),
			wpcf7_format_atts( $attributes ),
			$error
		);

		return $html;
	}

	public static function get_value( $name ) {
		$value = trim( \filter_input( \INPUT_POST, $name, \FILTER_SANITIZE_STRING ) );

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
	 * @param object $result Validation result.
	 * @param object $tag    Form tag.
	 *
	 * @return object
	 */
	public function validate( $result, $tag ) {
		$value = trim( \filter_input( \INPUT_POST, $tag->name, \FILTER_SANITIZE_STRING ) );

		// Check required.
		if ( $tag->is_required() && empty( $value ) ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );

			return $result;
		}

		// Parse input.
		$amount = self::get_value( $tag->name );

		if ( null === $amount ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_pronamic_pay_amount' ) );
		}

		return $result;
	}

	/**
	 * Contact Form 7 messages.
	 *
	 * @param array $messages Messages.
	 *
	 * @return array
	 */
	public function messages( $messages ) {
		return array_merge(
			$messages,
			array(
				'invalid_pronamic_pay_amount' => array(
					'description' => __( 'Input amount is invalid.', 'pronamic_ideal' ),
					'default'     => __( 'The input amount is invalid.', 'pronamic_ideal' )
				),
			)
		);
	}
}
