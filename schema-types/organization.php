<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_Organization' ) ) {
	/**
	 * Organization Schema Type
	 *
	 * Generates Organization schema from settings
	 *
	 * @since 1.0.0
	 */
	class Giga_SP_Type_Organization extends Giga_SP_Schema_Base {
		/**
		 * Get Organization schema
		 *
		 * @since 1.0.0
		 * @return array Schema data
		 */
		public function get_schema(): array {
			$settings = get_option( 'giga_sp_settings', [] );

			$schema = [
				'@context' => 'https://schema.org/',
				'@type' => 'Organization',
				'name' => isset( $settings['organization_name'] ) ? sanitize_text_field( $settings['organization_name'] ) : $this->get_site_name(),
				'url' => isset( $settings['organization_url'] ) ? esc_url( $settings['organization_url'] ) : home_url(),
			];

			// Add description
			if ( ! empty( $settings['organization_description'] ) ) {
				$schema['description'] = wp_kses_post( $settings['organization_description'] );
			}

			// Add logo
			if ( ! empty( $settings['organization_logo'] ) ) {
				$schema['logo'] = esc_url( $settings['organization_logo'] );
			}

			// Add social profiles
			$social_profiles = $this->get_social_profiles( $settings );
			if ( ! empty( $social_profiles ) ) {
				$schema['sameAs'] = $social_profiles;
			}

			return $schema;
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
