<?php
/**
 * Plugin Name: Pronamic Pay Contact Form 7 Add-On
 * Plugin URI: https://www.pronamic.eu/plugins/pronamic-pay-contact-form-7/
 * Description: Extend the Pronamic Pay plugin with Contact Form 7 support to receive payments through a variety of payment providers.
 *
 * Version: 3.5.1
 * Requires at least: 4.7
 * Requires PHP: 7.4
 *
 * Author: Pronamic
 * Author URI: https://www.pronamic.eu/
 *
 * Text Domain: pronamic-pay-contact-form-7
 * Domain Path: /languages/
 *
 * License: GPL-3.0-or-later
 *
 * Requires Plugins: pronamic-ideal, contact-form-7
 * Depends: wp-pay/core
 *
 * GitHub URI: https://github.com/wp-pay-extensions/contact-form-7
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

add_filter(
	'pronamic_pay_plugin_integrations',
	function ( $integrations ) {
		$classes = [
			\Pronamic\WordPress\Pay\Extensions\ContactForm7\Extension::class,
		];

		foreach ( $classes as $class ) {
			if ( ! array_key_exists( $class, $integrations ) ) {
				$integrations[ $class ] = new $class();
			}
		}

		return $integrations;
	}
);
