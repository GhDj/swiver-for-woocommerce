<?php

namespace Swiver\Swiver_WooCommerce;

class Swiver_Customer {

	private $api_token;


	public function __construct() {

		$this->api_token = Swiver_Helper::get_token();

	}

	public function get_swiver_customer($customer_name) {

		$api_url   = Swiver_Helper::get_api_endpoint('customers/find');

		$data = [
			"keyType" => "name",
			"key" => $customer_name
		];

		$response = wp_remote_post( $api_url, [
			'headers'      => [
				'X-AUTH-TOKEN' => $this->api_token,
			],
			'X-AUTH-TOKEN' => $this->api_token,
			'timeout'      => 45,
			'body' => $data
		] );

		$body = wp_remote_retrieve_body( $response );

		$customer = json_decode($body, true);

		if (isset($customer["code"]) && $customer['code']) {
			return false;
		}

		return $customer;
	}

	public function create_customer($order) {

		$api_url = Swiver_Helper::get_api_endpoint('customers/new');

		$customer = [];

		$phone = $order->get_billing_phone();

		if ($phone) {
			$country_code = WC()->countries->get_country_calling_code($order->get_billing_country());
			$phone_with_code = Swiver_Helper::ensure_phone_has_country_code($phone, $country_code);
			$phone = $phone_with_code;
		}

		$customer['name'] = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
		$customer['type'] = 0;
		$customer['email'] = $order->get_billing_email();
		$customer['website'] = '';
		$customer['company_name'] = $order->get_billing_company();
		$customer['registration'] = $order->get_billing_company();

		$customer['contact_address'] = [
			[
				'id'         => '',
				'is_default' => true,
				'type'       => 1,
				'address'    => [
					'id'       => '',
					'type'     => 0,
					'country'  => $order->get_shipping_country(),
					'region'   => $order->get_shipping_city(),
					'zip_code' => $order->get_shipping_postcode(),
					'address'  => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2()
				],
			],
			[
				'id'         => '',
				'is_default' => true,
				'type'       => 0,
				'address'    => [
					'id'       => '',
					'type'     => 1,
					'country'  => $order->get_billing_country(),
					'region'   => $order->get_billing_city(),
					'zip_code' => $order->get_billing_postcode(),
					'address'  => $order->get_billing_address_1() . ' ' . $order->get_billing_address_2()
				],
			]
		];

		$customer['contact_phones'] = [
			[
				'country' => $order->get_billing_country(),
				'value' => $phone,
				'type' => 1,
				'display_in_doc' => true,
				'is_default' => true
			]
		];
		$customer['phone1'] = $phone;
		$customer['phone2'] = $phone;

		$customer['civility'] = 'M';
		$customer['reference'] = '';
		$customer['turnover'] = '0';
		$customer['price_list'] = '';
		$customer['contactType'] = '';
		$customer['due_date_number'] = '';
		$customer['due_date_type'] = '';
		$customer['in_progress'] = '';
		$customer['company'] = Swiver_Helper::get_api_data()['data']['id'];
		$customer['enabled'] = true;
		$customer['integrationData'] = [
			[
				'id' => $order->get_customer_id(),
				'sku' => '',
				'url' => '',
				'idIntegration' => Swiver_Helper::get_api_data()['data']['id'],
				'dateSync' =>  gmdate( 'd/m/Y' ),
			]
		];

		// Send request to the API
		$response = wp_remote_post( $api_url, [
			'headers'      => [
				'X-AUTH-TOKEN' => $this->api_token,
			],
			'X-AUTH-TOKEN' => $this->api_token,
			'timeout'      => 45,
			'body'         => $customer
		] );

		$body = wp_remote_retrieve_body( $response );

		return json_decode($body, true);
	}

	public function get_customer($order) {

		$customer = $this->get_swiver_customer($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name());

		if (!$customer) {
			return $this->create_customer($order);
		}

		return $customer;

	}

}