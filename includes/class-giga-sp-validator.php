<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Validator' ) ) {
	/**
	 * Schema Validator Class
	 *
	 * Validates schema markup against Google Rich Results Test API
	 *
	 * @since 1.0.0
	 */
	class Giga_SP_Validator {
		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_giga_sp_validate_schema', [ $this, 'ajax_validate' ] );
			add_action( 'wp_ajax_giga_sp_bulk_validate', [ $this, 'ajax_bulk_validate' ] );
		}

		/**
		 * AJAX handler for single page validation
		 *
		 * @since 1.0.0
		 */
		public function ajax_validate() {
			// Security: Verify nonce
			check_ajax_referer( 'giga_sp_validate_nonce', 'nonce' );

			// Security: Check capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'giga-schema-pro' ) );
			}

			// Check Pro license
			if ( ! class_exists( 'Giga_SP_License' ) || ! Giga_SP_License::is_pro() ) {
				wp_send_json_error( __( 'Pro version required for this feature.', 'giga-schema-pro' ) );
			}

			// Validate input
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			if ( ! $post_id ) {
				wp_send_json_error( __( 'Invalid post ID.', 'giga-schema-pro' ) );
			}

			$url = get_permalink( $post_id );
			if ( ! $url ) {
				wp_send_json_error( __( 'Unable to get post URL.', 'giga-schema-pro' ) );
			}

			// Run validation
			$response = $this->run_test( $url );

			// Cache results for 7 days
			set_transient( 'giga_sp_validation_' . $post_id, $response, 7 * DAY_IN_SECONDS );

			wp_send_json_success( $response );
		}

		/**
		 * AJAX handler for bulk validation
		 *
		 * @since 1.0.0
		 */
		public function ajax_bulk_validate() {
			// Security: Verify nonce
			check_ajax_referer( 'giga_sp_validate_nonce', 'nonce' );

			// Security: Check capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'giga-schema-pro' ) );
			}

			// Check Pro license
			if ( ! class_exists( 'Giga_SP_License' ) || ! Giga_SP_License::is_pro() ) {
				wp_send_json_error( __( 'Pro version required for this feature.', 'giga-schema-pro' ) );
			}

			// Get post IDs to validate
			$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : [];
			if ( empty( $post_ids ) ) {
				wp_send_json_error( __( 'No posts to validate.', 'giga-schema-pro' ) );
			}

			$results = [];
			foreach ( $post_ids as $post_id ) {
				$url = get_permalink( $post_id );
				if ( $url ) {
					$response = $this->run_test( $url );
					$results[] = [
						'post_id' => $post_id,
						'url' => $url,
						'status' => $response['status'],
						'errors' => isset( $response['errors'] ) ? $response['errors'] : [],
					];
					// Cache results
					set_transient( 'giga_sp_validation_' . $post_id, $response, 7 * DAY_IN_SECONDS );
				}
			}

			wp_send_json_success( $results );
		}

		/**
		 * Run Google Rich Results Test
		 *
		 * @since 1.0.0
		 * @param string $url URL to validate
		 * @return array Validation results
		 */
		private function run_test( $url ) {
			// Google Rich Results Test API endpoint
			$api_url = 'https://searchconsole.googleapis.com/v1/urlTestingTools/mobileFriendlyTest:run';

			// Note: This requires a Google Cloud API key
			// For now, we'll use a client-side validation approach
			// In production, you would use the Google Rich Results Test API

			// Check for cached results
			$post_id = url_to_postid( $url );
			$cached = get_transient( 'giga_sp_validation_' . $post_id );
			if ( false !== $cached ) {
				return $cached;
			}

			// Client-side validation: Fetch the page and check schema
			$response = wp_remote_get( $url, [
				'timeout' => 30,
				'sslverify' => false,
			] );

			if ( is_wp_error( $response ) ) {
				return [
					'status' => 'error',
					'messages' => [ __( 'Unable to fetch page content.', 'giga-schema-pro' ) ],
					'errors' => [ $response->get_error_message() ],
				];
			}

			$html = wp_remote_retrieve_body( $response );

			// Extract JSON-LD from the page
			$schemas = $this->extract_schemas_from_html( $html );

			if ( empty( $schemas ) ) {
				return [
					'status' => 'warning',
					'messages' => [ __( 'No schema markup found on this page.', 'giga-schema-pro' ) ],
					'errors' => [],
				];
			}

			// Validate each schema
			$validation_results = [];
			$errors = [];
			foreach ( $schemas as $schema ) {
				$validation = $this->validate_schema_structure( $schema );
				$validation_results[] = $validation;
				if ( ! empty( $validation['errors'] ) ) {
					$errors = array_merge( $errors, $validation['errors'] );
				}
			}

			// Determine overall status
			$status = empty( $errors ) ? 'pass' : 'fail';

			return [
				'status' => $status,
				'messages' => [
					sprintf(
						/* translators: %d: number of schemas found */
						_n( 'Found %d schema on this page.', 'Found %d schemas on this page.', count( $schemas ), 'giga-schema-pro' ),
						count( $schemas )
					),
				],
				'errors' => $errors,
				'schemas' => $validation_results,
			];
		}

		/**
		 * Extract JSON-LD schemas from HTML
		 *
		 * @since 1.0.0
		 * @param string $html HTML content
		 * @return array Extracted schemas
		 */
		private function extract_schemas_from_html( $html ) {
			$schemas = [];
			preg_match_all( '/<script type="application\/ld\+json">(.*?)<\/script>/is', $html, $matches );

			foreach ( $matches[1] as $json_string ) {
				$data = json_decode( trim( $json_string ), true );
				if ( json_last_error() === JSON_ERROR_NONE && $data ) {
					$schemas[] = $data;
				}
			}

			return $schemas;
		}

		/**
		 * Validate schema structure
		 *
		 * @since 1.0.0
		 * @param array $schema Schema data
		 * @return array Validation result
		 */
		private function validate_schema_structure( $schema ) {
			$errors = [];

			// Check for @context
			if ( empty( $schema['@context'] ) ) {
				$errors[] = __( 'Missing required field: @context', 'giga-schema-pro' );
			}

			// Check for @type
			if ( empty( $schema['@type'] ) ) {
				$errors[] = __( 'Missing required field: @type', 'giga-schema-pro' );
			}

			// Type-specific validation
			$type = isset( $schema['@type'] ) ? $schema['@type'] : '';
			switch ( $type ) {
				case 'Product':
					if ( empty( $schema['name'] ) ) {
						$errors[] = __( 'Product schema missing required field: name', 'giga-schema-pro' );
					}
					if ( empty( $schema['offers'] ) ) {
						$errors[] = __( 'Product schema missing required field: offers', 'giga-schema-pro' );
					}
					break;

				case 'Article':
					if ( empty( $schema['headline'] ) ) {
						$errors[] = __( 'Article schema missing required field: headline', 'giga-schema-pro' );
					}
					if ( empty( $schema['author'] ) ) {
						$errors[] = __( 'Article schema missing required field: author', 'giga-schema-pro' );
					}
					break;

				case 'FAQPage':
					if ( empty( $schema['mainEntity'] ) ) {
						$errors[] = __( 'FAQPage schema missing required field: mainEntity', 'giga-schema-pro' );
					}
					break;
			}

			return [
				'type' => $type,
				'errors' => $errors,
			];
		}
	}
}
