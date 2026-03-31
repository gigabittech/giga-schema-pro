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

// Delete all post meta using WordPress API (best practice)
delete_post_meta_by_key( '_giga_sp_disabled_types' );
delete_post_meta_by_key( '_giga_sp_custom_json' );

// Delete all transients using WordPress API (best practice)
global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		'_transient_giga_sp_validation_%',
		'_transient_timeout_giga_sp_validation_%'
	)
);
