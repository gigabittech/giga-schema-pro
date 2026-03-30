<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_FAQ' ) ) {
	class Giga_SP_Type_FAQ extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			// Mock logic: Typically would pull from meta or custom fields
			return [
				'@context' => 'https://schema.org/',
				'@type' => 'FAQPage',
				'mainEntity' => []
			];
		}
	}

}
