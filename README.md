# WordPress Pay Extension: Contact Form 7

**Contact Form 7 driver for the WordPress payment processing library.**

## Amount Field

The amount field can be a `text`, `number`, `drop-down menu`, `checkboxes` or `radio buttons` field,
it requires the `pronamic_pay_amount` option:

```
[number pronamic_pay_amount]
```

## WordPress environment for building and testing

```
npx wp-env start
```

For testing with `wp-env` you probably want to disable the default Contact Form 7 mail.
You can achieve this by editing the form and including the following under 'Additional Settings':

```
skip_mail: on
```

## Links

- https://www.pronamicpay.com/
- https://www.pronamic.eu/
- https://wordpress.org/plugins/pronamic-ideal/
- https://wordpress.org/plugins/pronamic-pay-with-mollie-for-contact-form-7/
- https://wordpress.org/plugins/contact-form-7/
- https://github.com/rocklobster-in/contact-form-7
- https://wordpress.org/plugins/cf7-conditional-fields/
- https://contactform7.com/
- https://contactform7.com/tag-syntax/
