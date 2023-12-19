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
	 * Filter tags.
	 * 
	 * @param callable $callback Filter function.
	 * @return WPCF7_FormTag[]
	 */
	private function filter_tags( $callback ) {
		return \array_filter(
			$this->submission->get_contact_form()->scan_form_tags(),
			$callback
		);
	}

	/**
	 * Get tags by basetype or option.
	 * 
	 * @param string $value Name or option.
	 * @return WPCF7_FormTag[]
	 */
	public function get_tags_with_basetype_or_name_or_option( $value ) {
		return $this->filter_tags(
			function ( $tag ) use ( $value ) {
				return (
					( $tag->name === $value )
						||
					( $tag->basetype === $value )
						||
					$tag->has_option( $value )
				);
			}
		);
	}

	/**
	 * Get tags by basetype.
	 * 
	 * @param string $basetype Basetype.
	 * @return WPCF7_FormTag[]
	 */
	private function get_tags_with_basetype( $basetype ) {
		return $this->filter_tags(
			function ( $tag ) use ( $basetype ) {
				return ( $tag->basetype === $basetype );
			}
		);
	}

	/**
	 * Get tags by name.
	 * 
	 * @param string $name Name.
	 * @return WPCF7_FormTag[]
	 */
	private function get_tags_with_name( $name ) {
		return $this->filter_tags(
			function ( $tag ) use ( $name ) {
				return ( $tag->name === $name );
			}
		);
	}

	/**
	 * Get tags with option.
	 * 
	 * @param string $option Option.
	 * @return WPCF7_FormTag[]
	 */
	private function get_tags_with_option( $option ) {
		return $this->filter_tags(
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
	 * Get values by tag.
	 * 
	 * @param WPCF7_FormTag $tag Tag.
	 * @return array<string>
	 */
	public function get_values_by_tag( $tag ) {
		/**
		 * Hidden fields.
		 */
		$hidden_fields = $this->get_hidden_fields();

		if ( \in_array( $tag->name, $hidden_fields, true ) ) {
			return [];
		}

		$data = $this->submission->get_posted_data( $tag->name );

		if ( null === $data ) {
			return [];
		}

		$data = \wpcf7_array_flatten( $data );

		/**
		 * Contact Form 7 concatenates the field option value with user input for free text fields. We
		 * are only interested in the input value as amount.
		 *
		 * @link https://github.com/rocklobster-in/contact-form-7/blob/2cfaa472fa485c6d3366fcdd80701fdaf7f9e425/includes/submission.php#L434-L437
		 */
		if ( \wpcf7_form_tag_supports( $tag->type, 'selectable-values' ) && $tag->has_option( 'free_text' ) ) {
			$tag_values = \WPCF7_USE_PIPE ? $tag->pipes->collect_afters() : $tag->values;

			$tag_value_last = \end( $tag_values );

			$value = \array_pop( $data );

			if ( \str_starts_with( $value, $tag_value_last . ' ' ) ) {
				$value = \substr( $value, \strlen( $tag_value_last . ' ' ) );
			}

			$data[] = $value;
		}

		return $data;
	}

	/**
	 * Get value by tag.
	 * 
	 * @param WPCF7_FormTag $tag Tag.
	 * @return string
	 */
	public function get_value_by_tag( $tag ) {
		$values = $this->get_values_by_tag( $tag );

		return \implode( ', ', $values );
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
	 * Get value by tag name or option.
	 * 
	 * @param string $value Name or option value.
	 * @return string
	 */
	public function get_value_by_tag_name_or_option( $value ) {
		$tags = $this->get_tags_with_name( $value );

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_with_option( $value );
		}

		return $this->get_value_by_tags( $tags );
	}

	/**
	 * Get value by tag basetype, name or option.
	 * 
	 * @param string $value Value.
	 * @return string
	 */
	public function get_value_by_tag_basetype_or_name_or_option( $value ) {
		$tags = $this->get_tags_with_basetype( $value );

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_with_name( $value );
		}

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_with_option( $value );
		}

		return $this->get_value_by_tags( $tags );
	}

	/**
	 * Get value by tag name, option or basetype.
	 * 
	 * @param string $value    Name or option value.
	 * @param string $basetype Basetype.
	 * @return string
	 */
	public function get_value_by_tag_name_or_option_or_basetype( $value, $basetype ) {
		$tags = $this->get_tags_with_name( $value );

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_with_option( $value );
		}

		if ( 0 === \count( $tags ) ) {
			$tags = $this->get_tags_with_basetype( $basetype );
		}

		return $this->get_value_by_tags( $tags );
	}
}
