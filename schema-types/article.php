<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Article' ) ) {
	class Giga_SP_Type_Article extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Article',
				'headline' => get_the_title( $post ),
				'url' => $this->get_url(),
				'datePublished' => get_the_date( 'c', $post ),
				'dateModified' => get_the_modified_date( 'c', $post ),
				'author' => [
					'@type' => 'Person',
					'name' => get_the_author_meta( 'display_name', $post->post_author ),
				],
				'publisher' => [
					'@type' => 'Organization',
					'name' => $this->get_site_name(),
				]
			];
		}
	}

}
