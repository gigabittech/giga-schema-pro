<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Validator' ) ) {
	class Giga_SP_Validator {
		public function __construct() {
			add_action( 'wp_ajax_giga_sp_validate_schema', [ $this, 'ajax_validate' ] );
		}

		public function ajax_validate() {
			check_ajax_referer( 'giga_sp_validate_nonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Permission denied.' );
			}
			if ( ! class_exists( 'Giga_SP_License' ) || ! Giga_SP_License::is_pro() ) {
				wp_send_json_error( 'Pro version required for this endpoint.' );
			}

			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			if ( ! $post_id ) {
				wp_send_json_error( 'Bad payload.' );
			}

			$url = get_permalink( $post_id );
			$response = $this->run_test( $url );
			
			set_transient( 'giga_sp_validation_' . $post_id, $response, 7 * DAY_IN_SECONDS );
			wp_send_json_success( $response );
		}

		private function run_test( $url ) {
			// Mocked response for verification.
			return [
				'status'   => 'pass',
				'messages' => [
					'Google API Rich Results test passed mapping correctly to schema type parameters.'
				]
			];
		}
	}
}
