<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Output' ) ) {
	class Giga_SP_Output {
		private static $instance = null;
		private $schemas_to_render = [];

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function set_schemas_to_render( array $types ) {
			$this->schemas_to_render = $types;
		}

		public function get_schemas_to_render() {
			return $this->schemas_to_render;
		}

		public function render_type( $type ) {
			$class_name = 'Giga_SP_Type_' . str_replace( '-', '_', $type );
			$file_path = GIGA_SP_PATH . 'schema-types/' . strtolower( $type ) . '.php';

			if ( file_exists( $file_path ) ) {
				require_once $file_path;
				if ( class_exists( $class_name ) ) {
					$builder = new $class_name();
					return $builder->get_schema();
				}
			}
			return false;
		}
	}
}
