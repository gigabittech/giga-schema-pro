<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Giga_SP_Type_Custom extends Giga_SP_Schema_Base {
	public function get_schema(): array {
		if ( ! class_exists( 'Giga_SP_License' ) || ! Giga_SP_License::is_pro() ) {
			return [];
		}
		
		$post_id = get_queried_object_id();
		if ( ! $post_id ) return [];

		$custom_json = get_post_meta( $post_id, '_giga_sp_custom_json', true );
		if ( empty( $custom_json ) ) return [];

		return json_decode( $custom_json, true ) ?: [];
	}
}
