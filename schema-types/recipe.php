<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Recipe' ) ) {
	class Giga_SP_Type_Recipe extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Recipe',
				'name' => get_the_title( $post ),
				'image' => get_the_post_thumbnail_url( $post->ID, 'full' ) ?: '',
				'author' => [
					'@type' => 'Person',
					'name' => get_the_author_meta( 'display_name', $post->post_author ),
				],
				'datePublished' => get_the_date( 'c', $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'recipeIngredient' => get_post_meta( $post->ID, '_recipe_ingredients', true ) ?: [],
				'recipeInstructions' => get_post_meta( $post->ID, '_recipe_instructions', true ) ?: [],
			];
		}
	}

}
