<h1 align="center">Pronamic Pay integration for Contact Form 7</h1>

<p align="center">
	Pronamic Pay integration for Contact Form 7 – extend your WordPress forms with seamless payment processing using the powerful Pronamic Pay platform.
</p>

## Table of Contents

- [Amount Field](#amount-field)
- [Currency](#currency)
- [Additional Settings](#additional-settings)
- [WordPress environment for testing](#wordpress-environment-for-testing)
- [Links](#links)

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

## Additional Settings

It is possible to specify per Contact Form 7 form to which URL a visitor should be sent after a successful payment via the “Additional Settings”.

```
pronamic_pay_success_redirect_url: https://www.example.com/payment-completed/
```

## WordPress environment for testing

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
