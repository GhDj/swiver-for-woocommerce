<?php
/**
 * PHPUnit bootstrap file for Swiver WooCommerce plugin tests.
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load Brain Monkey
\Brain\Monkey\setUp();

// Define WordPress constants if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('SWIVER_PLUGIN_DIR')) {
    define('SWIVER_PLUGIN_DIR', dirname(__DIR__) . '/');
}

if (!defined('SWIVER_PLUGIN_URL')) {
    define('SWIVER_PLUGIN_URL', 'http://example.com/wp-content/plugins/swiver/');
}

if (!defined('SWIVER_API_URL')) {
    define('SWIVER_API_URL', 'https://server.swiver.io/open_api/');
}

if (!defined('SWIVER_VERSION')) {
    define('SWIVER_VERSION', '1.0.0');
}

/**
 * Base test case class with Brain Monkey setup.
 */
abstract class Swiver_Unit_Test_Case extends \PHPUnit\Framework\TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }
}
