<?php

namespace Swiver\Swiver_WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

class Swiver_WooCommerce {

	/**
	 * @var Swiver_WooCommerce
	 */
	private static $instance;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Singleton instance can't be cloned.
	 */
	private function __clone() {
	}

	/**
	 * Singleton instance can't be serialized.
	 */
	public function __wakeup() {
	}

	/**
	 * Swiver Woocommerce constructor.
	 */
	private function __construct() {

		// Bail early if WooCommerce is not activated
		if ( ! defined( 'WC_VERSION' ) ) {
			add_action( 'admin_notices', function () {
				?>
				<div id="message" class="notice notice-error">
					<p><?php esc_html(__( 'Swiver requires an active version of WooCommerce', 'swiver-for-woocommerce' )); ?></p>
				</div>
				<?php
			} );

			return;
		}
		add_filter( 'plugin_action_links_' . SWIVER_PLUGIN_BASENAME, [ $this, 'plugin_action_links' ] );
		add_action( 'woocommerce_product_options_advanced', [$this, 'swiver_product_field'] );
		add_action( 'woocommerce_process_product_meta', [$this, 'swiver_product_save_field'] );
	}

	public function plugin_action_links( $links = [] ) {
		$plugin_links = [
			'<a href="' . esc_url( Swiver_Helper::get_setting_link() ) . '">' . esc_html__( 'Settings', 'swiver-for-woocommerce' ) . '</a>',
		];

		return array_merge( $plugin_links, $links );
	}

	function swiver_product_field(){

		global $post;

		// Retrieve the product object using WooCommerce abstraction
		$product = wc_get_product( $post->ID );

		// Fetch meta values using WooCommerce's abstraction
		$swiver_sync = $product ? $product->get_meta( 'swiver_sync' ) : '';
		$swiver_id = $product ? $product->get_meta( 'swiver_id' ) : '';

		echo '<div class="options_group">';
		wp_nonce_field( 'swiver_save_product', 'swiver_product_nonce' );
		woocommerce_wp_checkbox(
			array(
				'id'      => 'swiver-sync',
				'value'   => $swiver_sync,
				'label'   => __( 'Swiver sync', 'swiver-for-woocommerce' ),
				'desc_tip' => true,
				'description' => __( 'This product is synchronised with Swiver', 'swiver-for-woocommerce' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'      => 'swiver-id',
				'value'   => $swiver_id,
				'label'   => __( 'Swiver ID', 'swiver-for-woocommerce' ),
				'desc_tip' => true,
				'description' => __( 'Product ID on Swiver', 'swiver-for-woocommerce' ),
			)
		);
		echo '</div>';

	}


	function swiver_product_save_field( $id ) {

		if ( ! isset( $_POST['swiver_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['swiver_product_nonce'] ) ), 'swiver_save_product' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_product', $id ) ) {
			return;
		}

		$product = wc_get_product( $id ); // Use WooCommerce's product abstraction

		// Save the 'swiver_sync' checkbox value
		$sync = isset( $_POST['swiver-sync'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['swiver-sync'] ) ) ? 'yes' : 'no';
		$product->update_meta_data( 'swiver_sync', $sync );

		// Save the 'swiver_id' text field value
		$swiver_id = isset( $_POST['swiver-id'] ) ? sanitize_text_field( wp_unslash( $_POST['swiver-id'] ) ) : '';
		$product->update_meta_data( 'swiver_id', $swiver_id );

		$product->save(); // Save changes to the product

	}

}