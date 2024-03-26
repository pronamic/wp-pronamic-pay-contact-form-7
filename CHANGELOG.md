# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [3.5.1] - 2024-03-26

### Commits

- Fixed "error  'detail' is never reassigned. Use 'const' instead". ([94f9a98](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/94f9a9838b3c251cc078f48863f66d7dd36e0f74))
- Added `.pronamic-build-ignore`. ([b042aa9](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/b042aa9c96cc5a92f062c517c526e4e89991becd))

### Composer

- Changed `wp-pay/core` from `^4.6` to `v4.16.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.16.0

Full set of changes: [`3.5.0...3.5.1`][3.5.1]

[3.5.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.5.0...v3.5.1

## [3.5.0] - 2024-02-07

### Changed

- Improved the support for Contact Form 7 checkbox fields used for the amount to be paid (`pronamic_pay_amount` tag option), multiple checked options/amounts are now added up. ([ba1322a](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/ba1322afb5d859f21281827a263dc94ed0dae350))

Full set of changes: [`3.4.0...3.5.0`][3.5.0]

[3.5.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.4.0...v3.5.0

## [3.4.0] - 2023-12-18

### Changed

- Added support for multiple `pronamic_pay_amount` tags. ([16](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/issues/16))

### Composer

- Changed `php` from `>=7.4` to `>=8.0`.

Full set of changes: [`3.3.2...3.4.0`][3.4.0]

[3.4.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.3.2...v3.4.0

## [3.3.2] - 2023-10-30

### Commits

- Fixed "Fatal error: Uncaught Error: Call to undefined method Pronamic\WordPress\Pay\Fields\SelectFieldOption::render()" (method has been removed in https://github.com/pronamic/wp-pay-core/commit/519532b7d65fb68a5374341ea1c1934885b28e5c). ([4f2f437](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/4f2f43706a62e4929ab21b274f89f84954276419))

Full set of changes: [`3.3.1...3.3.2`][3.3.2]

[3.3.2]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.3.1...v3.3.2

## [3.3.1] - 2023-10-13

### Commits

- Removed some left overs, subscriptions are not supported in current state. ([a8aef13](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/a8aef137ff427d3c604c0ccf8f95803831b4f8d5))

Full set of changes: [`3.3.0...3.3.1`][3.3.1]

[3.3.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.3.0...v3.3.1

## [3.3.0] - 2023-08-28

### Changed

- Improved processing of form submission data.

### Fixed

- Fixed compatibility with plugin "Send PDF for Contact Form 7".

### Commits

- Use `substr()` instead of `substr_replace()`. ([f245ee2](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/f245ee2daf483d1ae000a21f95a7fcb88581f673))
- Fixed "Cannot call method add() on Pronamic\WordPress\Money\Money|string". ([aa98a06](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/aa98a065b1a7c8282ee877bafbd7d4c7b87613a0))
- Improve handling free text value. ([d13802a](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/d13802a2244ef7ccd525b448b64979c94f965b23))
- Use `$submission->get_posted_string( $name )` for submission value. ([3ce7845](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/3ce7845de705a5fd69e5607b1895f19c4a5caf86))
- Check if submission is not null to fix issue #10. ([6cff3de](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/6cff3decf1e7b66a91d76ac50cd7b8140662a556))

Full set of changes: [`3.2.5...3.3.0`][3.3.0]

[3.3.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.2.5...v3.3.0

## [3.2.5] - 2023-06-01

### Commits

- Switch from `pronamic/wp-deployer` to `pronamic/pronamic-cli`. ([6407876](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/6407876b1eaba9d93c0a969f915ed416a423d3f1))
- Prevent duplicate integration registration. ([c69b923](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/c69b923dde07ee3f83031091ebd1aa47f438f627))

Full set of changes: [`3.2.4...3.2.5`][3.2.5]

[3.2.5]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.2.4...v3.2.5

## [3.2.4] - 2023-03-10

### Commits

- Set `wordpress-plugin` type for Composer (pronamic/wp-pronamic-pay-with-mollie-for-contact-form-7#3). ([7a54710](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/7a54710bc223b0db69a80dfda050e379d0ef2e5b))

Full set of changes: [`3.2.3...3.2.4`][3.2.4]

[3.2.4]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.2.3...v3.2.4

## [3.2.3] - 2023-03-03
### Changed

- Updated `.gitattributes`.

Full set of changes: [`3.2.2...3.2.3`][3.2.3]

[3.2.3]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.2.2...v3.2.3

## [3.2.2] - 2023-02-23

### Commits

- Added support for multiple free text value options. ([84d9856](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/84d9856461da4f915fed5485bf60818162c120cf))
- Updated payment start on form submit. ([631888a](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/631888a659fd8017bd78dc4162cf341cbb970dbc))
Full set of changes: [`3.2.1...3.2.2`][3.2.2]

[3.2.2]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.2.1...v3.2.2

## [3.2.1] - 2023-01-31
### Composer

- Changed `php` from `>=8.0` to `>=7.4`.
Full set of changes: [`3.2.0...3.2.1`][3.2.1]

[3.2.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.2.0...v3.2.1

## [3.2.0] - 2022-12-23

### Commits

- Added support for https://github.com/WordPress/wp-plugin-dependencies. ([c251392](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/commit/c2513924268437eed1f8a519e758dd52e4e9fea0))

### Composer

- Changed `php` from `>=5.6.20` to `>=8.0`.
- Changed `wp-pay/core` from `^4.4` to `v4.6.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v3.1.2
Full set of changes: [`3.1.2...3.2.0`][3.2.0]

