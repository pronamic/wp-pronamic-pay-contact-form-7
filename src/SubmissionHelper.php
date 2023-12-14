<?php
/**
 * Submission helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use WPCF7_FormTag;
use WPCF7_Submission;

/**
 * Submission helper class
 */
class SubmissionHelper {
	/**
	 * Submission.
	 * 
	 * @var WPCF7_Submission
	 */
	private $submission;

	/**
	 * Construct submission helper object.
	 * 
	 * @param WPCF7_Submission $submission Submission.
	 */
	public function __construct( $submission ) {
		$this->submission = $submission;
	}

	/**
	 * Get tags by filter.
	 * 
	 * @param callable $callback Filter function.
	 * @return WPCF7_FormTag[]
	 */
	private function get_tags_by_filter( $callback ) {
		return \array_filter(
			$this->submission->get_contact_form()->scan_form_tags(),
			$callback
		);
	}

	/**
	 * Get tags by basetype or option.
	 * 
	 * @param string $basetype Basetype.
	 * @param string $option   Option.
	 * @return WPCF7_FormTag[]
	 */
	public function get_tags_by_basetype_or_option( $basetype, $option ) {
		return $this->get_tags_by_filter(
			function ( $tag ) use ( $basetype, $option ) {
				return ( $tag->basetype === $basetype ) || $tag->has_option( $option );
			}
		);
	}

	/**
	 * Get tags by basetype.
	 * 
	 * @param string $basetype Basetype.
	 * @return WPCF7_FormTag[]
	 */
	private function get_tags_by_basetype( $basetype ) {
		return $this->get_tags_by_filter(
			function ( $tag ) use ( $basetype ) {
				return ( $tag->basetype === $basetype );
			}
		);
	}

	/**
	 * Get tags by options.
	 * 
	 * @param string $option Option.
	 * @return WPCF7_FormTag[]
	 */
	private function get_tags_by_option( $option ) {
		return $this->get_tags_by_filter(
			function ( $tag ) use ( $option ) {
				return $tag->has_option( $option );
			}
		);
	}

	/**
	 * Get hidden fields.
	 *
	 * Hidden fields may arise when using the "Conditional Fields for Contact Form 7" plugin.
	 * 
	 * @link https://wordpress.org/plugins/cf7-conditional-fields/
	 * @link https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/83122efa3755f1d4b667aed3e3e7c2ae0f813faa
	 * @return string[]
	 */
	private function get_hidden_fields() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by Contact Form 7.
		if ( ! \array_key_exists( '_wpcf7cf_hidden_group_fields', $_POST ) ) {
			return [];
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by Contact Form 7.
		$value = \sanitize_text_field( \wp_unslash( $_POST['_wpcf7cf_hidden_group_fields'] ) );

		$data = \json_decode( $value );

		if ( \is_array( $data ) ) {
			return $data;
		}

		return [];
	}

	/**
	 * Get value by tag.
	 * 
	 * @param WPCF7_FormTag $tag Tag.
	 * @return string
	 */
	public function get_value_by_tag( $tag ) {
		$value = $this->submission->get_posted_string( $tag->name );

		/**
		 * Contact Form 7 concatenates the field option value with user input for free text fields. We
		 * are only interested in the input value as amount.
		 *
		 * @link https://github.com/rocklobster-in/contact-form-7/blob/2cfaa472fa485c6d3366fcdd80701fdaf7f9e425/includes/submission.php#L434-L437
		 */
		if ( \wpcf7_form_tag_supports( $tag->type, 'selectable-values' ) && $tag->has_option( 'free_text' ) ) {
			$values = \WPCF7_USE_PIPE ? $tag->pipes->collect_afters() : $tag->values;

			$last_value = \end( $values );

			if ( \str_starts_with( $value, $last_value . ' ' ) ) {
				$value = \substr( $value, \strlen( $last_value . ' ' ) );
			}
		}

		/**
		 * Hidden fields.
		 */
		$hidden_fields = $this->get_hidden_fields();

		if ( \in_array( $tag->name, $hidden_fields, true ) ) {
			$value = '';
		}

		return $value;
	}

	/**
	 * Get value by tags.
	 * 
	 * @param WPCF7_FormTag[] $tags Tags.
	 * @return string
	 */
	private function get_value_by_tags( $tags ) {
		$tag = \reset( $tags );

		if ( false === $tag ) {
			return '';
		}

		return $this->get_value_by_tag( $tag );
	}

	/**
	 * Get value by tag option.
	 * 
	 * @param string $option Option.
	 * @return string
	 */
	public function get_value_by_tag_option( $option ) {
		$tags = $this->get_tags_by_option( $option );

		return $this->get_value_by_tags( $tags );
	}

	/**
	 * Get value by tag basetype or option.
	 * 
	 * @param string $basetype Basetype.
	 * @param string $option   Option.
	 * @return string
	 */
	public function get_value_by_tag_basetype_or_option( $basetype, $option ) {
		$tags = $this->get_tags_by_basetype( $basetype );

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_by_option( $option );
		}

		return $this->get_value_by_tags( $tags );
	}

	/**
	 * Get value by tag option or basetype.
	 * 
	 * @param string $option   Option.
	 * @param string $basetype Basetype.
	 * @return string
	 */
	public function get_value_by_tag_option_or_basetype( $option, $basetype ) {
		$tags = $this->get_tags_by_option( $option );

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_by_basetype( $basetype );
		}

		return $this->get_value_by_tags( $tags );
	}
}
