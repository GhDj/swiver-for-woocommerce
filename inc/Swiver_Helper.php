<?php

namespace Swiver\Swiver_WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Swiver_Helper {
    private static $options = null;
    private static $tax_cache = null;

    /**
     * Get all plugin options or a specific option
     *
     * @param string|null $key Specific option key to retrieve
     * @param mixed $default Default value if option doesn't exist
     * @return mixed
     */
    public static function get_options($key = null, $default = null) {
        if (self::$options === null) {
            self::$options = [
                'token' => get_option('swiver_token'),
                'api_data' => get_option('swiver_api_retrieved_data'),
                'api_url' => SWIVER_API_URL
            ];
        }

        if ($key === null) {
            return self::$options;
        }

        return isset(self::$options[$key]) ? self::$options[$key] : $default;
    }

    /**
     * Clear cached options (useful after updates)
     */
    public static function clear_options_cache() {
        self::$options = null;
        self::$tax_cache = null;
    }

    /**
     * Get API token
     *
     * @return string|null
     */
    public static function get_token() {
        return self::get_options('token');
    }

    /**
     * Get API retrieved data
     *
     * @param string|null $key Specific key from api_data
     * @return mixed
     */
    public static function get_api_data($key = null) {
        $api_data = self::get_options('api_data');
        if ($key === null) {
            return $api_data;
        }
        return isset($api_data[$key]) ? $api_data[$key] : null;
    }

    /**
     * Check if connected to Swiver
     *
     * @return bool
     */
    public static function is_connected() {
        $token = self::get_token();
        $api_data = self::get_api_data();
        return !empty($token) && !empty($api_data);
    }

    /**
     * Get last sync timestamp
     *
     * @return int|null
     */
    public static function get_last_sync() {
        return get_option('swiver_last_sync', null);
    }

    /**
     * Get formatted last sync time
     *
     * @return string
     */
    public static function get_last_sync_formatted() {
        $timestamp = self::get_last_sync();
        if (!$timestamp) {
            return __('Never', 'swiver-for-woocommerce');
        }
        /* translators: %1$s: date, %2$s: time */
        return sprintf(
            __('%1$s at %2$s', 'swiver-for-woocommerce'),
            date_i18n(get_option('date_format'), $timestamp),
            date_i18n(get_option('time_format'), $timestamp)
        );
    }

    /**
     * Get company name from API data
     *
     * @return string|null
     */
    public static function get_company_name() {
        $data = self::get_api_data('data');
        return isset($data['name']) ? $data['name'] : null;
    }


    /**
	 * Get a URL to the Swiver settings page.
	 *
	 * @return string
	 */
	public static function get_setting_link() {

		$section_slug      = strtolower( 'swiver-token-settings' );

		return admin_url( 'admin.php?page=' . $section_slug );
	}


	/**
     * Get all VATs/taxes from Swiver API with caching
     * 
     * @param string $api_token API token for authentication
     * @return array Array of VATs/taxes
     */
    public static function get_all_taxes($api_token) {
        // Return cached taxes if available
        if (self::$tax_cache !== null) {
            return self::$tax_cache;
        }

        // Fetch taxes from API
        $api_url = self::get_api_endpoint('shared/vats');
        $response = wp_remote_get($api_url, [
            'headers' => [
                'X-AUTH-TOKEN' => $api_token,
            ],
            'X-AUTH-TOKEN' => $api_token,
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            error_log(__('Error retrieving taxes from API:', 'swiver-for-woocommerce') . ' ' . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        self::$tax_cache = json_decode($body, true);
        
        return self::$tax_cache;
    }

	/**
     * Find existing tax by rate or create a new one
     * 
     * @param string $api_token API token for authentication
     * @param float $tax_rate Tax rate to find or create
     * @return array Tax data
     */
    public static function find_or_create_tax($api_token, $tax_rate) {
        $taxes = self::get_all_taxes($api_token);
        
        // Look for matching tax rate
        if (is_array($taxes)) {
            foreach ($taxes as $tax) {
                if (abs($tax['vat'] - $tax_rate) < 0.01 && $tax['enabled']) {
                    return [
                        'id' => $tax['id'],
                        'vat' => $tax['vat']
                    ];
                }
            }
        }
        
        // Create new tax if no match found
        return self::swiver_create_taxe($api_token, [
            'enabled' => true,
            'vat' => $tax_rate
        ]);
    }

	public static function swiver_create_taxe($api_token, $tax_data) {
		$api_url = self::get_api_endpoint('shared/vats');

		$response = wp_remote_post($api_url, [
			'headers' => [
				'X-AUTH-TOKEN' => $api_token,
			],
			'X-AUTH-TOKEN' => $api_token,
			'timeout' => 45,
			'body' => $tax_data
		]);

		$body = wp_remote_retrieve_body($response);
		$new_tax = json_decode($body, true);
		
		// Add to cache if successful and cache exists
		if (self::$tax_cache !== null && is_array($new_tax) && isset($new_tax['id'])) {
		    self::$tax_cache[] = $new_tax;
		}

		return $new_tax;
	}


	public static function get_default_unit(){
		$units = get_option('swiver_api_retrieved_data')['units'];
		foreach ($units as $unit) {
			if ($unit['code'] === 'pcs') {
				return $unit['id'];
			}
		}
	}


	public static function get_product_type($product) {
		if( $product->is_downloadable() || $product->is_virtual()) {
			return "service";
		}
		return "materiel";
	}

	public static function ensure_phone_has_country_code($phone_number, $country_code ) {
		// Remove any spaces, dashes, or parentheses
		$cleaned_number = preg_replace('/[\s\-\(\)]/', '', $phone_number);

		// Check if number already starts with + or country code
		if (strpos($cleaned_number, '+') === 0) {
			return $cleaned_number;
		}

		if (strpos($cleaned_number, ltrim($country_code, '+')) === 0) {
			return '+' . $cleaned_number;
		}

		// Add country code if it's missing
		return $country_code . $cleaned_number;
	}

    /**
     * Get full API endpoint URL
     *
     * @param string $path The API endpoint path
     * @return string The complete API URL
     */
    public static function get_api_endpoint($path = '') {
        return trailingslashit(SWIVER_API_URL) . ltrim($path, '/');
    }

    /**
     * Get the default API URL
     *
     * @return string
     */
    public static function get_default_api_url() {
        return SWIVER_API_URL;
    }

}