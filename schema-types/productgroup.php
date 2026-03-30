<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Giga_SP_Type_ProductGroup extends Giga_SP_Schema_Base {
	public function get_schema(): array {
		$post = $this->get_post();
		if ( ! $post || ! is_a( $post, 'WP_Post' ) || 'product' !== $post->post_type ) {
			return [];
		}

		if ( class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro() && class_exists( 'Giga_SP_WooCommerce' ) ) {
			$pro_schema = Giga_SP_WooCommerce::get_wc_schema( $post->ID );
			if ( ! empty( $pro_schema ) ) {
				return $pro_schema;
			}
		}

		return [];
	}
}
