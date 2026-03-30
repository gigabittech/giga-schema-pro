<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_ItemList' ) ) {
	class Giga_SP_Type_ItemList extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'ItemList',
				'name' => get_the_title( $post ),
				'itemListElement' => [], // To be populated dynamically with items
			];
		}
	}

}
