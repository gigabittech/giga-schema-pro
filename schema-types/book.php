<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Book' ) ) {
	class Giga_SP_Type_Book extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'Book',
				'name' => get_the_title( $post ),
				'author' => [
					'@type' => 'Person',
					'name' => get_the_author_meta( 'display_name', $post->post_author ),
				],
				'isbn' => get_post_meta( $post->ID, '_book_isbn', true ) ?: '',
				'bookFormat' => get_post_meta( $post->ID, '_book_format', true ) ?: 'https://schema.org/EBook',
				'numberOfPages' => get_post_meta( $post->ID, '_book_pages', true ) ?: '',
			];
		}
	}

}
