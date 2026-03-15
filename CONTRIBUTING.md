# Contributing to Swiver for WooCommerce

Thank you for your interest in contributing to Swiver for WooCommerce! This document provides guidelines and instructions for contributing.

## Code of Conduct

Please be respectful and constructive in all interactions. We welcome contributors of all experience levels.

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- Composer
- WordPress 6.0+
- WooCommerce 6.0+
- A Swiver account (for testing)

### Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/GhDj/swiver-for-woocommerce.git
   cd swiver-for-woocommerce
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run tests:
   ```bash
   composer test
   ```

## Development Workflow

### Branching Strategy

- `main` - Production-ready code
- `develop` - Integration branch for features
- `feature/*` - New features
- `fix/*` - Bug fixes
- `release/*` - Release preparation

### Creating a Feature

1. Create a branch from `develop`:
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```

2. Make your changes

3. Write/update tests

4. Run the test suite:
   ```bash
   composer test
   ```

5. Commit your changes with a descriptive message

6. Push and create a Pull Request to `develop`

## Coding Standards

### PHP

- Follow WordPress Coding Standards
- Use meaningful variable and function names
- Add PHPDoc blocks for classes and methods
- Keep functions focused and small

### JavaScript

- Use jQuery for DOM manipulation (WordPress compatibility)
- Follow WordPress JavaScript coding standards
- Use `'use strict';` in all files

### CSS

- Use Bootstrap classes where appropriate
- Follow BEM naming convention for custom styles

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage report
composer test:coverage
```

### Writing Tests

- Place unit tests in `tests/Unit/`
- Use Brain Monkey for mocking WordPress functions
- Name test files with `Test.php` suffix
- Name test methods with `test_` prefix

Example:
```php
public function test_is_connected_returns_true_when_token_exists()
{
    // Arrange
    Functions\when('get_option')->justReturn('token123');

    // Act
    $result = Swiver_Helper::is_connected();

    // Assert
    $this->assertTrue($result);
}
```

## Pull Request Process

1. Ensure all tests pass
2. Update documentation if needed
3. Update CHANGELOG.md with your changes
4. Request review from maintainers
5. Address review feedback
6. Squash commits if requested

### PR Title Format

- `feat: Add new feature description`
- `fix: Fix bug description`
- `docs: Update documentation`
- `test: Add/update tests`
- `refactor: Code refactoring`

## Reporting Issues

### Bug Reports

Include:
- WordPress version
- WooCommerce version
- PHP version
- Steps to reproduce
- Expected vs actual behavior
- Error messages/logs

### Feature Requests

Include:
- Use case description
- Proposed solution
- Alternative solutions considered

## Release Process

1. Create release branch from `develop`
2. Bump version numbers
3. Update CHANGELOG.md
4. Create PR to `main`
5. Tag release after merge
6. Create GitHub release with notes

## Questions?

- Open an issue for questions
- Check existing issues first
- Be patient for responses

## License

By contributing, you agree that your contributions will be licensed under the GPLv3 License.
