<?php

namespace Swiver\Swiver_WooCommerce;

if (!defined('ABSPATH')) {
	exit;
}

class Swiver_Order_Sync {

	public function __construct() {
		add_action('woocommerce_thankyou', [$this, 'sync_order_to_swiver_api']);
	}

	public function sync_order_to_swiver_api($order_id) {
        $api_token = Swiver_Helper::get_token();
        $api_data = Swiver_Helper::get_api_data();

		$warehouse   = $api_data['warehouses'];

		if (!$api_token) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log(__('No API token set.', 'swiver-for-woocommerce'));
			}
			return;
		}

		$document = $this->create_draft($api_token);

		if ($document) {
			$order = wc_get_order($order_id);
		}

		$document_data['id'] = $document['id'];

		$document_data['notes'] = $order->get_customer_note();

		$document_data['warehouse'] = $warehouse[0]['id'];
		$document_data['date'] = gmdate( 'Y-m-d' );
		$document_data['global_discount'] = $order->get_discount_total() / $order->get_total() * 100;

		$contact = new Swiver_Customer();

		$contact = $contact->get_customer($order);

		$document_data['contact'] = $contact['id'];
		$document_data['contact_address'] = $contact['contact_address'][0]['address']['id'];
		$document_data['document_lines'] = [];

		foreach ($order->get_items() as $item) {

			$product = new Swiver_Product();
			$product = $product->get_product($item);

			$document_data['document_lines'][] = [
			//	'id' => null,
				'type'                     => '0',
				'product'                  => $product['id'],
				'unit_price'               => (float) $item->get_total(),
				'qty'                      => (int)  $item->get_quantity(),
				'qty1'                      => (int) $item->get_quantity(),
				'qty2'                      => (int) $item->get_quantity(),
				'unit'                      => $product['unit']['id'],
				'product_description'      => $product['description'] ? $product['description'] : $product['name'],
				'is_description_show'      => false,
				'label'                    => $product['name'],
				'product_label'            => $product['name'],
				'weight'                   => (int) $item->get_product()->get_weight(),
				'vat'                      => (int) $product['vat']['vat'],
				'discount'                 => round(round( $item->get_subtotal() + $item->get_subtotal_tax(), 3 ) / round( $product['unit_price'] ) / 100 ),
				'prodd' => $product
			];
		}

		$update_document_url = Swiver_Helper::get_api_endpoint('document/'.$document_data['id']);

		$document_data['integrationData'] = [
			[
				'id'            => $order->get_id(),
				'sku'           => '',
				'url'           => get_edit_post_link($order->get_id()),
				'idIntegration' => Swiver_Helper::get_api_data()['data']['id'],
				'dateSync'      => gmdate( 'd/m/Y' ),

			]
		];

		$response = wp_remote_request( $update_document_url, [
			'method' => 'PUT',
			'headers'      => [
				'X-AUTH-TOKEN' => $api_token,
				'method' => 'PUT',
			],
			'X-AUTH-TOKEN' => $api_token,
			'timeout'      => 45,
			'body'         => $document_data
		] );

		// TODO : check for errors

		$body = wp_remote_retrieve_body( $response );

		$validated = $this->validate_draft($api_token, $document_data['id']);

		return $validated;

	}

	public function create_draft($api_token) {

		$create_document_api_url = Swiver_Helper::get_api_endpoint('document/draft');

		// Send request to the API
		$response = wp_remote_post( $create_document_api_url, [
			'headers'      => [
				'X-AUTH-TOKEN' => $api_token,
			],
			'X-AUTH-TOKEN' => $api_token,
			'timeout'      => 45,
			'body'         => [
				'type' => 4
			]
		] );


		if (is_wp_error($response)) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log(__('Error sending order data:', 'swiver-for-woocommerce') . ' ' . $response->get_error_message());
			}
			return false;
		} else {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log(__('Order data sent successfully:', 'swiver-for-woocommerce'));
			}
			$body = wp_remote_retrieve_body( $response );
			return json_decode($body, true);
		}

	}

	public function validate_draft($api_token, $id) {

		$validate_document_api_url = Swiver_Helper::get_api_endpoint('document/state/'.$id);

		// Send request to the API
		$response = wp_remote_request( $validate_document_api_url, [
			'method' => 'PUT',
			'headers'      => [
				'X-AUTH-TOKEN' => $api_token,
			],
			'X-AUTH-TOKEN' => $api_token,
			'timeout'      => 45,
			'body'         => [
				'transition' => 'to_created'
			]
		] );

		if (is_wp_error($response)) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log(__('Error validating draft:', 'swiver-for-woocommerce') . ' ' . $response->get_error_message());
			}
			return false;
		} else {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Order synced success');
			}
			$body = wp_remote_retrieve_body( $response );
			return json_decode($body, true);
		}

	}

	

}
