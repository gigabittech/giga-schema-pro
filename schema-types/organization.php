<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Organization' ) ) {
	class Giga_SP_Type_Organization extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$settings = get_option( 'giga_sp_settings', [] );
			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Organization',
				'name' => isset( $settings['organization_name'] ) ? $settings['organization_name'] : $this->get_site_name(),
				'url' => home_url(),
			];
		}
	}

}
