# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

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

[unreleased]: https://github.com/wp-pay-extensions/contact-form-7/compare/2.0.0...HEAD
[2.0.0]: https://github.com/wp-pay-extensions/contact-form-7/compare/1.1.1...2.0.0
[1.1.1]: https://github.com/wp-pay-extensions/contact-form-7/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/wp-pay-extensions/contact-form-7/compare/1.0.3...1.1.0
[1.0.3]: https://github.com/wp-pay-extensions/contact-form-7/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/wp-pay-extensions/contact-form-7/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/wp-pay-gateways/adyen/compare/1.0.0...1.0.1
