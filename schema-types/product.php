<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Giga_SP_Type_Product extends Giga_SP_Schema_Base {
	public function get_schema(): array {
		$post = $this->get_post();
		// If it's not a post or not a product type, return empty array.
		if ( ! $post || ! is_a( $post, 'WP_Post' ) || 'product' !== $post->post_type ) {
			return [];
		}

		// Try loading the Pro Deep Schema first
		if ( class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro() && class_exists( 'Giga_SP_WooCommerce' ) ) {
			$pro_schema = Giga_SP_WooCommerce::get_wc_schema( $post->ID );
			if ( ! empty( $pro_schema ) ) {
				return $pro_schema;
			}
		}

		// Fallback baseline schema for Free version / non-pro scenarios
		$schema = [
			'@context'    => 'https://schema.org/',
			'@type'       => 'Product',
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
			'url'         => $this->get_url(),
		];

		if ( function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post->ID );
			if ( $product ) {
				$sku = $product->get_sku();
				if ( ! empty( $sku ) ) {
					$schema['sku'] = $sku;
				}

				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$schema['image'][] = wp_get_attachment_url( $image_id );
				}

				$price = $product->get_price();
				if ( '' !== $price ) {
					$schema['offers'] = [
						'@type'         => 'Offer',
						'url'           => $this->get_url(),
						'priceCurrency' => get_woocommerce_currency(),
						'price'         => $price,
						'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					];
				}
			}
		}

		return $schema;
	}
}
