<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_SpeakableSpecification' ) ) {
	class Giga_SP_Type_SpeakableSpecification extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'SpeakableSpecification',
				'cssSelector' => ['.speakable-content'],
			];
		}
	}

}
