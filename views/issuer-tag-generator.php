<?php
/**
 * Issuer tag generator.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\ContactForm7
 */

if ( ! isset( $args ) ) {
	$args = array();
}

$args = wp_parse_args( $args, array() );

/* translators: 1: Pronamic Pay plugin name, 2: documentation URL anchor */
$description = __( 'Generate a tag for an issuer field. %1$s requires a field with the `pronamic_pay_amount` option, but payment method and issuer fields are optional in most cases. For step-by-step instructions on receiving payments with Contact Form 7, please see %2$s.', 'pronamic_ideal' );

$desc_link = wpcf7_link( __( 'https://www.pronamic.eu/support/how-to-connect-contact-form-7-to-pronamic-pay/', 'pronamic_ideal' ), __( 'How to connect Contact Form 7 to Pronamic Pay', 'pronamic_ideal' ) );

?>
<div class="control-box">
	<fieldset>
		<legend>
			<?php

			printf(
				esc_html( $description ),
				esc_html( __( 'Pronamic Pay', 'pronamic_ideal' ) ),
				wp_kses_post( $desc_link )
			);

			?>
		</legend>

		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<?php echo esc_html( __( 'Field type', 'pronamic_ideal' ) ); ?>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<?php echo esc_html( __( 'Field type', 'pronamic_ideal' ) ); ?>
						</legend>

						<label>
							<input type="checkbox" name="required"> <?php echo esc_html( __( 'Required field', 'pronamic_ideal' ) ); ?>
						</label>
					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>">
						<?php echo esc_html( __( 'Name', 'pronamic_ideal' ) ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>">
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>">
						<?php echo esc_html( __( 'Id attribute', 'pronamic_ideal' ) ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>">
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'pronamic_ideal' ) ); ?></label>
				</th>
				<td>
					<input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>">
				</td>
			</tr>
			</tbody>
		</table>
	</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="pronamic_pay_issuer" class="tag code" readonly="readonly" onfocus="this.select()">

	<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'pronamic_ideal' ) ); ?>">
	</div>

	<br class="clear">

	<p class="description mail-tag">
		<label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>">
			<?php

			printf(
				/* translators: %s: mail-tag placeholder HTML */
				esc_html( __( 'To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.', 'pronamic_ideal' ) ),
				'<strong><span class="mail-tag"></span></strong>'
			);

			?>

			<input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>">
		</label>
	</p>
</div>
