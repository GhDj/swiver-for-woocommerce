<?php

namespace Swiver\Swiver_WooCommerce\Admin;

use Swiver\Swiver_WooCommerce\Swiver_Helper;

if (!defined('ABSPATH')) {
    exit;
}

class Swiver_Settings
{

    const API_ENDPOINTS = [
        'me' => 'shared/me',
        'taxes' => 'shared/vats',
        'brands' => 'shared/brands',
        'warehouses' => 'shared/warehouses',
        'categories' => 'shared/categories',
        'units' => 'shared/units'
    ];

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_option_swiver_token', [$this, 'on_token_add'], 10, 2);
        add_action('update_option_swiver_token', [$this, 'on_token_update'], 10, 3);

        // AJAX handlers
        add_action('wp_ajax_swiver_sync', [$this, 'ajax_sync']);
        add_action('wp_ajax_swiver_resync', [$this, 'ajax_resync']);
        add_action('wp_ajax_swiver_disconnect', [$this, 'ajax_disconnect']);
        add_action('wp_ajax_swiver_add_tax', [$this, 'ajax_add_tax']);
        add_action('wp_ajax_swiver_add_all_taxes', [$this, 'ajax_add_all_taxes']);
    }

    // Add settings page
    public function add_settings_page()
    {
        add_menu_page(
            __('swiver_settings', 'swiver'),        // Page title
            __('Swiver', 'swiver'),                 // Menu title
            'manage_options',            // Capability
            'swiver-token-settings',        // Menu slug
            [$this, 'settings_page_content'],  // Callback function
            SWIVER_PLUGIN_URL . 'assets/logo-swiver.svg',    // Icon
            56
        );
    }

    // Register setting to store the token
    public function register_settings()
    {
        register_setting(
            'swiver_token_group',
            'swiver_token',
            array(
                'type' => 'string',
                'description' => 'API token for Swiver integration',
                'sanitize_callback' => [$this, 'sanitize_swiver_token'],
                'show_in_rest' => false,
                'default' => '',
            )
        );
    }


    // Sanitization callback function
    function sanitize_swiver_token($input)
    {
        // Remove any whitespace
        $sanitized_token = sanitize_text_field($input);

        // If token is empty, allow it (user is disconnecting)
        if (empty($sanitized_token)) {
            return '';
        }

        return $sanitized_token;
    }

    // Display the settings page content
    public function settings_page_content()
    {
        $this->enqueue_assets();
        require_once SWIVER_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }

    // AJAX: Sync token and fetch data
    public function ajax_sync()
    {
        check_ajax_referer('swiver_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'swiver')]);
        }

        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';

        if (empty($token)) {
            wp_send_json_error(['message' => __('Token cannot be empty.', 'swiver')]);
        }

        // Save the token
        update_option('swiver_token', $token);

        // Fetch API data
        $result = $this->fetch_all_api_data($token);

        if ($result === false) {
            wp_send_json_error(['message' => __('Failed to fetch data from Swiver API.', 'swiver')]);
        }

        $api_data = Swiver_Helper::get_api_data();

        if (!empty($api_data)) {
            wp_send_json_success([
                'message' => __('Successfully synchronized with Swiver!', 'swiver'),
                'data' => $api_data
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to fetch data from Swiver API.', 'swiver')]);
        }
    }

    // AJAX: Resync data using existing token
    public function ajax_resync()
    {
        check_ajax_referer('swiver_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'swiver')]);
        }

        $token = get_option('swiver_token');

        if (empty($token)) {
            wp_send_json_error(['message' => __('No token found. Please connect first.', 'swiver')]);
        }

        // Fetch API data using existing token
        $result = $this->fetch_all_api_data($token);

        if ($result === false) {
            wp_send_json_error(['message' => __('Failed to fetch data from Swiver API.', 'swiver')]);
        }

        $api_data = Swiver_Helper::get_api_data();

        if (!empty($api_data)) {
            wp_send_json_success([
                'message' => __('Successfully resynced with Swiver!', 'swiver'),
                'data' => $api_data
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to fetch data from Swiver API.', 'swiver')]);
        }
    }

    // AJAX: Disconnect (clear token and data)
    public function ajax_disconnect()
    {
        check_ajax_referer('swiver_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'swiver')]);
        }

        delete_option('swiver_token');
        delete_option('swiver_api_retrieved_data');
        delete_option('swiver_last_sync');
        Swiver_Helper::clear_options_cache();

        wp_send_json_success(['message' => __('Disconnected from Swiver.', 'swiver')]);
    }

    // AJAX: Add tax to WooCommerce
    public function ajax_add_tax()
    {
        check_ajax_referer('swiver_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'swiver')]);
        }

        $tax_rate = isset($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : 0;
        $tax_name = isset($_POST['tax_name']) ? sanitize_text_field($_POST['tax_name']) : '';
        $tax_id = isset($_POST['tax_id']) ? intval($_POST['tax_id']) : 0;

        if ($tax_rate <= 0) {
            wp_send_json_error(['message' => __('Invalid tax rate.', 'swiver')]);
        }

        // Create WooCommerce tax rate
        $tax_rate_data = [
            'tax_rate_country'  => '',
            'tax_rate_state'    => '',
            'tax_rate'          => $tax_rate,
            'tax_rate_name'     => $tax_name ?: sprintf(__('Tax %s%%', 'swiver'), $tax_rate),
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 1,
            'tax_rate_order'    => 0,
            'tax_rate_class'    => '',
        ];

        $new_tax_id = \WC_Tax::_insert_tax_rate($tax_rate_data);

        if ($new_tax_id) {
            // Update the stored data to mark this tax as matched
            $api_data = Swiver_Helper::get_api_data();
            if (!empty($api_data['taxes'])) {
                foreach ($api_data['taxes'] as &$tax) {
                    if ($tax['id'] == $tax_id) {
                        $tax['wc'] = true;
                        $tax['wc_name'] = $tax_rate_data['tax_rate_name'];
                        break;
                    }
                }
                update_option('swiver_api_retrieved_data', $api_data);
                Swiver_Helper::clear_options_cache();
            }

            wp_send_json_success([
                'message' => sprintf(__('Tax rate %s%% added to WooCommerce.', 'swiver'), $tax_rate),
                'wc_name' => $tax_rate_data['tax_rate_name']
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to create tax rate.', 'swiver')]);
        }
    }

    // AJAX: Add all unmatched taxes to WooCommerce
    public function ajax_add_all_taxes()
    {
        check_ajax_referer('swiver_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'swiver')]);
        }

        $api_data = Swiver_Helper::get_api_data();
        if (empty($api_data['taxes'])) {
            wp_send_json_error(['message' => __('No taxes found.', 'swiver')]);
        }

        $added_count = 0;
        $failed_count = 0;

        foreach ($api_data['taxes'] as &$tax) {
            // Skip already matched taxes
            if (!empty($tax['wc'])) {
                continue;
            }

            $tax_rate = floatval($tax['rate']);
            if ($tax_rate <= 0) {
                $failed_count++;
                continue;
            }

            // Create WooCommerce tax rate
            $wc_tax_name = $tax['name'] ?: sprintf(__('Tax %s%%', 'swiver'), $tax_rate);
            $tax_rate_data = [
                'tax_rate_country'  => '',
                'tax_rate_state'    => '',
                'tax_rate'          => $tax_rate,
                'tax_rate_name'     => $wc_tax_name,
                'tax_rate_priority' => 1,
                'tax_rate_compound' => 0,
                'tax_rate_shipping' => 1,
                'tax_rate_order'    => 0,
                'tax_rate_class'    => '',
            ];

            $new_tax_id = \WC_Tax::_insert_tax_rate($tax_rate_data);

            if ($new_tax_id) {
                $tax['wc'] = true;
                $tax['wc_name'] = $wc_tax_name;
                $added_count++;
            } else {
                $failed_count++;
            }
        }

        // Update stored data
        update_option('swiver_api_retrieved_data', $api_data);
        Swiver_Helper::clear_options_cache();

        if ($added_count > 0) {
            $message = sprintf(
                _n(
                    '%d tax rate added to WooCommerce.',
                    '%d tax rates added to WooCommerce.',
                    $added_count,
                    'swiver'
                ),
                $added_count
            );
            if ($failed_count > 0) {
                $message .= ' ' . sprintf(
                    _n(
                        '%d failed.',
                        '%d failed.',
                        $failed_count,
                        'swiver'
                    ),
                    $failed_count
                );
            }
            wp_send_json_success(['message' => $message, 'added' => $added_count]);
        } else {
            wp_send_json_error(['message' => __('No taxes were added.', 'swiver')]);
        }
    }

    public function on_token_add($option, $value)
    {
        if (!empty($value)) {
            $this->fetch_all_api_data($value);
        }
    }

    public function on_token_update($old_value, $new_value, $option)
    {
        // Skip if token hasn't changed
        if ($old_value === $new_value) {
            return;
        }

        if (empty($new_value)) {
            update_option('swiver_api_retrieved_data', []);
            Swiver_Helper::clear_options_cache();
            return;
        }

        $this->fetch_all_api_data($new_value);
    }

    private function fetch_all_api_data($api_token)
    {
        $api_data = [];

        // Clear existing data before fetching new data
        delete_option('swiver_api_retrieved_data');

        // Fetch main data
        $response = $this->fetch_api_endpoint('me', $api_token);
        if ($response === false) {
            // Critical endpoint failed
            error_log(__('Failed to fetch company data from Swiver API', 'swiver'));
            return false;
        }
        if ($response) {
            $api_data['data'] = $response;
        }

        // Fetch all other data
        foreach (self::API_ENDPOINTS as $key => $endpoint) {
            if ($key === 'me') continue;

            $response = $this->fetch_api_endpoint($key, $api_token);
            if ($response !== false) {
                if ($key === 'taxes') {
                    $api_data[$key] = $this->process_taxes_data($response);
                } else {
                    $api_data[$key] = $response;
                }
            }
        }

        if (!empty($api_data) && isset($api_data['data'])) {
            update_option('swiver_api_retrieved_data', $api_data);
            update_option('swiver_last_sync', current_time('timestamp'));
            Swiver_Helper::clear_options_cache();
            return true;
        }

        return false;
    }

    private function fetch_api_endpoint($key, $api_token)
    {

        $response = wp_remote_get(
            Swiver_Helper::get_api_endpoint(self::API_ENDPOINTS[$key]),
            [
                'headers' => [
                    'X-AUTH-TOKEN' => $api_token,
                ],
                'timeout' => 45,
            ]
        );

        if (is_wp_error($response)) {
            error_log(__('Error retrieving data from API:', 'swiver') . ' ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            error_log(sprintf(__('API returned status code %d for endpoint: %s', 'swiver'), $status_code, $key));
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Check if JSON decoding failed
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(sprintf(__('JSON decode error for %s: %s', 'swiver'), $key, json_last_error_msg()));
            return false;
        }

        // Check for API error responses (error object with code and message)
        if (is_array($data) && isset($data['code']) && isset($data['message'])) {
            error_log(sprintf(__('API error for %s: %s', 'swiver'), $key, $data['message']));
            return false;
        }

        // For non-critical endpoints, allow empty arrays (they might just have no data)
        // For 'me' endpoint, we need actual data
        if ($key === 'me' && empty($data)) {
            error_log(sprintf(__('Empty response for critical endpoint: %s', 'swiver'), $key));
            return false;
        }

        return $data;
    }

    private function process_taxes_data($taxes)
    {
        $processed_taxes = [];
        $wc_taxes = \WC_Tax::get_rates();

        foreach ($taxes as $tax) {
            if (!$tax['enabled']) {
                continue;
            }

            // Find matching WooCommerce tax by rate
            $wc_match = null;
            $wc_tax_name = null;
            foreach ($wc_taxes as $wc_tax) {
                if (abs(floatval($wc_tax['rate']) - floatval($tax['vat'])) < 0.01) {
                    $wc_match = true;
                    $wc_tax_name = $wc_tax['label'];
                    break;
                }
            }

            $processed_taxes[] = [
                'id' => $tax['id'],
                'rate' => $tax['vat'],
                'name' => $tax['name'] ?? '',
                'swiver' => true,
                'wc' => $wc_match,
                'wc_name' => $wc_tax_name
            ];
        }

        return $processed_taxes;
    }

    private function enqueue_assets()
    {
        wp_enqueue_style('swiver-bootstrap', SWIVER_PLUGIN_URL . 'assets/css/bootstrap.min.css', array(), '5.3.2', 'all');
        wp_enqueue_style('swiver', SWIVER_PLUGIN_URL . 'assets/css/swiver.css', array(), '5.3.2', 'all');
        wp_enqueue_script('swiver-bootstrap-js', SWIVER_PLUGIN_URL . 'assets/js/bootstrap.min.js', array(), '5.3.2', true);

        // Admin AJAX script
        wp_enqueue_script('swiver-admin', SWIVER_PLUGIN_URL . 'assets/js/swiver-admin.js', array('jquery'), SWIVER_VERSION, true);
        wp_localize_script('swiver-admin', 'swiverAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('swiver_ajax_nonce'),
            'strings' => [
                'syncing' => __('Synchronizing...', 'swiver'),
                'resyncing' => __('Resyncing...', 'swiver'),
                'disconnecting' => __('Disconnecting...', 'swiver'),
                'addingTaxes' => __('Adding...', 'swiver'),
                'confirmAddAllTaxes' => __('Add all unmatched tax rates to WooCommerce?', 'swiver'),
            ]
        ]);
    }


}
