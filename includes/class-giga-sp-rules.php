<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Rules' ) ) {
	class Giga_SP_Rules {
		public static function get_rules() {
			return get_option( 'giga_sp_rules', [] );
		}

		public static function update_rule( $rule_data ) {
			$rules = self::get_rules();
			$rule_id = isset( $rule_data['id'] ) ? $rule_data['id'] : 'rule_' . wp_generate_password( 6, false );
			$rule_data['id'] = $rule_id;
			
			$updated = false;
			foreach ( $rules as $key => $rule ) {
				if ( $rule['id'] === $rule_id ) {
					$rules[ $key ] = $rule_data;
					$updated = true;
					break;
				}
			}
			if ( ! $updated ) {
				$rules[] = $rule_data;
			}
			
			update_option( 'giga_sp_rules', $rules );
			return $rule_id;
		}

		public static function delete_rule( $rule_id ) {
			$rules = array_values( array_filter( self::get_rules(), function ( $rule ) use ( $rule_id ) {
				return $rule['id'] !== $rule_id;
			}) );
			update_option( 'giga_sp_rules', $rules );
		}
	}
}
