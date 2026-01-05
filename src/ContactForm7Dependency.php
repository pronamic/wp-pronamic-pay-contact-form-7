<?php
/**
 * Contact Form 7 dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

use Pronamic\WordPress\Pay\Dependencies\Dependency;

/**
 * Contact Form 7 dependency class
 */
final class ContactForm7Dependency extends Dependency {
	/**
	 * Is met.
	 *
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		return \defined( '\WPCF7_VERSION' );
	}
}
