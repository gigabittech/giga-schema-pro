<?php
/**
 * Plugin Name: Giga Schema Pro
 * Plugin URI:  https://github.com/giga-schema-pro
 * Description: A complete, production-ready WordPress plugin for advanced JSON-LD schema generation with zero frontend assets.
 * Version:     1.0.0
 * Author:      Giga
 * Text Domain: giga-schema-pro
 * Domain Path: /languages
 * License:     GPL-2.0+
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GIGA_SP_VERSION', '1.0.0' );
define( 'GIGA_SP_FILE', __FILE__ );
define( 'GIGA_SP_PATH', plugin_dir_path( __FILE__ ) );
define( 'GIGA_SP_URL', plugin_dir_url( __FILE__ ) );

require_once GIGA_SP_PATH . 'includes/class-giga-sp-core.php';

if ( ! function_exists( 'giga_sp_run' ) ) {
	function giga_sp_run() {
		Giga_SP_Core::get_instance();
	}
}
giga_sp_run();
