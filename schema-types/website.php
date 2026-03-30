<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_WebSite' ) ) {
	class Giga_SP_Type_WebSite extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			return [
				'@context' => 'https://schema.org/',
				'@type' => 'WebSite',
				'name' => $this->get_site_name(),
				'url' => home_url(),
				'potentialAction' => [
					'@type' => 'SearchAction',
					'target' => home_url( '?s={search_term_string}' ),
					'query-input' => 'required name=search_term_string'
				]
			];
		}
	}

}
