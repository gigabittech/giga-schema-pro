<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Service' ) ) {
	class Giga_SP_Type_Service extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Service',
				'serviceType' => get_the_title( $post ),
				'provider' => [
					'@type' => 'Organization',
					'name' => $this->get_site_name(),
				],
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
			];
		}
	}

}
