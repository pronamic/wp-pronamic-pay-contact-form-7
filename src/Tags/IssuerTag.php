<?php
/**
 * Issuer form tag.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7\Tags;

use Pronamic\WordPress\Pay\Extensions\ContactForm7\Pronamic;
use WPCF7_FormTag;
use WPCF7_Validation;

/**
 * Issuer tag.
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class IssuerTag {
	/**
	 * Form tag.
	 */
	const TAG = 'pronamic_pay_issuer';

	/**
	 * Issuer tag constructor.
	 */
	public function __construct() {
		\wpcf7_add_form_tag( self::TAG, array( $this, 'handler' ), true );
		\wpcf7_add_form_tag( self::TAG . '*', array( $this, 'handler' ), true );

		// Filters.
		\add_filter( 'wpcf7_validate_' . self::TAG, array( $this, 'validate' ), 10, 2 );
		\add_filter( 'wpcf7_validate_' . self::TAG . '*', array( $this, 'validate' ), 10, 2 );

		// Actions.
		\add_action( 'wpcf7_admin_init', array( $this, 'add_tag_generator' ), 60 );
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

		// Get gateway.
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

		$attributes = array(
			'class'    => $tag->get_class_option( $class ),
			'id'       => $tag->get_id_option(),
			'name'     => $tag->name,
			'tabindex' => $tag->get_option( 'tabindex', 'signed_int', true ),
			'value'    => \wpcf7_get_hangover( $tag->name, $tag->get_default_option( $value ) ),
		);

		if ( $tag->has_option( 'readonly' ) ) {
			$attributes['readonly'] = 'readonly';
		}

		// Payment method options.
		$issuer_options = $gateway->get_transient_issuers();

		if ( null === $issuer_options ) {
			return '';
		}

		$options = array(
			'<option></option>',
		);

		foreach ( $issuer_options[0]['options'] as $value => $label ) {
			$options[] = sprintf(
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
	 * Get value.
	 *
	 * @param string $name Field name.
	 * @return string|null
	 */
	public static function get_value( $name ) {
		$value = \trim( \filter_input( \INPUT_POST, $name, \FILTER_SANITIZE_STRING ) );

		if ( empty( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * Validate field input.
	 *
	 * @param WPCF7_Validation $result Validation result.
	 * @param WPCF7_FormTag    $tag    Form tag.
	 * @return WPCF7_Validation
	 */
	public function validate( $result, $tag ) {
		$value = trim( \filter_input( \INPUT_POST, $tag->name, \FILTER_SANITIZE_STRING ) );

		// Check required.
		if ( $tag->is_required() && empty( $value ) ) {
			$result->invalidate( $tag, \wpcf7_get_message( 'invalid_required' ) );

			return $result;
		}

		return $result;
	}

	/**
	 * Add tag generator.
	 *
	 * @return void
	 */
	public function add_tag_generator() {
		$tag_generator = \WPCF7_TagGenerator::get_instance();

		$tag_generator->add( self::TAG, __( 'Issuer', 'pronamic_ideal' ), array( $this, 'tag_generator' ) );
	}

	/**
	 * Tag generator.
	 *
	 * @param \WPCF7_ContactForm   $form Contact form.
	 * @param array<string, mixed> $args Arguments.
	 * @return void
	 */
	public function tag_generator( $form, $args ) {
		require \dirname( __FILE__ ) . '/../../views/issuer-tag-generator.php';
	}
}
