# Changelog

All notable changes to Swiver for WooCommerce will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-01-11

### Added
- Initial release
- **AJAX-based Sync** - Sync/disconnect with loading spinners and feedback
- **Tax Management**
  - View Swiver taxes with match status
  - "Add to WooCommerce" button for individual taxes
  - Bulk "Add all" button to import all unmatched taxes at once
  - Shows WooCommerce tax name for matched taxes
- **Resync Button** - Refresh data from Swiver without disconnecting
- **Connection Status Indicator** - Shows connection state, company name, and last sync time
- **Company Details** - Display company information from Swiver API
- **Business Data Display** - Brands, warehouses, categories, and units
- **Order Synchronization** - Automatic sync on checkout completion
- **Customer Management** - Auto-create/match customers in Swiver
- **Product Matching** - Match products by SKU or create new
- **WooCommerce HPOS** - High Performance Order Storage compatibility
- **Multi-site Support** - Multiple sites can connect to one Swiver account
- **Unit Tests** - PHPUnit test suite with Brain Monkey
- **GitHub Actions CI** - Automated testing on PHP 7.4, 8.0, 8.1, 8.2

### Security
- API token sanitization
- Nonce verification for AJAX requests
- Capability checks for admin actions

[Unreleased]: https://github.com/GhDj/swiver-woocommerce/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/GhDj/swiver-woocommerce/releases/tag/v1.0.0
