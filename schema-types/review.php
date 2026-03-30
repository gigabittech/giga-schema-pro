<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Review' ) ) {
	class Giga_SP_Type_Review extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Review',
				'itemReviewed' => [
					'@type' => 'Thing',
					'name' => get_the_title( $post ),
				],
				'author' => [
					'@type' => 'Person',
					'name' => get_the_author_meta( 'display_name', $post->post_author ),
				],
				'reviewRating' => [
					'@type' => 'Rating',
					'ratingValue' => get_post_meta( $post->ID, '_review_rating', true ) ?: '5',
					'bestRating' => '5',
				],
				'reviewBody' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
			];
		}
	}

}
