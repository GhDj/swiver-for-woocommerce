<?php

namespace Swiver\Tests\Unit;

use Brain\Monkey\Functions;
use Swiver\Swiver_WooCommerce\Admin\Swiver_Settings;

require_once dirname(__DIR__) . '/bootstrap.php';

class Swiver_SettingsTest extends \Swiver_Unit_Test_Case
{
    private $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock WordPress functions used in constructor
        Functions\when('add_action')->justReturn(true);

        $this->settings = new Swiver_Settings();
    }

    public function test_class_can_be_instantiated()
    {
        $this->assertInstanceOf(Swiver_Settings::class, $this->settings);
    }

    public function test_sanitize_swiver_token_removes_whitespace()
    {
        Functions\when('sanitize_text_field')->alias(function ($input) {
            return trim($input);
        });

        $result = $this->settings->sanitize_swiver_token('  token123  ');

        $this->assertEquals('token123', $result);
    }

    public function test_sanitize_swiver_token_allows_empty_for_disconnect()
    {
        Functions\when('sanitize_text_field')->alias(function ($input) {
            return trim($input);
        });

        $result = $this->settings->sanitize_swiver_token('');

        $this->assertEquals('', $result);
    }

    public function test_api_endpoints_constant_contains_required_endpoints()
    {
        $reflection = new \ReflectionClass(Swiver_Settings::class);
        $endpoints = $reflection->getConstant('API_ENDPOINTS');

        $this->assertArrayHasKey('me', $endpoints);
        $this->assertArrayHasKey('taxes', $endpoints);
        $this->assertArrayHasKey('brands', $endpoints);
        $this->assertArrayHasKey('warehouses', $endpoints);
        $this->assertArrayHasKey('categories', $endpoints);
        $this->assertArrayHasKey('units', $endpoints);
    }

    public function test_api_endpoints_have_correct_paths()
    {
        $reflection = new \ReflectionClass(Swiver_Settings::class);
        $endpoints = $reflection->getConstant('API_ENDPOINTS');

        $this->assertEquals('shared/me', $endpoints['me']);
        $this->assertEquals('shared/vats', $endpoints['taxes']);
        $this->assertEquals('shared/brands', $endpoints['brands']);
        $this->assertEquals('shared/warehouses', $endpoints['warehouses']);
        $this->assertEquals('shared/categories', $endpoints['categories']);
        $this->assertEquals('shared/units', $endpoints['units']);
    }
}
