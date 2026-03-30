<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_HowTo' ) ) {
	class Giga_SP_Type_HowTo extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			return [
				'@context' => 'https://schema.org/',
				'@type' => 'HowTo',
				'name' => 'How to use Giga Schema Pro'
			];
		}
	}

}