[3.2.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/v3.1.2...v3.2.0

## [3.1.2] - 2022-09-27
- Updated version number in `readme.txt`.

## [3.1.1] - 2022-09-27
- Update to `wp-pay/core` version `^4.4`.

## [3.1.0] - 2022-09-26
- Updated for new payment methods and fields registration.

## [3.0.3] - 2022-06-03
### Fixed
- Fix iDEAL bank select field when bank options are grouped (for example by country) by payment service provider. ([#2](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/issues/2))

## [3.0.2] - 2022-05-30
### Fixed
- Fix getting submission value from select option with quotes. ([#1](https://github.com/pronamic/wp-pronamic-pay-contact-form-7/issues/1))

## [3.0.1] - 2022-04-12
- Updated version number in `readme.txt`.

## [3.0.0] - 2022-01-10
### Chnaged
- Updated to https://github.com/pronamic/wp-pay-core/releases/tag/4.0.0.
- Added mail tags `pronamic_payment_id` and `pronamic_transaction_id`.

## [2.0.0] - 2021-08-05
- Updated to `pronamic/wp-pay-core`  version `3.0.0`.
- Updated to `pronamic/wp-money`  version `2.0.0`.
- Changed `TaxedMoney` to `Money`, no tax info.
- Switched to `pronamic/wp-coding-standards`.

## [1.1.1] - 2021-06-18
- Improved error handling on form submission.

## [1.1.0] - 2021-04-26
- Added support for getting submission value by tag name.
- Fixed handling tag options with non-unique values.
- Fixed processing form entry for active payment methods only.
- Improved compatibility with Contact Form 7 Conditional Fields add-on.

## [1.0.3] - 2021-01-14
- Fix redirecting when scripts are disabled through `wpcf7_load_js` filter.

## [1.0.2] - 2020-11-09
- Fixed getting amount from free text value.

## [1.0.1] - 2020-07-08
- Update main plugin file name.
- Add readme.

## 1.0.0 - 2020-07-08
- First release.

[unreleased]: https://github.com/wp-pay-extensions/contact-form-7/compare/3.1.2...HEAD
[3.1.2]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/3.0.3...3.1.0
[3.0.3]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/3.0.2...3.0.3
[3.0.2]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/2.0.0...3.0.0
[2.0.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/1.1.1...2.0.0
[1.1.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/1.0.3...1.1.0
[1.0.3]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/pronamic/wp-pronamic-pay-contact-form-7/compare/1.0.0...1.0.1
