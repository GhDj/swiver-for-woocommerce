<?php

namespace Swiver\Swiver_WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Utilities\OrderUtil;

class Swiver_Product {

	protected $api_token;

	public function __construct() {

		$this->api_token = Swiver_Helper::get_token();

	}

	public function get_swiver_product($id) {

		$api_url   = Swiver_Helper::get_api_endpoint('products/'.$id);

		$response = wp_remote_get( $api_url, [
			'headers'      => [
				'X-AUTH-TOKEN' => $this->api_token,
			],
			'X-AUTH-TOKEN' => $this->api_token,
			'timeout'      => 45,
		] );

		$body = wp_remote_retrieve_body( $response );

		return json_decode($body, true);

	}

	public function is_synced($order_product) {
		$is_synced = wc_get_product($order_product->get_product_id())->get_meta('swiver_sync');
		$swiver_id = wc_get_product($order_product->get_product_id())->get_meta('swiver_id');



		if ($is_synced && $swiver_id) {
			return $swiver_id;
		} else {
			return false;
		}
	}

	public function get_product($order_product) {

		if ($this->is_synced($order_product)) {
			$swiver_id = wc_get_product($order_product->get_product_id())->get_meta('swiver_id');

			return $this->get_swiver_product($swiver_id);

		} else {
			return $this->create_swiver_product($order_product);
		}

	}

	public function create_swiver_product($order_product) {
		$api_url   = Swiver_Helper::get_api_endpoint('products/new');
		$options   = Swiver_Helper::get_api_data();
		$category  = $options['categories'][0];

		$brand     = $options['brands'][0];
		$warehouse = $options['warehouses'][0];

		$tax_rate = round($order_product->get_subtotal_tax() / $order_product->get_subtotal() * 100);
		
		// Use the centralized tax management function
		$tax = Swiver_Helper::find_or_create_tax($this->api_token, $tax_rate);

		$a = 0;

		$product = [
			'name'              => $order_product->get_name(),
			'description'       => get_post( $order_product->get_product_id() )->post_content,
			'category'          => $category['id'],
			'brand'             => $brand['id'],
			'vat'               => $tax['id'],
			'taxes'             => [],
			'bar_codes'         => [],
			'allow_empty_stock' => true,
			'unit_price'        => (float) $order_product->get_product()->get_price(),
			'unit'              => Swiver_Helper::get_default_unit(),
			'has_serial_number' => false,
			'enabled'           => true,
			'integrationData'   => [
				[
					'id'            => $order_product->get_product_id(),
					'url'           => get_permalink( $order_product->get_product_id() ),
					'sku'           => $order_product->get_product()->get_sku(),
					'idIntegration' => Swiver_Helper::get_api_data()['data']['id'],
					'dateSync'      => gmdate( 'd/m/Y' )
				]
			],
			'logistics_infos'   => [
				[
					'warehouse' => $warehouse['id'],

					'allow_alert' => 'false'
				]
			],
			'internal_ref'      => $order_product->get_product()->get_sku(),
			'manufacturer_ref'  => "string",
			'model'             => Swiver_Helper::get_product_type( $order_product->get_product() )
		];

		// Send request to the API
		$response = wp_remote_post( $api_url, [
			'headers'      => [
				'X-AUTH-TOKEN' => $this->api_token,
			],
			'X-AUTH-TOKEN' => $this->api_token,
			'timeout'      => 45,
			'body'         => $product
		] );

		$body = wp_remote_retrieve_body( $response );

		$swiver_product = json_decode($body, true);



		$product = wc_get_product($order_product->get_product_id());
		$product->update_meta_data('swiver_sync', 'yes');
		$product->update_meta_data('swiver_id', $swiver_product['id']);
		$product->save();

		return $swiver_product;
	}

}