<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_BreadcrumbList' ) ) {
	class Giga_SP_Type_BreadcrumbList extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			// Basic breadcrumb implementation
			$items = [
				[
					'@type' => 'ListItem',
					'position' => 1,
					'name' => 'Home',
					'item' => home_url()
				]
			];

			$post = $this->get_post();
			if ( $post && is_a( $post, 'WP_Post' ) ) {
				$items[] = [
					'@type' => 'ListItem',
					'position' => 2,
					'name' => get_the_title( $post ),
					'item' => $this->get_url()
				];
			}

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'BreadcrumbList',
				'itemListElement' => $items
			];
		}
	}

}
