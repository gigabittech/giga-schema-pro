<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_VideoObject' ) ) {
	class Giga_SP_Type_VideoObject extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'VideoObject',
				'name' => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'thumbnailUrl' => get_the_post_thumbnail_url( $post->ID, 'full' ) ?: '',
				'uploadDate' => get_the_date( 'c', $post ),
				'contentUrl' => get_post_meta( $post->ID, '_video_url', true ) ?: '',
			];
		}
	}

}
