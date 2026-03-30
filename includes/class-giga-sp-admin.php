<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Admin' ) ) {
	class Giga_SP_Admin {
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'register_menus' ] );
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
			add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
		}

		public function register_menus() {
			add_menu_page(
				__( 'Giga Schema Pro', 'giga-schema-pro' ),
				__( 'Giga Schema', 'giga-schema-pro' ),
				'manage_options',
				'giga-schema-pro',
				[ $this, 'render_dashboard' ],
				'dashicons-feedback',
				80
			);

			add_submenu_page( 'giga-schema-pro', __( 'Rules', 'giga-schema-pro' ), __( 'Rules', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-rules', [ $this, 'render_rules' ] );
			add_submenu_page( 'giga-schema-pro', __( 'Schema Types', 'giga-schema-pro' ), __( 'Schema Types', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-types', [ $this, 'render_placeholder' ] );
			add_submenu_page( 'giga-schema-pro', __( 'WooCommerce', 'giga-schema-pro' ), __( 'WooCommerce', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-woo', [ $this, 'render_placeholder' ] );
			add_submenu_page( 'giga-schema-pro', __( 'Validation', 'giga-schema-pro' ), __( 'Validation', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-validation', [ $this, 'render_placeholder' ] );
			add_submenu_page( 'giga-schema-pro', __( 'Settings', 'giga-schema-pro' ), __( 'Settings', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-settings', [ $this, 'render_placeholder' ] );
		}

		public function render_dashboard() {
			if ( ! current_user_can('manage_options') ) return;
			$plugins = Giga_SP_Detector::get_detected_seo_plugins();
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Giga Schema Pro Dashboard', 'giga-schema-pro' ); ?></h1>
				<?php if ( ! empty($plugins) ): ?>
					<div class="notice notice-info"><p>
					<?php printf( 
						esc_html__( 'Detected SEO plugin: %s. %s is generating duplicate output. Giga Schema Pro will smartly inject alongside missing elements avoiding duplication.', 'giga-schema-pro' ), 
						esc_html( implode(', ', $plugins) ), 
						esc_html( implode(', ', $plugins) ) 
					); ?>
					</p></div>
				<?php endif; ?>
			</div>
			<?php
		}

		public function render_rules() {
			if ( ! current_user_can('manage_options') ) return;
			$rules = Giga_SP_Rules::get_rules();
			echo '<div class="wrap"><h1>' . esc_html__( 'Rules', 'giga-schema-pro' ) . '</h1><table class="wp-list-table widefat fixed striped"><thead><tr><th>ID</th><th>Type</th><th>Target</th><th>Status</th></tr></thead><tbody>';
			foreach($rules as $r) {
				echo '<tr><td>'.esc_html($r['id']).'</td><td>'.esc_html($r['schema_type']).'</td><td>'.esc_html($r['target_type']).'</td><td>'.($r['enabled'] ? 'Enabled' : 'Disabled').'</td></tr>';
			}
			echo '</tbody></table></div>';
		}

		public function render_placeholder() {
			echo '<div class="wrap"><h1>Coming Soon</h1></div>';
		}

		public function add_meta_boxes() {
			$screens = [ 'post', 'page', 'product' ];
			foreach ( $screens as $screen ) {
				add_meta_box( 'giga_sp_meta_box', __( 'Giga Schema Pro Settings', 'giga-schema-pro' ), [ $this, 'render_meta_box' ], $screen, 'side' );
			}
		}

		public function render_meta_box( $post ) {
			wp_nonce_field( 'giga_sp_meta_nonce', 'giga_sp_meta_nonce_field' );
			$disabled = get_post_meta( $post->ID, '_giga_sp_disabled_types', true ) ?: [];
			$custom = get_post_meta( $post->ID, '_giga_sp_custom_json', true ) ?: '';
			
			echo '<p><strong>' . esc_html__( 'Disable specific schemas for this page:', 'giga-schema-pro' ) . '</strong><br>';
			echo '<input type="checkbox" name="giga_sp_disable[]" value="Article" '.(in_array('Article', $disabled)?'checked':'').'> Article<br>';
			echo '<input type="checkbox" name="giga_sp_disable[]" value="Product" '.(in_array('Product', $disabled)?'checked':'').'> Product<br>';
			echo '</p>';

			if ( class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro() ) {
				echo '<p><strong>' . esc_html__( 'Custom JSON-LD:', 'giga-schema-pro' ) . '</strong><br><textarea name="giga_sp_custom" style="width:100%;height:100px;">'.esc_textarea($custom).'</textarea></p>';
				echo '<button type="button" class="button" id="giga-sp-validate-content">' . esc_html__( 'Validate Schema', 'giga-schema-pro' ) . '</button>';
			} else {
				echo '<p><a href="#">' . esc_html__( 'Upgrade to Giga Schema Pro', 'giga-schema-pro' ) . '</a> to unlock Custom JSON-LD insertion & Validation test runs.</p>';
			}
		}

		public function save_meta_boxes( $post_id ) {
			if ( ! isset( $_POST['giga_sp_meta_nonce_field'] ) || ! wp_verify_nonce( wp_unslash( $_POST['giga_sp_meta_nonce_field'] ), 'giga_sp_meta_nonce' ) ) return;
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			if ( ! current_user_can( 'edit_post', $post_id ) ) return;

			$disabled = isset($_POST['giga_sp_disable']) ? array_map('sanitize_text_field', wp_unslash($_POST['giga_sp_disable'])) : [];
			update_post_meta( $post_id, '_giga_sp_disabled_types', $disabled );

			if ( isset($_POST['giga_sp_custom']) && current_user_can('unfiltered_html') ) {
				update_post_meta( $post_id, '_giga_sp_custom_json', trim( wp_unslash( $_POST['giga_sp_custom'] ) ) );
			}
		}
	}
}
