<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Person' ) ) {
	class Giga_SP_Type_Person extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Person',
				'name' => get_the_author_meta( 'display_name', $post->post_author ),
				'url' => get_author_posts_url( $post->post_author )
			];
		}
	}

}
