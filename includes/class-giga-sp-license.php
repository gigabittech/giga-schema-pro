<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_License' ) ) {
	class Giga_SP_License {
		public static function is_pro() {
			$key = get_option( 'giga_sp_license_key', '' );
			return ! empty( $key );
		}
	}
}
