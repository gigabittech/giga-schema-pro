<?php
/**
 * Clean uninstall logic
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$giga_sp_options_to_delete = [
	'giga_sp_rules',
	'giga_sp_settings',
	'giga_sp_woocommerce_settings',
	'giga_sp_license_key',
	'giga_sp_validation_report',
	'giga_sp_version'
];

foreach ( $giga_sp_options_to_delete as $option ) {
	delete_option( $option );
}

global $wpdb;

// Delete all post meta
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_giga_sp_disabled_types', '_giga_sp_custom_json')" );

// Delete all transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_giga_sp_validation_%' OR option_name LIKE '_transient_timeout_giga_sp_validation_%'" );
