<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_WooCommerce' ) ) {
	class Giga_SP_WooCommerce {
		public static function get_wc_schema( $product_id ) {
			if ( ! class_exists( 'Giga_SP_License' ) || ! Giga_SP_License::is_pro() || ! function_exists( 'wc_get_product' ) ) {
				return [];
			}

			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				return [];
			}

			$settings = get_option( 'giga_sp_woocommerce_settings', [] );
			$gtin_field = isset( $settings['gtinField'] ) ? $settings['gtinField'] : '_gtin';
			$mpn_field = isset( $settings['mpnField'] ) ? $settings['mpnField'] : '_mpn';
			$brand_field = isset( $settings['brandField'] ) ? $settings['brandField'] : '_brand';
			$default_brand = isset( $settings['defaultBrand'] ) ? $settings['defaultBrand'] : '';

			$schema = [
				'@context' => 'https://schema.org/',
				'@type'    => $product->is_type('variable') ? 'ProductGroup' : 'Product',
				'name'     => wp_strip_all_tags( $product->get_name() ),
				'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
				'sku'      => $product->get_sku(),
			];

			// Add images
			$image_id = $product->get_image_id();
			if ( $image_id ) {
				$schema['image'][] = wp_get_attachment_url( $image_id );
			}

			$gallery_ids = $product->get_gallery_image_ids();
			foreach ( $gallery_ids as $gid ) {
				$schema['image'][] = wp_get_attachment_url( $gid );
			}

			// Add GTIN from custom field
			$gtin = get_post_meta( $product->get_id(), $gtin_field, true );
			if ( $gtin ) {
				$schema['gtin13'] = sanitize_text_field( $gtin );
			}

			// Add MPN from custom field
			$mpn = get_post_meta( $product->get_id(), $mpn_field, true );
			if ( $mpn ) {
				$schema['mpn'] = sanitize_text_field( $mpn );
			}

			// Add Brand from custom field or default
			$brand = get_post_meta( $product->get_id(), $brand_field, true );
			if ( $brand ) {
				$schema['brand'] = [ '@type' => 'Brand', 'name' => sanitize_text_field( $brand ) ];
			} elseif ( ! empty( $default_brand ) ) {
				$schema['brand'] = [ '@type' => 'Brand', 'name' => sanitize_text_field( $default_brand ) ];
			}

			// Add offers for simple products
			$price = $product->get_price();
			if ( '' !== $price && ! $product->is_type('variable') ) {
				$schema['offers'] = [
					'@type'         => 'Offer',
					'url'           => get_permalink( $product->get_id() ),
					'priceCurrency' => get_woocommerce_currency(),
					'price'         => $price,
					'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				];

				// Add shipping details if configured
				if ( ! empty( $settings['shippingRate'] ) ) {
					$shipping_currency = ! empty( $settings['shippingCurrency'] ) ? $settings['shippingCurrency'] : get_woocommerce_currency();
					$schema['offers']['shippingDetails'] = [
						'@type' => 'OfferShippingDetails',
						'shippingRate' => [
							'@type' => 'MonetaryAmount',
							'value' => esc_html( $settings['shippingRate'] ),
							'currency' => sanitize_text_field( $shipping_currency )
						]
					];
				}

				// Add return policy if configured
				if ( ! empty( $settings['returnDays'] ) ) {
					$return_policy_category = ! empty( $settings['returnPolicyCategory'] ) ? $settings['returnPolicyCategory'] : 'https://schema.org/MerchantReturnFiniteReturnWindow';
					$schema['offers']['hasMerchantReturnPolicy'] = [
						'@type' => 'MerchantReturnPolicy',
						'merchantReturnDays' => intval( $settings['returnDays'] ),
						'returnPolicyCategory' => esc_url( $return_policy_category )
					];
				}
			} elseif ( $product->is_type('variable') ) {
				// Handle variable products with variants
				$schema['hasVariant'] = [];
				$available_variations = $product->get_available_variations();

				foreach ( $available_variations as $variation ) {
					$var_obj = wc_get_product( $variation['variation_id'] );
					if ( ! $var_obj ) {
						continue;
					}

					$variant_schema = [
						'@type' => 'Product',
						'name'  => wp_strip_all_tags( $var_obj->get_name() ),
						'sku'   => $var_obj->get_sku(),
						'image' => wp_get_attachment_url( $var_obj->get_image_id() ),
						'offers' => [
							'@type'         => 'Offer',
							'url'           => get_permalink( $product->get_id() ) . '?attribute_' . http_build_query( $variation['attributes'] ),
							'priceCurrency' => get_woocommerce_currency(),
							'price'         => $var_obj->get_price(),
							'availability'  => $var_obj->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
						]
					];

					// Add shipping and return policy to variant if configured
					if ( ! empty( $settings['shippingRate'] ) ) {
						$shipping_currency = ! empty( $settings['shippingCurrency'] ) ? $settings['shippingCurrency'] : get_woocommerce_currency();
						$variant_schema['offers']['shippingDetails'] = [
							'@type' => 'OfferShippingDetails',
							'shippingRate' => [
								'@type' => 'MonetaryAmount',
								'value' => esc_html( $settings['shippingRate'] ),
								'currency' => sanitize_text_field( $shipping_currency )
							]
						];
					}

					if ( ! empty( $settings['returnDays'] ) ) {
						$return_policy_category = ! empty( $settings['returnPolicyCategory'] ) ? $settings['returnPolicyCategory'] : 'https://schema.org/MerchantReturnFiniteReturnWindow';
						$variant_schema['offers']['hasMerchantReturnPolicy'] = [
							'@type' => 'MerchantReturnPolicy',
							'merchantReturnDays' => intval( $settings['returnDays'] ),
							'returnPolicyCategory' => esc_url( $return_policy_category )
						];
					}

					$schema['hasVariant'][] = $variant_schema;
				}

				// Fallback ProductGroupID
				$schema['productGroupID'] = $product->get_sku() ?: (string)$product->get_id();
			}

			// Add aggregate rating and reviews
			if ( $product->get_review_count() > 0 ) {
				$schema['aggregateRating'] = [
					'@type'       => 'AggregateRating',
					'ratingValue' => $product->get_average_rating(),
					'reviewCount' => $product->get_review_count(),
				];

				// Get up to 10 most recent reviews
				$reviews = get_comments( [
					'post_id' => $product->get_id(),
					'status'  => 'approve',
					'type'    => 'review',
					'number'  => 10,
				] );

				if ( $reviews ) {
					$schema['review'] = [];
					foreach ( $reviews as $review ) {
						$rating = intval( get_comment_meta( $review->comment_ID, 'rating', true ) );
						if ( $rating > 0 ) {
							$schema['review'][] = [
								'@type' => 'Review',
								'reviewRating' => [
									'@type' => 'Rating',
									'ratingValue' => (string)$rating,
									'bestRating' => '5',
								],
								'author' => [
									'@type' => 'Person',
									'name'  => sanitize_text_field( $review->comment_author ),
								],
								'reviewBody' => wp_strip_all_tags( $review->comment_content ),
								'datePublished' => get_comment_date( 'c', $review->comment_ID ),
							];
						}
					}
				}
			}

			return $schema;
		}
	}
}
