<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_WebPage' ) ) {
	class Giga_SP_Type_WebPage extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			return [
				'@context' => 'https://schema.org/',
				'@type' => 'WebPage',
				'name' => ( $post && is_a( $post, 'WP_Post' ) ) ? get_the_title( $post ) : $this->get_site_name(),
				'url' => $this->get_url(),
			];
		}
	}

}
