<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_LocalBusiness' ) ) {
	class Giga_SP_Type_LocalBusiness extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			return [
				'@context' => 'https://schema.org/',
				'@type' => 'LocalBusiness',
				'name' => $this->get_site_name(),
				'url' => home_url()
			];
		}
	}

}
