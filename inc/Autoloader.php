<?php

namespace Swiver\Swiver_WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Autoloader {

	public static function register() {
		spl_autoload_register([__CLASS__, 'autoload']);
	}

	public static function autoload($class_name) {

		// Only autoload classes from our namespace
		if (strpos($class_name, 'Swiver\Swiver_WooCommerce') !== 0) {
			return;
		}

		// Remove the base namespace
		$class_name = str_replace('Swiver\Swiver_WooCommerce', '', $class_name);

		// Convert the class name to file path (replace backslashes with forward slashes)
		$class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);

		// Create the full file path
		$file_path = __DIR__ . $class_name . '.php';


		// Check if the file exists
		if (file_exists($file_path)) {
			require_once $file_path;
		}
	}
}
