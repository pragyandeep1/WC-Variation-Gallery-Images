<?php

/**
 * Plugin Name:      WooCommerce Variation Gallery
 * Plugin URI:       https://github.com/pragyandeep1/Woocommerce-Variation-Gallery-Images
 * Description:      Dynamically updates the product gallery based on selected variation.
 * Version:          1.1.1
 * Author:           Pragyandeep Mohanty
 * Author URI:       https://github.com/pragyandeep1
 * Text Domain:      woocommerce-variation-gallery
 **/

// defined('ABSPATH') or die('Keep Silent');
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define RTWPVG_PLUGIN_FILE.
if (!defined('RTWPVG_PLUGIN_FILE')) {
  define('RTWPVG_PLUGIN_FILE', __FILE__);
}
define( 'RTWPVG_PLUGIN_PATH', plugin_dir_path(__FILE__) );

if (!class_exists('WooProductVariationGallery')) {
  require_once 'app/wc-variation-gallery.php';
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
