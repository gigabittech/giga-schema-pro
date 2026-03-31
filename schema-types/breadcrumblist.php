<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_BreadcrumbList' ) ) {
	/**
	 * BreadcrumbList Schema Type
	 *
	 * Generates breadcrumb schema with WooCommerce category hierarchy support
	 *
	 * @since 1.0.0
	 */
	class Giga_SP_Type_BreadcrumbList extends Giga_SP_Schema_Base {
		/**
		 * Get breadcrumb schema
		 *
		 * @since 1.0.0
		 * @return array Schema data
		 */
		public function get_schema(): array {
			$items = [
				[
					'@type' => 'ListItem',
					'position' => 1,
					'name' => __( 'Home', 'giga-schema-pro' ),
					'item' => home_url()
				]
			];

			$post = $this->get_post();

			// If it's a WooCommerce product, follow category hierarchy
			if ( $post && is_a( $post, 'WP_Post' ) && 'product' === $post->post_type && function_exists( 'wc_get_product_terms' ) ) {
				$product = wc_get_product( $post->ID );
				if ( $product ) {
					$categories = wc_get_product_terms( $post->ID, 'product_cat', [ 'orderby' => 'parent', 'order' => 'ASC' ] );

					if ( ! empty( $categories ) ) {
						// Get the first category's hierarchy
						$category = $categories[0];
						$category_hierarchy = $this->get_category_hierarchy( $category->term_id, 'product_cat' );

						// Add category breadcrumbs in reverse order (parent to child)
						$position = 2;
						foreach ( array_reverse( $category_hierarchy ) as $cat ) {
							$items[] = [
								'@type' => 'ListItem',
								'position' => $position++,
								'name' => $cat->name,
								'item' => get_term_link( $cat->term_id, 'product_cat' )
							];
						}
					}
				}
			} elseif ( $post && is_a( $post, 'WP_Post' ) ) {
				// For posts/pages, get category hierarchy
				if ( 'post' === $post->post_type ) {
					$categories = get_the_category( $post->ID );
					if ( ! empty( $categories ) ) {
						$category = $categories[0];
						$category_hierarchy = $this->get_category_hierarchy( $category->term_id, 'category' );

						$position = 2;
						foreach ( array_reverse( $category_hierarchy ) as $cat ) {
							$items[] = [
								'@type' => 'ListItem',
								'position' => $position++,
								'name' => $cat->name,
								'item' => get_category_link( $cat->term_id )
							];
						}
					}
				}
			}

			// Add current page as last item
			if ( $post && is_a( $post, 'WP_Post' ) ) {
				$items[] = [
					'@type' => 'ListItem',
					'position' => count( $items ) + 1,
					'name' => get_the_title( $post ),
					'item' => $this->get_url()
				];
			}

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'BreadcrumbList',
				'itemListElement' => $items
			];
		}

		/**
		 * Get category hierarchy (parent to child)
		 *
		 * @since 1.0.0
		 * @param int $term_id Category term ID
		 * @param string $taxonomy Taxonomy name
		 * @return array Category hierarchy
		 */
		private function get_category_hierarchy( $term_id, $taxonomy ) {
			$hierarchy = [];
			$term = get_term( $term_id, $taxonomy );

			if ( ! $term || is_wp_error( $term ) ) {
				return $hierarchy;
			}

			$hierarchy[] = $term;

			// Recursively get parent categories
			while ( $term->parent !== 0 ) {
				$term = get_term( $term->parent, $taxonomy );
				if ( ! $term || is_wp_error( $term ) ) {
					break;
				}
				$hierarchy[] = $term;
			}

			return $hierarchy;
		}
	}
}
