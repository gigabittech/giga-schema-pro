<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_LocalBusiness' ) ) {
	/**
	 * LocalBusiness Schema Type
	 *
	 * Generates LocalBusiness schema from settings or custom fields
	 *
	 * @since 1.0.0
	 */
	class Giga_SP_Type_LocalBusiness extends Giga_SP_Schema_Base {
		/**
		 * Get LocalBusiness schema
		 *
		 * @since 1.0.0
		 * @return array Schema data
		 */
		public function get_schema(): array {
			$settings = get_option( 'giga_sp_settings', [] );

			// Try to get LocalBusiness data from custom field first
			$post = $this->get_post();
			if ( $post && is_a( $post, 'WP_Post' ) ) {
				$lb_data = get_post_meta( $post->ID, '_giga_sp_localbusiness_data', true );
				if ( ! empty( $lb_data ) && is_array( $lb_data ) ) {
					return $this->build_localbusiness_schema( $lb_data );
				}
			}

			// Fallback to global settings
			$lb_data = [
				'name' => isset( $settings['organization_name'] ) ? $settings['organization_name'] : $this->get_site_name(),
				'description' => isset( $settings['organization_description'] ) ? $settings['organization_description'] : get_bloginfo( 'description' ),
				'url' => isset( $settings['organization_url'] ) ? $settings['organization_url'] : home_url(),
				'logo' => isset( $settings['organization_logo'] ) ? $settings['organization_logo'] : get_site_icon_url(),
				'address' => isset( $settings['lb_address'] ) ? $settings['lb_address'] : '',
				'phone' => isset( $settings['lb_phone'] ) ? $settings['lb_phone'] : '',
				'email' => isset( $settings['lb_email'] ) ? $settings['lb_email'] : get_option( 'admin_email' ),
				'opening_hours' => isset( $settings['lb_hours'] ) ? $settings['lb_hours'] : '',
				'price_range' => isset( $settings['lb_price_range'] ) ? $settings['lb_price_range'] : '',
				'same_as' => $this->get_social_profiles( $settings ),
			];

			return $this->build_localbusiness_schema( $lb_data );
		}

		/**
		 * Build LocalBusiness schema from data
		 *
		 * @since 1.0.0
		 * @param array $data LocalBusiness data
		 * @return array Schema data
		 */
		private function build_localbusiness_schema( $data ) {
			$schema = [
				'@context' => 'https://schema.org/',
				'@type' => 'LocalBusiness',
				'name' => ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : $this->get_site_name(),
				'description' => ! empty( $data['description'] ) ? wp_kses_post( $data['description'] ) : '',
				'url' => ! empty( $data['url'] ) ? esc_url( $data['url'] ) : home_url(),
			];

			// Add image/logo
			if ( ! empty( $data['logo'] ) ) {
				$schema['image'] = esc_url( $data['logo'] );
			}

			// Add address (required for rich snippets)
			if ( ! empty( $data['address'] ) ) {
				$schema['address'] = [
					'@type' => 'PostalAddress',
					'streetAddress' => sanitize_text_field( $data['address'] ),
				];

				// Try to parse address components
				if ( is_array( $data['address'] ) ) {
					$schema['address'] = array_merge( $schema['address'], [
						'addressLocality' => ! empty( $data['city'] ) ? sanitize_text_field( $data['city'] ) : '',
						'addressRegion' => ! empty( $data['state'] ) ? sanitize_text_field( $data['state'] ) : '',
						'postalCode' => ! empty( $data['zip'] ) ? sanitize_text_field( $data['zip'] ) : '',
						'addressCountry' => ! empty( $data['country'] ) ? sanitize_text_field( $data['country'] ) : '',
					] );
				}
			}

			// Add phone
			if ( ! empty( $data['phone'] ) ) {
				$schema['telephone'] = sanitize_text_field( $data['phone'] );
			}

			// Add email
			if ( ! empty( $data['email'] ) ) {
				$schema['email'] = sanitize_email( $data['email'] );
			}

			// Add opening hours
			if ( ! empty( $data['opening_hours'] ) ) {
				if ( is_array( $data['opening_hours'] ) ) {
					$schema['openingHoursSpecification'] = [];
					foreach ( $data['opening_hours'] as $hours ) {
						if ( ! empty( $hours['day'] ) && ! empty( $hours['opens'] ) && ! empty( $hours['closes'] ) ) {
							$schema['openingHoursSpecification'][] = [
								'@type' => 'OpeningHoursSpecification',
								'dayOfWeek' => sanitize_text_field( $hours['day'] ),
								'opens' => sanitize_text_field( $hours['opens'] ),
								'closes' => sanitize_text_field( $hours['closes'] )
							];
						}
					}
				} else {
					// Simple string format
					$schema['openingHours'] = sanitize_text_field( $data['opening_hours'] );
				}
			}

			// Add price range
			if ( ! empty( $data['price_range'] ) ) {
				$schema['priceRange'] = sanitize_text_field( $data['price_range'] );
			}

			// Add social profiles
			if ( ! empty( $data['same_as'] ) && is_array( $data['same_as'] ) ) {
				$schema['sameAs'] = array_filter( array_map( 'esc_url', $data['same_as'] ) );
			}

			return array_filter( $schema );
		}

		/**
		 * Get social profiles from settings
		 *
		 * @since 1.0.0
		 * @param array $settings Plugin settings
		 * @return array Social profile URLs
		 */
		private function get_social_profiles( $settings ) {
			$profiles = [];

			if ( ! empty( $settings['social_facebook'] ) ) {
				$profiles[] = esc_url( $settings['social_facebook'] );
			}
			if ( ! empty( $settings['social_twitter'] ) ) {
				$profiles[] = esc_url( $settings['social_twitter'] );
			}
			if ( ! empty( $settings['social_linkedin'] ) ) {
				$profiles[] = esc_url( $settings['social_linkedin'] );
			}
			if ( ! empty( $settings['social_instagram'] ) ) {
				$profiles[] = esc_url( $settings['social_instagram'] );
			}

			return $profiles;
		}
	}
}
