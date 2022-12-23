# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

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
