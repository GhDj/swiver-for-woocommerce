<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://https://github.com/GhDj
 * @since      1.0.0
 *
 * @package    Swiver
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all plugin options
delete_option('swiver_token');
delete_option('swiver_api_retrieved_data');
delete_option('swiver_last_sync');

// For multisite installations, delete options from all sites
if ( is_multisite() ) {
	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option('swiver_token');
		delete_option('swiver_api_retrieved_data');
		delete_option('swiver_last_sync');
		restore_current_blog();
	}
}

// Delete all product meta created by the plugin
global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s)",
		'swiver_sync',
		'swiver_id'
	)
);

// Log the uninstallation
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'Swiver for WooCommerce has been uninstalled and all data has been removed.' );
}
