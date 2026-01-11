<?php

namespace Swiver\Tests\Unit;

use Brain\Monkey\Functions;
use Swiver\Swiver_WooCommerce\Swiver_Helper;

require_once dirname(__DIR__) . '/bootstrap.php';

class Swiver_HelperTest extends \Swiver_Unit_Test_Case
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset static properties using reflection
        $reflection = new \ReflectionClass(Swiver_Helper::class);

        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $optionsProperty->setValue(null, null);

        $taxCacheProperty = $reflection->getProperty('tax_cache');
        $taxCacheProperty->setAccessible(true);
        $taxCacheProperty->setValue(null, null);
    }

    public function test_is_connected_returns_true_when_token_and_data_exist()
    {
        Functions\when('get_option')->alias(function ($option) {
            if ($option === 'swiver_token') {
                return 'test_token_123';
            }
            if ($option === 'swiver_api_retrieved_data') {
                return ['data' => ['name' => 'Test Company']];
            }
            return null;
        });

        $result = Swiver_Helper::is_connected();

        $this->assertTrue($result);
    }

    public function test_is_connected_returns_false_when_token_empty()
    {
        Functions\when('get_option')->alias(function ($option) {
            if ($option === 'swiver_token') {
                return '';
            }
            if ($option === 'swiver_api_retrieved_data') {
                return ['data' => ['name' => 'Test Company']];
            }
            return null;
        });

        // Clear cache
        Swiver_Helper::clear_options_cache();

        $result = Swiver_Helper::is_connected();

        $this->assertFalse($result);
    }

    public function test_is_connected_returns_false_when_api_data_empty()
    {
        Functions\when('get_option')->alias(function ($option) {
            if ($option === 'swiver_token') {
                return 'test_token_123';
            }
            if ($option === 'swiver_api_retrieved_data') {
                return [];
            }
            return null;
        });

        // Clear cache
        Swiver_Helper::clear_options_cache();

        $result = Swiver_Helper::is_connected();

        $this->assertFalse($result);
    }

    public function test_get_last_sync_returns_timestamp()
    {
        $timestamp = 1704067200; // 2024-01-01 00:00:00

        Functions\when('get_option')->alias(function ($option, $default = null) use ($timestamp) {
            if ($option === 'swiver_last_sync') {
                return $timestamp;
            }
            return $default;
        });

        $result = Swiver_Helper::get_last_sync();

        $this->assertEquals($timestamp, $result);
    }

    public function test_get_last_sync_returns_null_when_not_set()
    {
        Functions\when('get_option')->alias(function ($option, $default = null) {
            return $default;
        });

        $result = Swiver_Helper::get_last_sync();

        $this->assertNull($result);
    }

    public function test_get_last_sync_formatted_returns_never_when_not_set()
    {
        Functions\when('get_option')->alias(function ($option, $default = null) {
            return $default;
        });

        Functions\when('__')->returnArg();

        $result = Swiver_Helper::get_last_sync_formatted();

        $this->assertEquals('Never', $result);
    }

    public function test_get_last_sync_formatted_returns_formatted_date()
    {
        $timestamp = 1704067200;

        Functions\when('get_option')->alias(function ($option, $default = null) use ($timestamp) {
            if ($option === 'swiver_last_sync') {
                return $timestamp;
            }
            if ($option === 'date_format') {
                return 'Y-m-d';
            }
            if ($option === 'time_format') {
                return 'H:i';
            }
            return $default;
        });

        Functions\when('date_i18n')->alias(function ($format, $ts) {
            return date($format, $ts);
        });

        // Mock __ to return a formatted string directly
        Functions\when('__')->alias(function ($text) use ($timestamp) {
            if ($text === '%s at %s') {
                return date('Y-m-d', $timestamp) . ' at ' . date('H:i', $timestamp);
            }
            return $text;
        });

        $result = Swiver_Helper::get_last_sync_formatted();

        $this->assertStringContainsString('2024-01-01', $result);
    }

    public function test_get_company_name_returns_name_from_api_data()
    {
        Functions\when('get_option')->alias(function ($option) {
            if ($option === 'swiver_token') {
                return 'test_token';
            }
            if ($option === 'swiver_api_retrieved_data') {
                return ['data' => ['name' => 'My Company']];
            }
            return null;
        });

        // Clear cache
        Swiver_Helper::clear_options_cache();

        $result = Swiver_Helper::get_company_name();

        $this->assertEquals('My Company', $result);
    }

    public function test_get_company_name_returns_null_when_no_data()
    {
        Functions\when('get_option')->alias(function ($option) {
            if ($option === 'swiver_api_retrieved_data') {
                return [];
            }
            return null;
        });

        // Clear cache
        Swiver_Helper::clear_options_cache();

        $result = Swiver_Helper::get_company_name();

        $this->assertNull($result);
    }

    public function test_get_api_endpoint_returns_full_url()
    {
        Functions\when('trailingslashit')->alias(function ($string) {
            return rtrim($string, '/') . '/';
        });

        $result = Swiver_Helper::get_api_endpoint('shared/me');

        $this->assertEquals('https://server.swiver.io/open_api/shared/me', $result);
    }

    public function test_get_api_endpoint_trims_leading_slash()
    {
        Functions\when('trailingslashit')->alias(function ($string) {
            return rtrim($string, '/') . '/';
        });

        $result = Swiver_Helper::get_api_endpoint('/shared/me');

        $this->assertEquals('https://server.swiver.io/open_api/shared/me', $result);
    }

    public function test_ensure_phone_has_country_code_adds_code()
    {
        $result = Swiver_Helper::ensure_phone_has_country_code('123456789', '+216');

        $this->assertEquals('+216123456789', $result);
    }

    public function test_ensure_phone_has_country_code_keeps_existing_plus()
    {
        $result = Swiver_Helper::ensure_phone_has_country_code('+216123456789', '+216');

        $this->assertEquals('+216123456789', $result);
    }

    public function test_ensure_phone_has_country_code_adds_plus_when_code_exists()
    {
        $result = Swiver_Helper::ensure_phone_has_country_code('216123456789', '+216');

        $this->assertEquals('+216123456789', $result);
    }

    public function test_clear_options_cache_resets_cache()
    {
        // First, populate the cache
        Functions\when('get_option')->alias(function ($option) {
            static $callCount = 0;
            $callCount++;
            if ($option === 'swiver_token') {
                return 'token_' . $callCount;
            }
            return null;
        });

        $token1 = Swiver_Helper::get_token();

        // Clear cache
        Swiver_Helper::clear_options_cache();

        // Should get fresh value
        $token2 = Swiver_Helper::get_token();

        $this->assertNotEquals($token1, $token2);
    }
}
