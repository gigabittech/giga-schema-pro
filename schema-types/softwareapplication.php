<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_SoftwareApplication' ) ) {
	class Giga_SP_Type_SoftwareApplication extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'SoftwareApplication',
				'name' => get_the_title( $post ),
				'operatingSystem' => get_post_meta( $post->ID, '_software_os', true ) ?: 'Windows, macOS, Linux',
				'applicationCategory' => get_post_meta( $post->ID, '_software_category', true ) ?: 'WebApplication',
				'offers' => [
					'@type' => 'Offer',
					'price' => get_post_meta( $post->ID, '_software_price', true ) ?: '0',
					'priceCurrency' => get_post_meta( $post->ID, '_software_currency', true ) ?: 'USD',
				]
			];
		}
	}

}
