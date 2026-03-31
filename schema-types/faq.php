<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_FAQ' ) ) {
	/**
	 * FAQ Schema Type
	 *
	 * Generates FAQ schema from FAQ blocks or custom fields
	 *
	 * @since 1.0.0
	 */
	class Giga_SP_Type_FAQ extends Giga_SP_Schema_Base {
		/**
		 * Get FAQ schema
		 *
		 * @since 1.0.0
		 * @return array Schema data
		 */
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
				return [];
			}

			$faqs = [];

			// Try to get FAQs from custom field
			$faq_data = get_post_meta( $post->ID, '_giga_sp_faq_data', true );
			if ( ! empty( $faq_data ) && is_array( $faq_data ) ) {
				foreach ( $faq_data as $faq ) {
					if ( ! empty( $faq['question'] ) && ! empty( $faq['answer'] ) ) {
						$faqs[] = [
							'@type' => 'Question',
							'name' => wp_kses_post( $faq['question'] ),
							'acceptedAnswer' => [
								'@type' => 'Answer',
								'text' => wp_kses_post( $faq['answer'] )
							]
						];
					}
				}
			} else {
				// Try to extract FAQs from content (FAQ blocks or structured HTML)
				$faqs = $this->extract_faqs_from_content( $post->post_content );
			}

			if ( empty( $faqs ) ) {
				return [];
			}

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'FAQPage',
				'mainEntity' => $faqs
			];
		}

		/**
		 * Extract FAQs from post content
		 *
		 * @since 1.0.0
		 * @param string $content Post content
		 * @return array Extracted FAQs
		 */
		private function extract_faqs_from_content( $content ) {
			$faqs = [];

			// Look for FAQ blocks (Gutenberg)
			if ( has_blocks( $content ) ) {
				$blocks = parse_blocks( $content );
				foreach ( $blocks as $block ) {
					if ( 'core/details' === $block['blockName'] || 'core/faq' === $block['blockName'] ) {
						$question = isset( $block['attrs']['question'] ) ? $block['attrs']['question'] : '';
						$answer = isset( $block['innerHTML'] ) ? $block['innerHTML'] : '';

						if ( ! empty( $question ) && ! empty( $answer ) ) {
							$faqs[] = [
								'@type' => 'Question',
								'name' => wp_kses_post( $question ),
								'acceptedAnswer' => [
									'@type' => 'Answer',
									'text' => wp_kses_post( $answer )
								]
							];
						}
					}
				}
			}

			// Look for structured FAQ HTML (details/summary tags)
			if ( empty( $faqs ) && preg_match_all( '/<details[^>]*>(.*?)<\/details>/is', $content, $matches ) ) {
				foreach ( $matches[1] as $faq_html ) {
					$question = '';
					$answer = '';

					if ( preg_match( '/<summary[^>]*>(.*?)<\/summary>/is', $faq_html, $q_match ) ) {
						$question = strip_tags( $q_match[1] );
					}

					$answer = strip_tags( str_replace( $q_match[0], '', $faq_html ) );

					if ( ! empty( $question ) && ! empty( $answer ) ) {
						$faqs[] = [
							'@type' => 'Question',
							'name' => wp_kses_post( $question ),
							'acceptedAnswer' => [
								'@type' => 'Answer',
								'text' => wp_kses_post( $answer )
							]
						];
					}
				}
			}

			return $faqs;
		}
	}
}
