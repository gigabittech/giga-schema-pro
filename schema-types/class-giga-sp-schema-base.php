<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Schema_Base' ) ) {
	abstract class Giga_SP_Schema_Base {
		abstract public function get_schema(): array;
		
		protected function get_post() {
			return get_queried_object();
		}

		protected function get_url() {
			global $wp;
			return home_url( add_query_arg( [], $wp->request ) );
		}

		protected function get_site_name() {
			return get_bloginfo( 'name' );
		}
	}
}
