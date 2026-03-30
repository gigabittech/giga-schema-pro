<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Event' ) ) {
	class Giga_SP_Type_Event extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Event',
				'name' => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'startDate' => get_post_meta( $post->ID, '_event_start_date', true ) ?: wp_date( 'c' ),
				'location' => [
					'@type' => 'Place',
					'name' => get_post_meta( $post->ID, '_event_location_name', true ) ?: 'Online',
					'address' => [
						'@type' => 'PostalAddress',
						'streetAddress' => get_post_meta( $post->ID, '_event_address', true ) ?: '',
					]
				]
			];
		}
	}

}
