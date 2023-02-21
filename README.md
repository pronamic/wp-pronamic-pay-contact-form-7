# WordPress Pay Extension: Contact Form 7

**Contact Form 7 driver for the WordPress payment processing library.**

## Amount Field

The amount field can be a `text`, `number`, `drop-down menu`, `checkboxes` or `radio buttons` field,
it requires the `pronamic_pay_amount` attribute:

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
