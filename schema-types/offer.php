<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Offer' ) ) {
	class Giga_SP_Type_Offer extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Offer',
				'name' => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'price' => get_post_meta( $post->ID, '_offer_price', true ) ?: '0.00',
				'priceCurrency' => get_post_meta( $post->ID, '_offer_currency', true ) ?: 'USD',
				'url' => $this->get_url(),
			];
		}
	}

}
