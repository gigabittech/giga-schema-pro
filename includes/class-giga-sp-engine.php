<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Engine' ) ) {
	class Giga_SP_Engine {
		public function __construct() {
			add_action( 'wp_head', [ $this, 'generate_schema' ], 1 );
		}

		public function generate_schema() {
			if ( is_admin() ) {
				return;
			}

			// Don't render schemas if disabled for this post
			$post_id = get_queried_object_id();
			if ( $post_id ) {
				$disabled_types = get_post_meta( $post_id, '_giga_sp_disabled_types', true );
				if ( ! is_array( $disabled_types ) ) {
					$disabled_types = [];
				}
			} else {
				$disabled_types = [];
			}

			$rules = get_option( 'giga_sp_rules', [] );
			if ( empty( $rules ) ) {
				$rules = [
					[ 'id' => 'rule_001', 'schema_type' => 'Article', 'target_type' => 'post', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_002', 'schema_type' => 'WebPage', 'target_type' => 'page', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_003', 'schema_type' => 'Product', 'target_type' => 'product', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_004', 'schema_type' => 'BreadcrumbList', 'target_type' => 'all', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_005', 'schema_type' => 'WebSite', 'target_type' => 'homepage', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_006', 'schema_type' => 'Organization', 'target_type' => 'homepage', 'conditions' => [], 'priority' => 10, 'enabled' => true ]
				];
				// Save it so we don't have to define it dynamically again
				update_option( 'giga_sp_rules', $rules );
			}
			$schemas_to_generate = [];

			foreach ( $rules as $rule ) {
				if ( ! $rule['enabled'] ) continue;
				if ( in_array( $rule['schema_type'], $disabled_types ) ) continue;

				if ( $this->matches_target( $rule['target_type'] ) ) {
					$schemas_to_generate[] = $rule['schema_type'];
				}
			}

			$schemas_to_generate = array_unique( $schemas_to_generate );
			Giga_SP_Output::get_instance()->set_schemas_to_render( $schemas_to_generate );
		}

		private function matches_target( $target ) {
			if ( 'all' === $target ) return true;
			if ( 'homepage' === $target && ( is_front_page() || is_home() ) ) return true;
			if ( is_singular( $target ) ) return true;
			return false;
		}
	}
	new Giga_SP_Engine();
}
