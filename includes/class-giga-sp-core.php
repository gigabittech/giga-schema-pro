<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Core' ) ) {
	class Giga_SP_Core {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->load_dependencies();
			$this->define_hooks();
		}

		private function load_dependencies() {
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-engine.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-detector.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-output.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-rules.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-types.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-woocommerce.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-validator.php';
			require_once GIGA_SP_PATH . 'includes/class-giga-sp-license.php';
			
			if ( is_admin() ) {
				require_once GIGA_SP_PATH . 'includes/class-giga-sp-admin.php';
			}

			// Base schema class
			require_once GIGA_SP_PATH . 'schema-types/class-giga-sp-schema-base.php';
		}

		private function define_hooks() {
			register_activation_hook( GIGA_SP_FILE, [ $this, 'activate' ] );
			register_deactivation_hook( GIGA_SP_FILE, [ $this, 'deactivate' ] );

			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		}

		public function load_textdomain() {
			load_plugin_textdomain( 'giga-schema-pro', false, dirname( plugin_basename( GIGA_SP_FILE ) ) . '/languages' );
		}

		public function activate() {
			$rules = get_option( 'giga_sp_rules', false );
			if ( ! $rules ) {
				$default_rules = [
					[ 'id' => 'rule_001', 'schema_type' => 'Article', 'target_type' => 'post', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_002', 'schema_type' => 'WebPage', 'target_type' => 'page', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_003', 'schema_type' => 'Product', 'target_type' => 'product', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_004', 'schema_type' => 'BreadcrumbList', 'target_type' => 'all', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_005', 'schema_type' => 'WebSite', 'target_type' => 'homepage', 'conditions' => [], 'priority' => 10, 'enabled' => true ],
					[ 'id' => 'rule_006', 'schema_type' => 'Organization', 'target_type' => 'homepage', 'conditions' => [], 'priority' => 10, 'enabled' => true ]
				];
				update_option( 'giga_sp_rules', $default_rules );
			}
			update_option( 'giga_sp_version', GIGA_SP_VERSION );
			flush_rewrite_rules();
		}

		public function deactivate() {
			flush_rewrite_rules();
		}
	}
}
