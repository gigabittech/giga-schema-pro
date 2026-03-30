<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_AggregateRating' ) ) {
	class Giga_SP_Type_AggregateRating extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'AggregateRating',
				'itemReviewed' => [
					'@type' => 'Thing',
					'name' => get_the_title( $post ),
				],
				'ratingValue' => get_post_meta( $post->ID, '_aggregate_rating_value', true ) ?: '5',
				'reviewCount' => get_post_meta( $post->ID, '_aggregate_rating_count', true ) ?: '1',
			];
		}
	}

}
