<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_RealEstateListing' ) ) {
	class Giga_SP_Type_RealEstateListing extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'RealEstateListing',
				'name' => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'datePosted' => get_the_date( 'c', $post ),
				'offers' => [
					'@type' => 'Offer',
					'price' => get_post_meta( $post->ID, '_realestate_price', true ) ?: '0',
					'priceCurrency' => get_post_meta( $post->ID, '_realestate_currency', true ) ?: 'USD',
				]
			];
		}
	}

}
