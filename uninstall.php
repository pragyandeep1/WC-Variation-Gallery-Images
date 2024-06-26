<?php
/**
 * Uninstall plugin
 */

// If uninstall not called from WordPress, then exit
defined( 'WP_UNINSTALL_PLUGIN' ) or die( 'Keep Silent' );

$options = get_option( 'rtwpvg', array() );
if ( ! empty( $options ) && isset( $options['remove_all_data'] ) && $options['remove_all_data'] ) {
	global $wpdb;

	delete_option( 'rtwpvg' );
	// Remove Option
	delete_option( 'rtwpvg_pro_activate' );
	// Site options in Multisite
	delete_site_option( 'rtwpvg_pro_activate' );
}