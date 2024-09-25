# WordPress Pay Extension: Contact Form 7

**Contact Form 7 driver for the WordPress payment processing library.**

## Amount Field

The amount field can be a `text`, `number`, `drop-down menu`, `checkboxes` or `radio buttons` field,
it requires the `pronamic_pay_amount` option:

```
[number pronamic_pay_amount]
```

## Currency

Payments are created with the Euro currency by default. An ISO 4217 currency code can be provided
using the `pronamic_pay_currency` field name or as a field option: 

```
[hidden pronamic_pay_currency "CHF"]

[select select-123 pronamic_pay_currency "EUR" "CHF"]
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
- https://en.wikipedia.org/wiki/ISO_4217#List_of_ISO_4217_currency_codes
