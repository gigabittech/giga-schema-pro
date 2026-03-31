<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_HowTo' ) ) {
	/**
	 * HowTo Schema Type
	 *
	 * Generates HowTo schema from HowTo blocks or custom fields
	 *
	 * @since 1.0.0
	 */
	class Giga_SP_Type_HowTo extends Giga_SP_Schema_Base {
		/**
		 * Get HowTo schema
		 *
		 * @since 1.0.0
		 * @return array Schema data
		 */
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
				return [];
			}

			// Try to get HowTo data from custom field
			$howto_data = get_post_meta( $post->ID, '_giga_sp_howto_data', true );
			if ( ! empty( $howto_data ) && is_array( $howto_data ) ) {
				return $this->build_howto_schema( $howto_data, $post );
			}

			// Try to extract HowTo from content (HowTo blocks)
			$howto_data = $this->extract_howto_from_content( $post->post_content );
			if ( ! empty( $howto_data ) ) {
				return $this->build_howto_schema( $howto_data, $post );
			}

			// Fallback: build from post content
			return $this->build_howto_from_post( $post );
		}

		/**
		 * Build HowTo schema from data
		 *
		 * @since 1.0.0
		 * @param array $data HowTo data
		 * @param WP_Post $post Post object
		 * @return array Schema data
		 */
		private function build_howto_schema( $data, $post ) {
			$schema = [
				'@context' => 'https://schema.org/',
				'@type' => 'HowTo',
				'name' => ! empty( $data['name'] ) ? wp_kses_post( $data['name'] ) : get_the_title( $post ),
				'description' => ! empty( $data['description'] ) ? wp_kses_post( $data['description'] ) : wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'image' => ! empty( $data['image'] ) ? esc_url( $data['image'] ) : get_the_post_thumbnail_url( $post->ID, 'full' ),
				'totalTime' => ! empty( $data['total_time'] ) ? $this->format_duration( $data['total_time'] ) : '',
				'estimatedCost' => ! empty( $data['cost'] ) ? [
					'@type' => 'MonetaryAmount',
					'currency' => 'USD',
					'value' => sanitize_text_field( $data['cost'] )
				] : '',
				'supply' => ! empty( $data['supplies'] ) ? $data['supplies'] : [],
				'tool' => ! empty( $data['tools'] ) ? $data['tools'] : [],
				'step' => []
			];

			if ( ! empty( $data['steps'] ) && is_array( $data['steps'] ) ) {
				foreach ( $data['steps'] as $index => $step ) {
					if ( ! empty( $step['text'] ) ) {
						$schema['step'][] = [
							'@type' => 'HowToStep',
							'position' => $index + 1,
							'text' => wp_kses_post( $step['text'] ),
							'name' => ! empty( $step['name'] ) ? wp_kses_post( $step['name'] ) : '',
							'image' => ! empty( $step['image'] ) ? esc_url( $step['image'] ) : ''
						];
					}
				}
			}

			return array_filter( $schema );
		}

		/**
		 * Build HowTo schema from post content
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object
		 * @return array Schema data
		 */
		private function build_howto_from_post( $post ) {
			// Extract steps from headings
			$content = $post->post_content;
			$steps = [];

			// Look for numbered steps or heading-based steps
			if ( preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $content, $headings ) ) {
				foreach ( $headings[1] as $index => $heading_text ) {
					$steps[] = [
						'@type' => 'HowToStep',
						'position' => $index + 1,
						'text' => wp_kses_post( $heading_text ),
						'name' => wp_kses_post( $heading_text )
					];
				}
			}

			if ( empty( $steps ) ) {
				return [];
			}

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'HowTo',
				'name' => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'image' => get_the_post_thumbnail_url( $post->ID, 'full' ),
				'step' => $steps
			];
		}

		/**
		 * Extract HowTo data from Gutenberg blocks
		 *
		 * @since 1.0.0
		 * @param string $content Post content
		 * @return array HowTo data
		 */
		private function extract_howto_from_content( $content ) {
			if ( ! has_blocks( $content ) ) {
				return [];
			}

			$blocks = parse_blocks( $content );
			$data = [
				'name' => '',
				'description' => '',
				'steps' => []
			];

			foreach ( $blocks as $block ) {
				if ( 'core/heading' === $block['blockName'] && empty( $data['name'] ) ) {
					$data['name'] = isset( $block['innerHTML'] ) ? strip_tags( $block['innerHTML'] ) : '';
				}

				if ( 'core/paragraph' === $block['blockName'] && empty( $data['description'] ) ) {
					$data['description'] = isset( $block['innerHTML'] ) ? strip_tags( $block['innerHTML'] ) : '';
				}

				if ( 'core/list' === $block['blockName'] ) {
					$items = isset( $block['innerHTML'] ) ? $block['innerHTML'] : '';
					if ( preg_match_all( '/<li[^>]*>(.*?)<\/li>/is', $items, $matches ) ) {
						foreach ( $matches[1] as $index => $item ) {
							$data['steps'][] = [
								'text' => strip_tags( $item ),
								'name' => strip_tags( $item )
							];
						}
					}
				}
			}

			return $data;
		}

		/**
		 * Format duration for schema
		 *
		 * @since 1.0.0
		 * @param int $minutes Duration in minutes
		 * @return string ISO 8601 duration format
		 */
		private function format_duration( $minutes ) {
			if ( ! is_numeric( $minutes ) ) {
				return '';
			}

			$hours = floor( $minutes / 60 );
			$mins = $minutes % 60;

			$duration = 'PT';
			if ( $hours > 0 ) {
				$duration .= $hours . 'H';
			}
			if ( $mins > 0 ) {
				$duration .= $mins . 'M';
			}

			return $duration;
		}
	}
}
