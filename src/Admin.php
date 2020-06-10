<?php
/**
 * Admin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

namespace Pronamic\WordPress\Pay\Extensions\ContactForm7;

/**
 * Title: WordPress pay extension Contact Form 7 admin
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 1.0.0
 * @since   1.0.0
 */
class Admin {
	/**
	 * Bootstrap.
	 */
	public function __construct() {
		// Actions.
		\add_action( 'cf7_admin_init', array( __CLASS__, 'cf7_admin_init' ) );
	}

	/**
	 * Contact Form 7 admin init.
	 */
	public static function cf7_admin_init() {
	}
}
