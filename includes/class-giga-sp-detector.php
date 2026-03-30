<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Detector' ) ) {
	class Giga_SP_Detector {
		public static $detected_types = [];

		public function __construct() {
			add_action( 'template_redirect', [ $this, 'start_buffer' ], -9999 );
			add_action( 'shutdown', [ $this, 'end_buffer' ], 9999 );

			$this->disable_competitor_schemas();
		}

		private function disable_competitor_schemas() {
			// Disable Yoast native product schema
			add_filter( 'wpseo_schema_product', '__return_false', 999 );
			
			// Disable Rank Math native product schema
			add_filter( 'rank_math/snippet/rich_snippet_product_entity', '__return_false', 999 );

			// Aggressively disable WooCommerce's default schema generator
			add_filter( 'woocommerce_structured_data_product', '__return_empty_array', 999 );
			add_filter( 'woocommerce_structured_data_breadcrumblist', '__return_empty_array', 999 );
			
			// Wait until WooCommerce is fully initialized to tear down its output hook just in case
			add_action( 'wp', [ $this, 'remove_wc_structured_data_hook' ], 99 );
		}

		public function remove_wc_structured_data_hook() {
			if ( function_exists( 'WC' ) && isset( WC()->structured_data ) ) {
				remove_action( 'wp_footer', [ WC()->structured_data, 'output_structured_data' ], 10 );
			}
		}

		public function start_buffer() {
			if ( ! is_admin() ) {
				ob_start( [ $this, 'scan_and_modify_output' ] );
			}
		}

		public function end_buffer() {
			if ( ob_get_level() > 0 ) {
				ob_end_flush();
			}
		}

		public function scan_and_modify_output( $buffer ) {
			if ( is_admin() ) {
				return $buffer;
			}

			$matches = [];
			// Extract all existing JSON-LD
			preg_match_all( '/<script type="application\/ld\+json">(.*?)<\/script>/is', $buffer, $matches );

			foreach ( $matches[1] as $json_string ) {
				$data = json_decode( trim( $json_string ), true );
				if ( isset( $data['@type'] ) ) {
					if ( is_array( $data['@type'] ) ) {
						self::$detected_types = array_merge( self::$detected_types, $data['@type'] );
					} else {
						self::$detected_types[] = $data['@type'];
					}
				}
				// Look through graphs Yoast likes to use
				if ( isset( $data['@graph'] ) && is_array( $data['@graph'] ) ) {
					foreach ( $data['@graph'] as $node ) {
						if ( isset( $node['@type'] ) ) {
							self::$detected_types[] = $node['@type'];
						}
					}
				}
			}

			self::$detected_types = array_unique( self::$detected_types );
			
			// Get instance and safety check
			$output_instance = Giga_SP_Output::get_instance();
			$schemas_to_render = $output_instance->get_schemas_to_render();
			$final_schemas = array_diff( $schemas_to_render, self::$detected_types );

			$our_json_ld = '';
			foreach ( $final_schemas as $type ) {
				$json = $output_instance->render_type( $type );
				if ( $json ) {
					$our_json_ld .= "\n" . '<script type="application/ld+json" id="giga-sp-' . esc_attr( strtolower( $type ) ) . '">' . wp_json_encode( $json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
				}
			}

			// Pro custom JSON-LD Support
			$post_id = get_queried_object_id();
			if ( $post_id && class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro() ) {
				$custom_json = get_post_meta( $post_id, '_giga_sp_custom_json', true );
				if ( ! empty( $custom_json ) ) {
					$our_json_ld .= "\n" . '<script type="application/ld+json" id="giga-sp-custom">' . $custom_json . '</script>' . "\n";
				}
			}

			// Inject directly before </head>
			if ( $our_json_ld ) {
				$buffer = str_replace( '</head>', $our_json_ld . '</head>', $buffer );
			}

			return $buffer;
		}

		public static function get_detected_seo_plugins() {
			$plugins = [];
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			
			$check_plugins = [
				'wordpress-seo/wp-seo.php' => 'Yoast SEO',
				'seo-by-rank-math/rank-math.php' => 'Rank Math',
				'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'AIOSEO',
				'wp-seopress/seopress.php' => 'SEOPress',
				'autodescription/autodescription.php' => 'The SEO Framework'
			];

			foreach ( $check_plugins as $path => $name ) {
				if ( is_plugin_active( $path ) ) {
					$plugins[] = $name;
				}
			}
			return $plugins;
		}
	}
	new Giga_SP_Detector();
}
