<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Admin' ) ) {
	class Giga_SP_Admin {
		public function __construct() {
			add_action( 'admin_menu',            [ $this, 'register_menus' ] );
			add_action( 'add_meta_boxes',        [ $this, 'add_meta_boxes' ] );
			add_action( 'save_post',             [ $this, 'save_meta_boxes' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

			// AJAX: rule management (available to all logged-in admins)
			add_action( 'wp_ajax_giga_sp_save_rule',   [ $this, 'ajax_save_rule' ] );
			add_action( 'wp_ajax_giga_sp_delete_rule', [ $this, 'ajax_delete_rule' ] );
			add_action( 'wp_ajax_giga_sp_toggle_rule', [ $this, 'ajax_toggle_rule' ] );
		}

		/**
		 * Enqueue admin CSS and JavaScript
		 *
		 * @since 1.0.0
		 * @param string $hook Current admin page hook
		 */
		public function enqueue_admin_assets( $hook ) {
			// Only load assets on our plugin pages and post edit screens
			if ( strpos( $hook, 'giga-schema-pro' ) === false && ! in_array( $hook, [ 'post.php', 'post-new.php', 'page.php', 'page-new.php' ] ) ) {
				return;
			}

			// Enqueue admin CSS
			wp_enqueue_style(
				'giga-sp-admin',
				GIGA_SP_URL . 'admin/css/giga-sp-admin.css',
				[],
				GIGA_SP_VERSION
			);

			// Enqueue admin JS
			wp_enqueue_script(
				'giga-sp-admin',
				GIGA_SP_URL . 'admin/js/giga-sp-admin.js',
				[ 'jquery' ],
				GIGA_SP_VERSION,
				true
			);

			// Localize script for AJAX
			wp_localize_script( 'giga-sp-admin', 'gigaSpAdmin', [
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'giga_sp_admin_nonce' ),
				'isPro' => class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro(),
			] );
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
			add_submenu_page( 'giga-schema-pro', __( 'Schema Types', 'giga-schema-pro' ), __( 'Schema Types', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-types', [ $this, 'render_schema_types' ] );
			add_submenu_page( 'giga-schema-pro', __( 'WooCommerce', 'giga-schema-pro' ), __( 'WooCommerce', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-woo', [ $this, 'render_woocommerce_settings' ] );
			add_submenu_page( 'giga-schema-pro', __( 'Validation', 'giga-schema-pro' ), __( 'Validation', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-validation', [ $this, 'render_validation' ] );
			add_submenu_page( 'giga-schema-pro', __( 'Settings', 'giga-schema-pro' ), __( 'Settings', 'giga-schema-pro' ), 'manage_options', 'giga-schema-pro-settings', [ $this, 'render_settings' ] );
		}

		public function render_dashboard() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$plugins      = Giga_SP_Detector::get_detected_seo_plugins();
			$all_types    = Giga_SP_Types::get_all_types();
			$is_pro       = class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro();
			$free_count   = count( $all_types['free'] );
			$pro_count    = count( $all_types['pro'] );
			$total_count  = $free_count + $pro_count;
			$settings     = get_option( 'giga_sp_settings', [] );
			$woo_settings = get_option( 'giga_sp_woocommerce_settings', [] );
			$org_set      = ! empty( $settings['organization_name'] );
			$woo_active   = class_exists( 'WooCommerce' );
			$woo_set      = ! empty( $woo_settings['defaultBrand'] ) || ! empty( $woo_settings['shippingRate'] );
			
			// Get analytics data
			$analytics = $this->get_analytics_data();
			?>
			<div class="giga-sp-admin">
				<div class="giga-sp-container">
					
					<!-- Modern Dashboard Header -->
					<div class="giga-sp-dashboard-header">
						<div class="giga-sp-dashboard-content">
							<div class="giga-sp-header-main">
								<div class="giga-sp-header-left">
									<div class="giga-sp-header-icon">
										<span class="dashicons dashicons-feedback"></span>
									</div>
									<div class="giga-sp-header-title">
										<h1><?php esc_html_e( 'Giga Schema Pro', 'giga-schema-pro' ); ?></h1>
										<p class="giga-sp-header-subtitle"><?php esc_html_e( 'Advanced JSON-LD Schema Generation for WordPress', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
								<div class="giga-sp-header-actions">
									<?php if ( ! $is_pro ) : ?>
										<a href="#" class="giga-btn giga-btn-primary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Upgrade to Pro', 'giga-schema-pro' ); ?>
										</a>
									<?php endif; ?>
									<a href="#" class="giga-btn giga-btn-secondary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
										<span class="dashicons dashicons-cog"></span>
										<?php esc_html_e( 'Settings', 'giga-schema-pro' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>

					<!-- Modern Navigation Tabs -->
				

					<!-- SEO Plugin Detection Notice -->
					<?php if ( ! empty( $plugins ) ) : ?>
						<div class="giga-card giga-card-notice">
							
							<div class="giga-card-body">
								<p>
									<strong><?php esc_html_e( 'Detected:', 'giga-schema-pro' ); ?></strong>
									<?php
										printf(
											/* translators: %s: list of detected SEO plugins */
											esc_html__( '%s is active. Giga Schema Pro will smartly inject only missing schema types to avoid duplication.', 'giga-schema-pro' ),
											esc_html( implode( ', ', $plugins ) )
										);
									?>
								</p>
								<p class="giga-card-description">
									<?php esc_html_e( 'This ensures no duplicate schema markup that Google penalizes.', 'giga-schema-pro' ); ?>
								</p>
							</div>
						</div>
					<?php endif; ?>

					<!-- Modern Stats Grid -->
					<div class="giga-stats-grid">
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon">📊</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $total_count ); ?></h3>
									<p><?php esc_html_e( 'Schema Types', 'giga-schema-pro' ); ?></p>
								</div>
								<?php if ( $is_pro ) : ?>
									<div class="giga-stat-trend">+<?php echo esc_html( $pro_count ); ?> Pro</div>
								<?php endif; ?>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #10b981;">✓</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $analytics['pages_with_schema'] ); ?></h3>
									<p><?php esc_html_e( 'Pages with Schema', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #3b82f6;">🔍</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $analytics['validation_passed'] ); ?></h3>
									<p><?php esc_html_e( 'Validated', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #f59e0b;">⚡</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $analytics['rules_active'] ); ?></h3>
									<p><?php esc_html_e( 'Active Rules', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Main Content Grid -->
					<div class="giga-card-grid">
						
						<!-- Setup Checklist Card -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-yes-alt"></span>
									<?php esc_html_e( 'Setup Checklist', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-checklist">
										<a href="?page=giga-schema-pro-settings" class="giga-check-item <?php echo $org_set ? 'completed' : ''; ?>">
											<div class="giga-check-icon"></div>
											<div class="giga-check-content">
												<div class="giga-check-title"><?php esc_html_e( 'Configure Organization Info', 'giga-schema-pro' ); ?></div>
												<div class="giga-check-description"><?php esc_html_e( 'Set your site name, logo, and social profiles', 'giga-schema-pro' ); ?></div>
											</div>
											<?php if ( $org_set ) : ?>
												<span class="giga-sp-badge giga-sp-pass">✓ Done</span>
											<?php endif; ?>
										</a>
										
										<?php if ( $woo_active ) : ?>
											<a href="?page=giga-schema-pro-woo" class="giga-check-item <?php echo $woo_set ? 'completed' : ''; ?>">
												<div class="giga-check-icon"></div>
												<div class="giga-check-content">
													<div class="giga-check-title"><?php esc_html_e( 'Configure WooCommerce Settings', 'giga-schema-pro' ); ?></div>
													<div class="giga-check-description"><?php esc_html_e( 'Set default brand, shipping rates, and return policies', 'giga-schema-pro' ); ?></div>
												</div>
												<?php if ( $woo_set ) : ?>
													<span class="giga-sp-badge giga-sp-pass">✓ Done</span>
												<?php endif; ?>
											</a>
										<?php endif; ?>
										
										
										<a href="?page=giga-schema-pro-rules" class="giga-check-item <?php echo $analytics['rules_active'] > 0 ? 'completed' : ''; ?>">
											<div class="giga-check-icon"></div>
											<div class="giga-check-content">
												<div class="giga-check-title"><?php esc_html_e( 'Create Auto-Generation Rules', 'giga-schema-pro' ); ?></div>
												<div class="giga-check-description"><?php esc_html_e( 'Set rules to automatically apply schema to content', 'giga-schema-pro' ); ?></div>
											</div>
											<?php if ( $analytics['rules_active'] > 0 ) : ?>
												<span class="giga-sp-badge giga-sp-pass">✓ Done</span>
											<?php endif; ?>
										</a>
									</div>
								
								<?php if ( ! $org_set || ! ( $woo_active && $woo_set ) || $analytics['rules_active'] === 0 ) : ?>
									<div class="giga-card-footer">
										<a href="#" class="giga-btn giga-btn-primary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
											<?php esc_html_e( 'Complete Setup', 'giga-schema-pro' ); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Quick Actions Card -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-rocket"></span>
									<?php esc_html_e( 'Quick Actions', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-action-grid">
									<a href="#" class="giga-action-item" onclick="window.location.href='?page=giga-schema-pro-validation'; return false;">
										<div class="giga-action-icon">🔍</div>
										<div class="giga-action-title"><?php esc_html_e( 'Validate Schema', 'giga-schema-pro' ); ?></div>
										<div class="giga-action-description"><?php esc_html_e( 'Test all pages against Google standards', 'giga-schema-pro' ); ?></div>
									</a>
									
									<a href="#" class="giga-action-item" onclick="window.location.href='?page=giga-schema-pro-rules'; return false;">
										<div class="giga-action-icon">📋</div>
										<div class="giga-action-title"><?php esc_html_e( 'Manage Rules', 'giga-schema-pro' ); ?></div>
										<div class="giga-action-description"><?php esc_html_e( 'Configure auto-generation rules', 'giga-schema-pro' ); ?></div>
									</a>
									
									<a href="#" class="giga-action-item" onclick="window.location.href='?page=giga-schema-pro-types'; return false;">
										<div class="giga-action-icon">🏷️</div>
										<div class="giga-action-title"><?php esc_html_e( 'Schema Types', 'giga-schema-pro' ); ?></div>
										<div class="giga-action-description"><?php esc_html_e( 'View and manage all schema types', 'giga-schema-pro' ); ?></div>
									</a>
									
									<?php if ( $woo_active ) : ?>
										<a href="#" class="giga-action-item" onclick="window.location.href='?page=giga-schema-pro-woo'; return false;">
											<div class="giga-action-icon">🛒</div>
											<div class="giga-action-title"><?php esc_html_e( 'WooCommerce', 'giga-schema-pro' ); ?></div>
											<div class="giga-action-description"><?php esc_html_e( 'Configure product schema settings', 'giga-schema-pro' ); ?></div>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- Schema Performance Card -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-chart-line"></span>
									<?php esc_html_e( 'Schema Performance', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-performance-metrics">
									<div class="giga-metric-item">
										<div class="giga-metric-label"><?php esc_html_e( 'Schema Coverage', 'giga-schema-pro' ); ?></div>
										<div class="giga-metric-value"><?php echo esc_html( round( ( $analytics['pages_with_schema'] / max( 1, $analytics['total_pages'] ) ) * 100 ) ); ?>%</div>
									</div>
									
									<div class="giga-progress-bar">
										<div class="giga-progress-fill" data-progress="<?php echo esc_attr( round( ( $analytics['pages_with_schema'] / max( 1, $analytics['total_pages'] ) ) * 100 ) ); ?>"></div>
										<div class="giga-progress-text"><?php echo esc_html( round( ( $analytics['pages_with_schema'] / max( 1, $analytics['total_pages'] ) ) * 100 ) ); ?>%</div>
									</div>
									
									<div class="giga-metrics-grid">
										<div class="giga-metric-item">
											<div class="giga-metric-label"><?php esc_html_e( 'Total Pages', 'giga-schema-pro' ); ?></div>
											<div class="giga-metric-value"><?php echo esc_html( $analytics['total_pages'] ); ?></div>
										</div>
										
										<div class="giga-metric-item">
											<div class="giga-metric-label"><?php esc_html_e( 'Schema Pages', 'giga-schema-pro' ); ?></div>
											<div class="giga-metric-value"><?php echo esc_html( $analytics['pages_with_schema'] ); ?></div>
										</div>
										
										<div class="giga-metric-item">
											<div class="giga-metric-label"><?php esc_html_e( 'Validated', 'giga-schema-pro' ); ?></div>
											<div class="giga-metric-value"><?php echo esc_html( $analytics['validation_passed'] ); ?></div>
										</div>
										
										<div class="giga-metric-item">
											<div class="giga-metric-label"><?php esc_html_e( 'Issues', 'giga-schema-pro' ); ?></div>
											<div class="giga-metric-value" style="color: #ef4444;"><?php echo esc_html( $analytics['validation_failed'] ); ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Recent Activity Card -->
						
					</div>

				</div>
			</div>
			<?php
		}
		
		/**
		 * Get live analytics data for the dashboard.
		 * All values are queried directly from the WordPress database.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		private function get_analytics_data() {

			/* ---- 1. Active rules ---- */
			$rules        = get_option( 'giga_sp_rules', [] );
			$rules_active = count( array_filter( $rules, function( $r ) {
				return ! empty( $r['enabled'] );
			} ) );

			/* ---- 2. Total published content ---- */
			$post_counts  = wp_count_posts( 'post' );
			$page_counts  = wp_count_posts( 'page' );
			$total_pages  = (int) $post_counts->publish + (int) $page_counts->publish;
			if ( class_exists( 'WooCommerce' ) ) {
				$prod_counts = wp_count_posts( 'product' );
				$total_pages += (int) $prod_counts->publish;
			}

			/* ---- 3. Pages covered by at least one active rule ---- */
			$targeted_types     = [];
			$has_all_rule       = false;
			$has_homepage_rule  = false;
			foreach ( $rules as $rule ) {
				if ( empty( $rule['enabled'] ) ) {
					continue;
				}
				switch ( $rule['target_type'] ) {
					case 'all':
						$has_all_rule = true;
						break;
					case 'homepage':
						$has_homepage_rule = true;
						break;
					default:
						$targeted_types[] = $rule['target_type'];
				}
			}
			$targeted_types = array_unique( $targeted_types );

			if ( $has_all_rule ) {
				$pages_with_schema = $total_pages;
			} else {
				$pages_with_schema = 0;
				foreach ( $targeted_types as $ptype ) {
					$c = wp_count_posts( $ptype );
					if ( isset( $c->publish ) ) {
						$pages_with_schema += (int) $c->publish;
					}
				}
				if ( $has_homepage_rule ) {
					// homepage counts as 1 extra if not already included
					$pages_with_schema = min( $pages_with_schema + 1, $total_pages );
				}
			}

			/* ---- 4. Validation results (stored by validation runner) ---- */
			$val_data          = get_option( 'giga_sp_validation_results', [] );
			$validation_passed = isset( $val_data['passed'] ) ? (int) $val_data['passed'] : 0;
			$validation_failed = isset( $val_data['failed'] ) ? (int) $val_data['failed'] : 0;

			/* ---- 5. Recent activity log ---- */
			$log             = get_option( 'giga_sp_activity_log', [] );
			$recent_activity = array_slice( array_reverse( $log ), 0, 5 );

			return [
				'total_pages'       => $total_pages,
				'pages_with_schema' => $pages_with_schema,
				'validation_passed' => $validation_passed,
				'validation_failed' => $validation_failed,
				'rules_active'      => $rules_active,
				'recent_activity'   => $recent_activity,
			];
		}

		/**
		 * Append an entry to the persistent activity log (max 50 entries).
		 *
		 * @since 1.0.0
		 * @param string $title      Human-readable description.
		 * @param string $icon       Dashicons class, e.g. 'dashicons-yes-alt'.
		 * @param string $status     CSS badge class: giga-sp-pass | giga-sp-info | giga-sp-fail.
		 * @param string $status_text Short label shown in the badge.
		 */
		public static function log_activity( $title, $icon = 'dashicons-admin-generic', $status = 'giga-sp-info', $status_text = 'Done' ) {
			$log   = get_option( 'giga_sp_activity_log', [] );
			$log[] = [
				'title'       => $title,
				'icon'        => $icon,
				'status'      => $status,
				'status_text' => $status_text,
				'time'        => current_time( 'timestamp' ),
				'user'        => wp_get_current_user()->display_name,
			];
			// Keep only the most recent 50 entries
			if ( count( $log ) > 50 ) {
				$log = array_slice( $log, -50 );
			}
			update_option( 'giga_sp_activity_log', $log );
		}

		/**
		 * AJAX: save (create or update) a rule.
		 *
		 * @since 1.0.0
		 */
		public function ajax_save_rule() {
			check_ajax_referer( 'giga_sp_admin_nonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( [ 'message' => __( 'Permission denied.', 'giga-schema-pro' ) ] );
			}

			$rule_id     = isset( $_POST['rule_id'] )     ? sanitize_text_field( wp_unslash( $_POST['rule_id'] ) )     : '';
			$schema_type = isset( $_POST['schema_type'] ) ? sanitize_text_field( wp_unslash( $_POST['schema_type'] ) ) : '';
			$target_type = isset( $_POST['target_type'] ) ? sanitize_text_field( wp_unslash( $_POST['target_type'] ) ) : 'post';
			$priority    = isset( $_POST['priority'] )    ? (int) $_POST['priority']    : 10;
			$enabled     = isset( $_POST['enabled'] )     ? (bool) $_POST['enabled']     : true;
			$conditions  = isset( $_POST['conditions'] )  ? (array) $_POST['conditions'] : [];

			if ( empty( $schema_type ) ) {
				wp_send_json_error( [ 'message' => __( 'Schema type is required.', 'giga-schema-pro' ) ] );
			}

			$rule_data = [
				'id'          => $rule_id,
				'schema_type' => $schema_type,
				'target_type' => $target_type,
				'priority'    => $priority,
				'enabled'     => $enabled,
				'conditions'  => $conditions,
			];

			$saved_id   = Giga_SP_Rules::update_rule( $rule_data );
			$action_txt = $rule_id ? __( 'Rule updated', 'giga-schema-pro' ) : __( 'Rule created', 'giga-schema-pro' );

			self::log_activity(
				/* translators: %s: schema type name */
				sprintf( $action_txt . ': %s → %s', $schema_type, $target_type ),
				'dashicons-editor-ul',
				'giga-sp-pass',
				$rule_id ? __( 'Updated', 'giga-schema-pro' ) : __( 'Created', 'giga-schema-pro' )
			);

			wp_send_json_success( [ 'rule_id' => $saved_id, 'message' => $action_txt . '.' ] );
		}

		/**
		 * AJAX: delete a rule by ID.
		 *
		 * @since 1.0.0
		 */
		public function ajax_delete_rule() {
			check_ajax_referer( 'giga_sp_admin_nonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( [ 'message' => __( 'Permission denied.', 'giga-schema-pro' ) ] );
			}

			$rule_id = isset( $_POST['rule_id'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_id'] ) ) : '';
			if ( empty( $rule_id ) ) {
				wp_send_json_error( [ 'message' => __( 'Rule ID is required.', 'giga-schema-pro' ) ] );
			}

			Giga_SP_Rules::delete_rule( $rule_id );
			self::log_activity(
				/* translators: %s: rule ID */
				sprintf( __( 'Rule deleted: %s', 'giga-schema-pro' ), $rule_id ),
				'dashicons-trash',
				'giga-sp-fail',
				__( 'Deleted', 'giga-schema-pro' )
			);

			wp_send_json_success( [ 'message' => __( 'Rule deleted.', 'giga-schema-pro' ) ] );
		}

		/**
		 * AJAX: toggle a rule's enabled/disabled state.
		 *
		 * @since 1.0.0
		 */
		public function ajax_toggle_rule() {
			check_ajax_referer( 'giga_sp_admin_nonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( [ 'message' => __( 'Permission denied.', 'giga-schema-pro' ) ] );
			}

			$rule_id = isset( $_POST['rule_id'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_id'] ) ) : '';
			if ( empty( $rule_id ) ) {
				wp_send_json_error( [ 'message' => __( 'Rule ID is required.', 'giga-schema-pro' ) ] );
			}

			$rules = Giga_SP_Rules::get_rules();
			$new_state = false;
			foreach ( $rules as &$rule ) {
				if ( $rule['id'] === $rule_id ) {
					$rule['enabled'] = ! $rule['enabled'];
					$new_state = $rule['enabled'];
					Giga_SP_Rules::update_rule( $rule );
					break;
				}
			}

			self::log_activity(
				sprintf(
					/* translators: %1$s: rule ID, %2$s: state */
					__( 'Rule %1$s %2$s', 'giga-schema-pro' ),
					$rule_id,
					$new_state ? __( 'enabled', 'giga-schema-pro' ) : __( 'disabled', 'giga-schema-pro' )
				),
				'dashicons-controls-play',
				$new_state ? 'giga-sp-pass' : 'giga-sp-fail',
				$new_state ? __( 'Enabled', 'giga-schema-pro' ) : __( 'Disabled', 'giga-schema-pro' )
			);

			wp_send_json_success( [ 'enabled' => $new_state ] );
		}
		
		/**
		 * Get schema type description
		 *
		 * @since 1.0.0
		 * @param string $type Schema type name
		 * @return string Description
		 */
		private function get_schema_type_description( $type ) {
			$descriptions = [
				'Article' => 'Blog posts, news articles, and written content',
				'WebPage' => 'Standard pages and web documents',
				'Product' => 'E-commerce products with pricing and availability',
				'BreadcrumbList' => 'Navigation breadcrumbs for better site structure',
				'Organization' => 'Company and business information',
				'Person' => 'Author profiles and personal information',
				'WebSite' => 'Site-level schema with search functionality',
				'FAQ' => 'Frequently asked questions with dropdown answers',
				'HowTo' => 'Step-by-step instructions and tutorials',
				'LocalBusiness' => 'Local business with address, hours, and contact',
				'Review' => 'Individual product or service reviews',
				'AggregateRating' => 'Star ratings and overall scoring',
				'Offer' => 'Product offers with pricing and conditions',
				'Event' => 'Events with dates, locations, and ticket info',
				'Course' => 'Online courses with lessons and materials',
				'Recipe' => 'Recipes with ingredients, cook time, and nutrition',
				'VideoObject' => 'Video content with duration, thumbnails, and publisher',
				'SoftwareApplication' => 'Applications and software programs',
				'Book' => 'Books with authors, publishers, and reviews',
				'JobPosting' => 'Job listings with requirements and details',
				'Service' => 'Service offerings with descriptions and pricing',
				'MedicalCondition' => 'Health conditions with symptoms and treatments',
				'RealEstateListing' => 'Property listings with features and pricing',
				'CollectionPage' => 'Category and collection pages',
				'ItemList' => 'Carousel-eligible lists of items',
				'SpeakableSpecification' => 'Voice search optimization',
				'Custom' => 'Manual JSON-LD for any schema type'
			];
			
			return isset( $descriptions[ $type ] ) ? $descriptions[ $type ] : 'Schema markup for this content type';
		}

		public function render_rules() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
	
			$rules = Giga_SP_Rules::get_rules();
			$all_types = Giga_SP_Types::get_all_types();
			$is_pro = class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro();
			?>
			<div class="giga-sp-admin">
				<div class="giga-sp-container">
					
					<!-- Modern Header -->
					<div class="giga-sp-dashboard-header">
						<div class="giga-sp-dashboard-content">
							<div class="giga-sp-header-main">
								<div class="giga-sp-header-left">
									<div class="giga-sp-header-icon">
										<span class="dashicons dashicons-editor-ul"></span>
									</div>
									<div class="giga-sp-header-title">
										<h1><?php esc_html_e( 'Auto-Generation Rules', 'giga-schema-pro' ); ?></h1>
										<p class="giga-sp-header-subtitle"><?php esc_html_e( 'Configure automatic schema generation rules. Rules are applied in order of priority.', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
								<div class="giga-sp-header-actions">
									<?php if ( ! $is_pro ) : ?>
										<a href="#" class="giga-btn giga-btn-primary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Upgrade to Pro', 'giga-schema-pro' ); ?>
										</a>
									<?php endif; ?>
									<a href="#" class="giga-btn giga-btn-secondary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
										<span class="dashicons dashicons-cog"></span>
										<?php esc_html_e( 'Settings', 'giga-schema-pro' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
	
					<!-- Rules Management Section -->
					<div class="giga-card">
						<div class="giga-card-header">
							<h3 class="giga-card-title">
								<span class="dashicons dashicons-list-view"></span>
								<?php esc_html_e( 'Active Rules', 'giga-schema-pro' ); ?>
							</h3>
						</div>
						<div class="giga-card-body">
							<?php if ( empty( $rules ) ) : ?>
								<div class="giga-empty-state">
									<div class="giga-empty-icon">📋</div>
									<h4><?php esc_html_e( 'No rules configured yet', 'giga-schema-pro' ); ?></h4>
									<p><?php esc_html_e( 'Create rules to automatically apply schema markup to your content based on conditions.', 'giga-schema-pro' ); ?></p>
									<?php if ( $is_pro ) : ?>
										<button type="button" class="giga-btn giga-btn-primary" id="giga-sp-add-rule">
											<span class="dashicons dashicons-plus"></span>
											<?php esc_html_e( 'Create First Rule', 'giga-schema-pro' ); ?>
										</button>
									<?php else : ?>
										<a href="#" class="giga-btn giga-btn-primary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Upgrade to Pro to create rules', 'giga-schema-pro' ); ?>
										</a>
									<?php endif; ?>
								</div>
							<?php else : ?>
								<div class="giga-rules-container">
									<div class="giga-rules-list">
										<?php foreach ( $rules as $rule ) : ?>
											<div class="giga-rule-card <?php echo $rule['enabled'] ? 'active' : ''; ?>">
												<div class="giga-rule-header">
													<div class="giga-rule-info">
														<h4 class="giga-rule-title"><?php echo esc_html( $rule['schema_type'] ); ?></h4>
														<div class="giga-rule-meta">
															<span class="giga-rule-target"><?php echo esc_html( $rule['target_type'] ); ?></span>
															<span class="giga-rule-priority">Priority: <?php echo esc_html( $rule['priority'] ); ?></span>
														</div>
													</div>
													<div class="giga-rule-status">
														<?php if ( $rule['enabled'] ) : ?>
															<span class="giga-sp-badge giga-sp-pass">✓ Enabled</span>
														<?php else : ?>
															<span class="giga-sp-badge giga-sp-fail">✗ Disabled</span>
														<?php endif; ?>
													</div>
												</div>
												<div class="giga-rule-conditions">
													<div class="giga-rule-conditions-title">
														<span class="dashicons dashicons-filter"></span>
														<?php esc_html_e( 'Conditions', 'giga-schema-pro' ); ?>
													</div>
													<div class="giga-rule-conditions-content">
														<?php if ( empty( $rule['conditions'] ) ) : ?>
															<span class="giga-rule-no-conditions"><?php esc_html_e( 'No conditions - applies to all content of this type', 'giga-schema-pro' ); ?></span>
														<?php else : ?>
															<div class="giga-rule-conditions-list">
																<?php
																foreach ( $rule['conditions'] as $condition ) :
																	$condition_text = isset( $condition['taxonomy'] ) ?
																		sprintf( '%s: %s', $condition['taxonomy'], implode(', ', $condition['terms'] ) ) :
																		sprintf( '%s: %s', $condition['field'], $condition['value'] );
																?>
																	<div class="giga-rule-condition">
																		<span class="dashicons dashicons-check"></span>
																		<?php echo esc_html( $condition_text ); ?>
																	</div>
																<?php endforeach; ?>
															</div>
														<?php endif; ?>
													</div>
												</div>
												<div class="giga-rule-actions">
													<?php if ( $is_pro ) : ?>
														<button type="button" class="giga-btn giga-btn-secondary" data-action="edit" data-rule-id="<?php echo esc_attr( $rule['id'] ); ?>">
															<span class="dashicons dashicons-edit"></span>
															<?php esc_html_e( 'Edit', 'giga-schema-pro' ); ?>
														</button>
														<button type="button" class="giga-btn giga-btn-secondary" data-action="delete" data-rule-id="<?php echo esc_attr( $rule['id'] ); ?>">
															<span class="dashicons dashicons-trash"></span>
															<?php esc_html_e( 'Delete', 'giga-schema-pro' ); ?>
														</button>
													<?php else : ?>
														<span class="giga-rule-pro-notice"><?php esc_html_e( 'Pro feature', 'giga-schema-pro' ); ?></span>
													<?php endif; ?>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
	
					<?php if ( $is_pro ) : ?>
						<div class="giga-card">
							<div class="giga-card-body">
								<div style="text-align: center;">
									<button type="button" class="giga-btn giga-btn-primary" id="giga-sp-add-rule">
										<span class="dashicons dashicons-plus"></span>
										<?php esc_html_e( 'Add New Rule', 'giga-schema-pro' ); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Render schema types page with modern UI
		 *
		 * @since 1.0.0
		 */
		public function render_schema_types() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$all_types = Giga_SP_Types::get_all_types();
			$is_pro = class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro();
			?>
			<div class="giga-sp-admin">
				<div class="giga-sp-container">
					
					<!-- Modern Header -->
					<div class="giga-sp-dashboard-header">
						<div class="giga-sp-dashboard-content">
							<div class="giga-sp-header-main">
								<div class="giga-sp-header-left">
									<div class="giga-sp-header-icon">
										<span class="dashicons dashicons-category"></span>
									</div>
									<div class="giga-sp-header-title">
										<h1><?php esc_html_e( 'Schema Types', 'giga-schema-pro' ); ?></h1>
										<p class="giga-sp-header-subtitle"><?php esc_html_e( 'Browse and manage all supported schema types', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
								<div class="giga-sp-header-actions">
									<?php if ( ! $is_pro ) : ?>
										<a href="#" class="giga-btn giga-btn-primary" onclick="window.location.href='?page=giga-schema-pro-settings'; return false;">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Upgrade to Pro', 'giga-schema-pro' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<!-- Schema Type Stats -->
					<div class="giga-stats-grid">
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon">📊</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( count( $all_types['free'] ) ); ?></h3>
									<p><?php esc_html_e( 'Free Types', 'giga-schema-pro' ); ?></p>
								</div>
								<div class="giga-stat-trend">Active</div>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #8b5cf6; color: white;">⭐</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( count( $all_types['pro'] ) ); ?></h3>
									<p><?php esc_html_e( 'Pro Types', 'giga-schema-pro' ); ?></p>
								</div>
								<?php if ( ! $is_pro ) : ?>
									<div class="giga-stat-trend">Upgrade</div>
								<?php endif; ?>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #10b981; color: white;">✓</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( count( $all_types['free'] ) + count( $all_types['pro'] ) ); ?></h3>
									<p><?php esc_html_e( 'Total Types', 'giga-schema-pro' ); ?></p>
								</div>
								<div class="giga-stat-trend">Complete</div>
							</div>
						</div>
					</div>

					<!-- Schema Type Filter Tabs -->
					<div class="giga-tabs">
						<a href="#all-types" class="giga-sp-tab active" onclick="return false;">
							<span class="dashicons dashicons-grid-view"></span>
							<?php esc_html_e( 'All Types', 'giga-schema-pro' ); ?>
						</a>
						<a href="#free-types" class="giga-sp-tab" onclick="return false;">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Free Types', 'giga-schema-pro' ); ?>
						</a>
						<?php if ( $is_pro ) : ?>
							<a href="#pro-types" class="giga-sp-tab" onclick="return false;">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Pro Types', 'giga-schema-pro' ); ?>
							</a>
						<?php endif; ?>
						<a href="#popular-types" class="giga-sp-tab" onclick="return false;">
							<span class="dashicons dashicons-heart"></span>
							<?php esc_html_e( 'Popular', 'giga-schema-pro' ); ?>
						</a>
					</div>

					<!-- All Types Grid -->
					<div id="all-types" class="giga-panel">
						<div class="giga-schema-grid">
							<?php 
							// Show all types with free/pro distinction
							$all_schema_types = array_merge(
								array_map(function($type) { 
									return ['name' => $type, 'type' => 'free']; 
								}, $all_types['free']),
								array_map(function($type) { 
									return ['name' => $type, 'type' => 'pro']; 
								}, $all_types['pro'])
							);
							
							foreach ($all_schema_types as $schema_type) : 
								$is_free = $schema_type['type'] === 'free';
								$icon = $this->get_schema_type_icon($schema_type['name']);
								$badge_class = $is_free ? 'free' : 'pro';
								$badge_text = $is_free ? 'Free' : 'Pro';
							?>
								<div class="giga-schema-card" data-schema-type="<?php echo esc_attr( $schema_type['name'] ); ?>" data-schema-category="<?php echo esc_attr( $this->get_schema_category($schema_type['name']) ); ?>">
									<div class="giga-schema-icon">
										<?php echo esc_html( $icon ); ?>
									</div>
									<h3 class="giga-schema-title"><?php echo esc_html( $schema_type['name'] ); ?></h3>
									<p class="giga-schema-description"><?php echo esc_html( $this->get_schema_type_description( $schema_type['name'] ) ); ?></p>
									<div class="giga-schema-footer">
										<span class="giga-schema-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
										<?php if ( $is_free || $is_pro ) : ?>
											<button class="giga-btn-icon giga-schema-toggle">
												<span class="dashicons dashicons-plus"></span>
											</button>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Free Types Panel -->
					<div id="free-types" class="giga-panel" style="display: none;">
						<div class="giga-schema-grid">
							<?php foreach ( $all_types['free'] as $type ) : 
								$icon = $this->get_schema_type_icon($type);
							?>
								<div class="giga-schema-card" data-schema-type="<?php echo esc_attr( $type ); ?>">
									<div class="giga-schema-icon">
										<?php echo esc_html( $icon ); ?>
									</div>
									<h3 class="giga-schema-title"><?php echo esc_html( $type ); ?></h3>
									<p class="giga-schema-description"><?php echo esc_html( $this->get_schema_type_description( $type ) ); ?></p>
									<div class="giga-schema-footer">
										<span class="giga-schema-badge free">Free</span>
										<button class="giga-btn-icon giga-schema-toggle">
											<span class="dashicons dashicons-plus"></span>
										</button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Pro Types Panel -->
					<?php if ( $is_pro ) : ?>
						<div id="pro-types" class="giga-panel" style="display: none;">
							<div class="giga-schema-grid">
								<?php foreach ( $all_types['pro'] as $type ) : 
									$icon = $this->get_schema_type_icon($type);
								?>
									<div class="giga-schema-card" data-schema-type="<?php echo esc_attr( $type ); ?>">
										<div class="giga-schema-icon">
											<?php echo esc_html( $icon ); ?>
										</div>
										<h3 class="giga-schema-title"><?php echo esc_html( $type ); ?></h3>
										<p class="giga-schema-description"><?php echo esc_html( $this->get_schema_type_description( $type ) ); ?></p>
										<div class="giga-schema-footer">
											<span class="giga-schema-badge pro">Pro</span>
											<button class="giga-btn-icon giga-schema-toggle">
												<span class="dashicons dashicons-plus"></span>
											</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php else : ?>
						<div id="pro-types" class="giga-panel" style="display: none;">
							<div class="giga-card">
								<div class="giga-card-header">
									<h3 class="giga-card-title">
										<span class="dashicons dashicons-lock"></span>
										<?php esc_html_e( 'Pro Schema Types', 'giga-schema-pro' ); ?>
									</h3>
								</div>
								<div class="giga-card-body">
									<p><?php esc_html_e( 'Unlock 15+ additional schema types with the Pro version:', 'giga-schema-pro' ); ?></p>
									<ul class="giga-pro-features">
										<li>Product (deep integration with WooCommerce)</li>
										<li>Review and AggregateRating schema</li>
										<li>Event, Course, Recipe schema</li>
										<li>VideoObject and SoftwareApplication</li>
										<li>Custom JSON-LD editor</li>
									</ul>
									<a href="#" class="giga-btn giga-btn-primary">
										<span class="dashicons dashicons-star-filled"></span>
										<?php esc_html_e( 'Upgrade to Pro', 'giga-schema-pro' ); ?>
									</a>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- Popular Types Panel -->
					<div id="popular-types" class="giga-panel" style="display: none;">
						<div class="giga-schema-grid">
							<?php 
							$popular_types = ['Article', 'Product', 'FAQ', 'HowTo', 'LocalBusiness', 'Organization'];
							foreach ($popular_types as $type) :
								$type_exists = in_array($type, $all_types['free']) || in_array($type, $all_types['pro']);
								if ($type_exists) :
									$icon = $this->get_schema_type_icon($type);
									$is_free = in_array($type, $all_types['free']);
									$badge_class = $is_free ? 'free' : 'pro';
									$badge_text = $is_free ? 'Free' : 'Pro';
							?>
								<div class="giga-schema-card" data-schema-type="<?php echo esc_attr( $type ); ?>">
									<div class="giga-schema-icon">
										<?php echo esc_html( $icon ); ?>
									</div>
									<h3 class="giga-schema-title"><?php echo esc_html( $type ); ?></h3>
									<p class="giga-schema-description"><?php echo esc_html( $this->get_schema_type_description( $type ) ); ?></p>
									<div class="giga-schema-footer">
										<span class="giga-schema-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
										<button class="giga-btn-icon giga-schema-toggle">
											<span class="dashicons dashicons-plus"></span>
										</button>
									</div>
								</div>
							<?php 
								endif;
							endforeach; 
							?>
						</div>
					</div>

				</div>
			</div>
			<?php
		}
		
		/**
		 * Get schema type icon
		 *
		 * @since 1.0.0
		 * @param string $type Schema type name
		 * @return string Icon
		 */
		private function get_schema_type_icon( $type ) {
			$icons = [
				'Article' => '📝',
				'WebPage' => '🌐',
				'Product' => '🛒',
				'BreadcrumbList' => '📍',
				'Organization' => '🏢',
				'Person' => '👤',
				'WebSite' => '🌐',
				'FAQ' => '❓',
				'HowTo' => '📋',
				'LocalBusiness' => '🏪',
				'Review' => '⭐',
				'AggregateRating' => '📊',
				'Offer' => '💰',
				'Event' => '📅',
				'Course' => '📚',
				'Recipe' => '🍳',
				'VideoObject' => '🎥',
				'SoftwareApplication' => '💻',
				'Book' => '📖',
				'JobPosting' => '💼',
				'Service' => '🔧',
				'MedicalCondition' => '🏥',
				'RealEstateListing' => '🏠',
				'CollectionPage' => '📂',
				'ItemList' => '📋',
				'SpeakableSpecification' => '🗣️',
				'Custom' => '⚙️'
			];
			
			return isset( $icons[ $type ] ) ? $icons[ $type ] : '📄';
		}
		
		/**
		 * Get schema category
		 *
		 * @since 1.0.0
		 * @param string $type Schema type name
		 * @return string Category
		 */
		private function get_schema_category( $type ) {
			$categories = [
				'Article' => 'content',
				'WebPage' => 'content',
				'Product' => 'ecommerce',
				'BreadcrumbList' => 'navigation',
				'Organization' => 'business',
				'Person' => 'business',
				'WebSite' => 'technical',
				'FAQ' => 'content',
				'HowTo' => 'content',
				'LocalBusiness' => 'business',
				'Review' => 'content',
				'AggregateRating' => 'content',
				'Offer' => 'ecommerce',
				'Event' => 'content',
				'Course' => 'content',
				'Recipe' => 'content',
				'VideoObject' => 'media',
				'SoftwareApplication' => 'technical',
				'Book' => 'content',
				'JobPosting' => 'business',
				'Service' => 'business',
				'MedicalCondition' => 'health',
				'RealEstateListing' => 'business',
				'CollectionPage' => 'navigation',
				'ItemList' => 'navigation',
				'SpeakableSpecification' => 'technical',
				'Custom' => 'technical'
			];
			
			return isset( $categories[ $type ] ) ? $categories[ $type ] : 'general';
		}

		/**
		 * Render validation page
		 *
		 * @since 1.0.0
		 */
		public function render_validation() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
	
			$is_pro = class_exists( 'Giga_SP_License' ) && Giga_SP_License::is_pro();
			$woo_active = class_exists( 'WooCommerce' );
			
			// Get validation results if available
			$validation_results = get_option( 'giga_sp_validation_results', [] );
			$has_results = !empty($validation_results) && isset($validation_results['timestamp']);
			
			// Get schema statistics
			$stats = $this->get_validation_stats();
			?>
			<div class="giga-sp-admin">
				<div class="giga-sp-container">
					
					<!-- Modern Header -->
					<div class="giga-sp-dashboard-header">
						<div class="giga-sp-dashboard-content">
							<div class="giga-sp-header-main">
								<div class="giga-sp-header-left">
									<div class="giga-sp-header-icon">
										<span class="dashicons dashicons-search"></span>
									</div>
									<div class="giga-sp-header-title">
										<h1><?php esc_html_e( 'Schema Validation', 'giga-schema-pro' ); ?></h1>
										<p class="giga-sp-header-subtitle"><?php esc_html_e( 'Test your schema markup against Google Rich Results requirements', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
								<div class="giga-sp-header-actions">
									<a href="#" class="giga-btn giga-btn-secondary" onclick="window.location.href='?page=giga-schema-pro'; return false;">
										<span class="dashicons dashicons-arrow-left"></span>
										<?php esc_html_e( 'Back to Dashboard', 'giga-schema-pro' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
	
					<!-- Validation Stats Overview -->
					<div class="giga-stats-grid">
						<?php if ($woo_active) : ?>
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #10b981; color: white;">🛒</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $stats['products'] ); ?></h3>
									<p><?php esc_html_e( 'Products', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
						<?php endif; ?>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #3b82f6; color: white;">✓</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $stats['validated'] ); ?></h3>
									<p><?php esc_html_e( 'Validated', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #f59e0b; color: white;">⚠</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $stats['warnings'] ); ?></h3>
									<p><?php esc_html_e( 'Warnings', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
						
						<div class="giga-stat-card">
							<div class="giga-stat-header">
								<div class="giga-stat-icon" style="background: #ef4444; color: white;">✕</div>
								<div class="giga-stat-content">
									<h3><?php echo esc_html( $stats['errors'] ); ?></h3>
									<p><?php esc_html_e( 'Errors', 'giga-schema-pro' ); ?></p>
								</div>
							</div>
						</div>
					</div>
	
					<?php if ( $is_pro ) : ?>
						
						<!-- Quick Validation Actions -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-rocket"></span>
									<?php esc_html_e( 'Quick Validation Actions', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-validation-actions">
									<?php if ($woo_active) : ?>
									<button type="button" class="giga-btn giga-btn-primary" id="giga-validate-woo-products">
										<span class="dashicons dashicons-cart"></span>
										<?php esc_html_e( 'Validate All Products', 'giga-schema-pro' ); ?>
									</button>
									<?php endif; ?>
									
									<button type="button" class="giga-btn giga-btn-primary" id="giga-validate-current-page">
										<span class="dashicons dashicons-screen"></span>
										<?php esc_html_e( 'Validate Current Page', 'giga-schema-pro' ); ?>
									</button>
									
									<button type="button" class="giga-btn giga-btn-secondary" id="giga-validate-selected">
										<span class="dashicons dashicons-list-check"></span>
										<?php esc_html_e( 'Validate Selected', 'giga-schema-pro' ); ?>
									</button>
								</div>
							</div>
						</div>
	
						<!-- Validation Filters -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-filter"></span>
									<?php esc_html_e( 'Validation Filters', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-filters-grid">
									<div class="giga-filter-group">
										<label><?php esc_html_e( 'Content Type', 'giga-schema-pro' ); ?></label>
										<select id="validation-content-type" class="giga-form-select">
											<option value="all"><?php esc_html_e( 'All Content', 'giga-schema-pro' ); ?></option>
											<option value="post"><?php esc_html_e( 'Posts', 'giga-schema-pro' ); ?></option>
											<option value="page"><?php esc_html_e( 'Pages', 'giga-schema-pro' ); ?></option>
											<?php if ($woo_active) : ?>
												<option value="product"><?php esc_html_e( 'Products', 'giga-schema-pro' ); ?></option>
											<?php endif; ?>
										</select>
									</div>
									
									<div class="giga-filter-group">
										<label><?php esc_html_e( 'Status', 'giga-schema-pro' ); ?></label>
										<select id="validation-status" class="giga-form-select">
											<option value="all"><?php esc_html_e( 'All Status', 'giga-schema-pro' ); ?></option>
											<option value="passed"><?php esc_html_e( 'Passed', 'giga-schema-pro' ); ?></option>
											<option value="warning"><?php esc_html_e( 'Warnings', 'giga-schema-pro' ); ?></option>
											<option value="error"><?php esc_html_e( 'Errors', 'giga-schema-pro' ); ?></option>
										</select>
									</div>
									
									<div class="giga-filter-group">
										<label><?php esc_html_e( 'Date Range', 'giga-schema-pro' ); ?></label>
										<select id="validation-date-range" class="giga-form-select">
											<option value="all"><?php esc_html_e( 'All Time', 'giga-schema-pro' ); ?></option>
											<option value="today"><?php esc_html_e( 'Today', 'giga-schema-pro' ); ?></option>
											<option value="week"><?php esc_html_e( 'Last 7 Days', 'giga-schema-pro' ); ?></option>
											<option value="month"><?php esc_html_e( 'Last 30 Days', 'giga-schema-pro' ); ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
	
						<!-- Bulk Validation -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-tasks"></span>
									<?php esc_html_e( 'Bulk Validation', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-bulk-validation">
									<div class="giga-validation-progress" id="giga-validation-progress" style="display: none;">
										<div class="giga-progress-bar">
											<div class="giga-progress-fill" style="width: 0%"></div>
											<div class="giga-progress-text">0%</div>
										</div>
										<div class="giga-validation-stats">
											<span id="giga-validation-current">0</span> / <span id="giga-validation-total">0</span> items
										</div>
									</div>
									
									<div class="giga-validation-options">
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" id="validate-schema-types" checked>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Validate Schema Types', 'giga-schema-pro' ); ?></span>
											</label>
										</div>
										
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" id="validate-structure" checked>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Validate Structure', 'giga-schema-pro' ); ?></span>
											</label>
										</div>
										
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" id="validate-rich-results" checked>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Validate for Rich Results', 'giga-schema-pro' ); ?></span>
											</label>
										</div>
									</div>
									
									<div class="giga-form-actions">
										<button type="button" class="giga-btn giga-btn-primary" id="giga-start-bulk-validation">
											<span class="dashicons dashicons-play"></span>
											<?php esc_html_e( 'Start Bulk Validation', 'giga-schema-pro' ); ?>
										</button>
										<button type="button" class="giga-btn giga-btn-secondary" id="giga-export-results">
											<span class="dashicons dashicons-download"></span>
											<?php esc_html_e( 'Export Results', 'giga-schema-pro' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
	
						<!-- Validation Results -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-clipboard-list"></span>
									<?php esc_html_e( 'Validation Results', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div id="giga-sp-validation-results">
									<?php if ($has_results) : ?>
										<?php $this->display_validation_results($validation_results); ?>
									<?php else : ?>
										<div class="giga-empty-state">
											<div class="giga-empty-icon">
												<span class="dashicons dashicons-search"></span>
											</div>
											<h3><?php esc_html_e( 'No validation results yet', 'giga-schema-pro' ); ?></h3>
											<p><?php esc_html_e( 'Run a validation to see detailed results and recommendations for improving your schema markup.', 'giga-schema-pro' ); ?></p>
											<button type="button" class="giga-btn giga-btn-primary" onclick="$('#giga-start-bulk-validation').click();">
												<?php esc_html_e( 'Start Validation', 'giga-schema-pro' ); ?>
											</button>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
	
					<?php else : ?>
						
						<!-- Pro Feature Card -->
						<div class="giga-card">
							<div class="giga-card-header">
								<h3 class="giga-card-title">
									<span class="dashicons dashicons-lock"></span>
									<?php esc_html_e( 'Pro Feature - Advanced Validation', 'giga-schema-pro' ); ?>
								</h3>
							</div>
							<div class="giga-card-body">
								<div class="giga-pro-features">
									<div class="giga-pro-feature-list">
										<div class="giga-pro-feature">
											<span class="dashicons dashicons-check"></span>
											<div>
												<h4><?php esc_html_e( 'Bulk Product Validation', 'giga-schema-pro' ); ?></h4>
												<p><?php esc_html_e( 'Validate all WooCommerce products at once with detailed reporting', 'giga-schema-pro' ); ?></p>
											</div>
										</div>
										
										<div class="giga-pro-feature">
											<span class="dashicons dashicons-check"></span>
											<div>
												<h4><?php esc_html_e( 'Google Rich Results Test', 'giga-schema-pro' ); ?></h4>
												<p><?php esc_html_e( 'Test against Google\'s latest Rich Results requirements', 'giga-schema-pro' ); ?></p>
											</div>
										</div>
										
										<div class="giga-pro-feature">
											<span class="dashicons dashicons-check"></span>
											<div>
												<h4><?php esc_html_e( 'Auto-fix Suggestions', 'giga-schema-pro' ); ?></h4>
												<p><?php esc_html_e( 'Get specific recommendations to fix schema issues', 'giga-schema-pro' ); ?></p>
											</div>
										</div>
										
										<div class="giga-pro-feature">
											<span class="dashicons dashicons-check"></span>
											<div>
												<h4><?php esc_html_e( 'Export Validation Reports', 'giga-schema-pro' ); ?></h4>
												<p><?php esc_html_e( 'Generate and download detailed validation reports', 'giga-schema-pro' ); ?></p>
											</div>
										</div>
									</div>
									
									<div class="giga-upgrade-section">
										<h3><?php esc_html_e( 'Upgrade to Pro', 'giga-schema-pro' ); ?></h3>
										<p><?php esc_html_e( 'Unlock advanced validation features and improve your search visibility', 'giga-schema-pro' ); ?></p>
										<a href="#" class="giga-btn giga-btn-primary">
											<span class="dashicons dashicons-star-filled"></span>
											<?php esc_html_e( 'Upgrade Now', 'giga-schema-pro' ); ?>
										</a>
									</div>
								</div>
							</div>
						</div>
	
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
 
		/**
		 * Render WooCommerce settings page
		 *
		 * @since 1.0.0
		 */
		public function render_woocommerce_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
	
			// Save settings if form submitted
			if ( isset( $_POST['giga_sp_woo_save'] ) && check_admin_referer( 'giga_sp_woo_settings' ) ) {
				$settings = [
					'shippingRate' => isset( $_POST['shipping_rate'] ) ? sanitize_text_field( $_POST['shipping_rate'] ) : '',
					'shippingCurrency' => isset( $_POST['shipping_currency'] ) ? sanitize_text_field( $_POST['shipping_currency'] ) : '',
					'returnDays' => isset( $_POST['return_days'] ) ? intval( $_POST['return_days'] ) : 30,
					'returnPolicyCategory' => isset( $_POST['return_policy_category'] ) ? sanitize_text_field( $_POST['return_policy_category'] ) : 'https://schema.org/MerchantReturnFiniteReturnWindow',
					'defaultBrand' => isset( $_POST['default_brand'] ) ? sanitize_text_field( $_POST['default_brand'] ) : '',
					'gtinField' => isset( $_POST['gtin_field'] ) ? sanitize_text_field( $_POST['gtin_field'] ) : '_gtin',
					'mpnField' => isset( $_POST['mpn_field'] ) ? sanitize_text_field( $_POST['mpn_field'] ) : '_mpn',
					'brandField' => isset( $_POST['brand_field'] ) ? sanitize_text_field( $_POST['brand_field'] ) : '_brand',
				];
				update_option( 'giga_sp_woocommerce_settings', $settings );
				self::log_activity(
					__( 'WooCommerce schema settings updated', 'giga-schema-pro' ),
					'dashicons-cart',
					'giga-sp-pass',
					__( 'Saved', 'giga-schema-pro' )
				);
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully.', 'giga-schema-pro' ) . '</p></div>';
			}
	
			$settings = get_option( 'giga_sp_woocommerce_settings', [] );
			$shipping_rate = isset( $settings['shippingRate'] ) ? $settings['shippingRate'] : '';
			$shipping_currency = isset( $settings['shippingCurrency'] ) ? $settings['shippingCurrency'] : '';
			$return_days = isset( $settings['returnDays'] ) ? $settings['returnDays'] : 30;
			$return_policy_category = isset( $settings['returnPolicyCategory'] ) ? $settings['returnPolicyCategory'] : 'https://schema.org/MerchantReturnFiniteReturnWindow';
			$default_brand = isset( $settings['defaultBrand'] ) ? $settings['defaultBrand'] : '';
			$gtin_field = isset( $settings['gtinField'] ) ? $settings['gtinField'] : '_gtin';
			$mpn_field = isset( $settings['mpnField'] ) ? $settings['mpnField'] : '_mpn';
			$brand_field = isset( $settings['brandField'] ) ? $settings['brandField'] : '_brand';
			?>
			<div class="giga-sp-admin">
				<div class="giga-sp-container">
					
					<!-- Modern Header -->
					<div class="giga-sp-dashboard-header">
						<div class="giga-sp-dashboard-content">
							<div class="giga-sp-header-main">
								<div class="giga-sp-header-left">
									<div class="giga-sp-header-icon">
										<span class="dashicons dashicons-cart"></span>
									</div>
									<div class="giga-sp-header-title">
										<h1><?php esc_html_e( 'WooCommerce Schema Settings', 'giga-schema-pro' ); ?></h1>
										<p class="giga-sp-header-subtitle"><?php esc_html_e( 'Configure schema markup for your WooCommerce products', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
								<div class="giga-sp-header-actions">
									<a href="#" class="giga-btn giga-btn-secondary" onclick="window.location.href='?page=giga-schema-pro'; return false;">
										<span class="dashicons dashicons-arrow-left"></span>
										<?php esc_html_e( 'Back to Dashboard', 'giga-schema-pro' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
	
					<!-- WooCommerce Status Card -->
					<div class="giga-card">
						<div class="giga-card-header">
							<h3 class="giga-card-title">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'WooCommerce Integration Status', 'giga-schema-pro' ); ?>
							</h3>
						</div>
						<div class="giga-card-body">
							<div class="giga-woo-status">
								<div class="giga-status-item">
									<div class="giga-status-icon active">
										<span class="dashicons dashicons-yes"></span>
									</div>
									<div class="giga-status-content">
										<div class="giga-status-title"><?php esc_html_e( 'WooCommerce Detected', 'giga-schema-pro' ); ?></div>
										<div class="giga-status-description"><?php esc_html_e( 'WooCommerce is active and ready for schema integration', 'giga-schema-pro' ); ?></div>
									</div>
								</div>
								<div class="giga-status-item">
									<div class="giga-status-icon <?php echo !empty($default_brand) ? 'active' : 'inactive'; ?>">
										<span class="dashicons <?php echo !empty($default_brand) ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
									</div>
									<div class="giga-status-content">
										<div class="giga-status-title"><?php esc_html_e( 'Brand Configuration', 'giga-schema-pro' ); ?></div>
										<div class="giga-status-description"><?php echo !empty($default_brand) ? esc_html__( 'Default brand is configured', 'giga-schema-pro' ) : esc_html__( 'Set a default brand for products', 'giga-schema-pro' ); ?></div>
									</div>
								</div>
								<div class="giga-status-item">
									<div class="giga-status-icon <?php echo !empty($shipping_rate) ? 'active' : 'inactive'; ?>">
										<span class="dashicons <?php echo !empty($shipping_rate) ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
									</div>
									<div class="giga-status-content">
										<div class="giga-status-title"><?php esc_html_e( 'Shipping Configuration', 'giga-schema-pro' ); ?></div>
										<div class="giga-status-description"><?php echo !empty($shipping_rate) ? esc_html__( 'Shipping rates are configured', 'giga-schema-pro' ) : esc_html__( 'Set default shipping rates', 'giga-schema-pro' ); ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
	
					<!-- Settings Tabs -->
					<div class="giga-tabs">
						<a href="#shipping-settings" class="giga-sp-tab active" onclick="return false;">
							<span class="dashicons dashicons-shipping"></span>
							<?php esc_html_e( 'Shipping & Returns', 'giga-schema-pro' ); ?>
						</a>
						<a href="#product-fields" class="giga-sp-tab" onclick="return false;">
							<span class="dashicons dashicons-edit"></span>
							<?php esc_html_e( 'Product Fields', 'giga-schema-pro' ); ?>
						</a>
						<a href="#advanced-settings" class="giga-sp-tab" onclick="return false;">
							<span class="dashicons dashicons-cog"></span>
							<?php esc_html_e( 'Advanced', 'giga-schema-pro' ); ?>
						</a>
					</div>
	
					<!-- Shipping & Returns Settings -->
					<div id="shipping-settings" class="giga-panel">
						<form method="post" action="" class="giga-settings-form">
							<?php wp_nonce_field( 'giga_sp_woo_settings' ); ?>
							
							<div class="giga-settings-grid">
								<div class="giga-settings-column">
									<div class="giga-form-group">
										<label for="shipping_rate" class="giga-form-label">
											<span class="dashicons dashicons-shipping"></span>
											<?php esc_html_e( 'Default Shipping Rate', 'giga-schema-pro' ); ?>
										</label>
										<input type="number" step="0.01" name="shipping_rate" id="shipping_rate" value="<?php echo esc_attr( $shipping_rate ); ?>" class="giga-form-input" placeholder="0.00">
										<p class="giga-form-description"><?php esc_html_e( 'Default shipping cost for all products. Leave empty to disable shipping details in schema.', 'giga-schema-pro' ); ?></p>
									</div>
	
									<div class="giga-form-group">
										<label for="shipping_currency" class="giga-form-label">
											<span class="dashicons dashicons-money-alt"></span>
											<?php esc_html_e( 'Shipping Currency', 'giga-schema-pro' ); ?>
										</label>
										<input type="text" name="shipping_currency" id="shipping_currency" value="<?php echo esc_attr( $shipping_currency ); ?>" class="giga-form-input" placeholder="USD">
										<p class="giga-form-description"><?php esc_html_e( 'Currency code for shipping (e.g., USD, EUR). Leave empty to use WooCommerce default.', 'giga-schema-pro' ); ?></p>
									</div>
	
									<div class="giga-form-group">
										<label for="return_days" class="giga-form-label">
											<span class="dashicons dashicons-undo"></span>
											<?php esc_html_e( 'Return Policy Days', 'giga-schema-pro' ); ?>
										</label>
										<input type="number" name="return_days" id="return_days" value="<?php echo esc_attr( $return_days ); ?>" class="giga-form-input" min="0">
										<p class="giga-form-description"><?php esc_html_e( 'Number of days customers can return products.', 'giga-schema-pro' ); ?></p>
									</div>
	
									<div class="giga-form-group">
										<label for="return_policy_category" class="giga-form-label">
											<span class="dashicons dashicons-list-alt"></span>
											<?php esc_html_e( 'Return Policy Category', 'giga-schema-pro' ); ?>
										</label>
										<select name="return_policy_category" id="return_policy_category" class="giga-form-select">
											<option value="https://schema.org/MerchantReturnFiniteReturnWindow" <?php selected( $return_policy_category, 'https://schema.org/MerchantReturnFiniteReturnWindow' ); ?>><?php esc_html_e( 'Finite Return Window', 'giga-schema-pro' ); ?></option>
											<option value="https://schema.org/MerchantReturnUnlimitedWindow" <?php selected( $return_policy_category, 'https://schema.org/MerchantReturnUnlimitedWindow' ); ?>><?php esc_html_e( 'Unlimited Return Window', 'giga-schema-pro' ); ?></option>
											<option value="https://schema.org/MerchantReturnNotPermitted" <?php selected( $return_policy_category, 'https://schema.org/MerchantReturnNotPermitted' ); ?>><?php esc_html_e( 'Returns Not Permitted', 'giga-schema-pro' ); ?></option>
										</select>
										<p class="giga-form-description"><?php esc_html_e( 'Choose the appropriate return policy category for your business.', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
	
								<div class="giga-settings-column">
									<div class="giga-info-card">
										<div class="giga-info-header">
											<span class="dashicons dashicons-info"></span>
											<h4><?php esc_html_e( 'Schema Benefits', 'giga-schema-pro' ); ?></h4>
										</div>
										<div class="giga-info-content">
											<ul class="giga-info-list">
												<li><?php esc_html_e( 'Improved product visibility in search results', 'giga-schema-pro' ); ?></li>
												<li><?php esc_html_e( 'Rich snippets in Google search', 'giga-schema-pro' ); ?></li>
												<li><?php esc_html_e( 'Better shopping experience for customers', 'giga-schema-pro' ); ?></li>
												<li><?php esc_html_e( 'Enhanced local SEO for physical stores', 'giga-schema-pro' ); ?></li>
											</ul>
										</div>
									</div>
	
									<div class="giga-preview-card">
										<div class="giga-preview-header">
											<span class="dashicons dashicons-eye"></span>
											<h4><?php esc_html_e( 'Schema Preview', 'giga-schema-pro' ); ?></h4>
										</div>
										<div class="giga-preview-content">
											<div class="giga-schema-preview">
												<pre><code>{
		 "@type": "Offer",
		 "priceCurrency": "<?php echo esc_attr( $shipping_currency ?: 'USD' ); ?>",
		 "price": "<?php echo esc_attr( $shipping_rate ?: '0.00' ); ?>",
		 "shippingDetails": {
		   "@type": "OfferShippingDetails",
		   "shippingRate": {
		     "@type": "MonetaryAmount",
		     "value": "<?php echo esc_attr( $shipping_rate ?: '0.00' ); ?>",
		     "currency": "<?php echo esc_attr( $shipping_currency ?: 'USD' ); ?>"
		   }
		 },
		 "hasMerchantReturnPolicy": {
		   "@type": "MerchantReturnPolicy",
		   "merchantReturnDays": <?php echo esc_attr( $return_days ); ?>,
		   "returnPolicyCategory": "<?php echo esc_attr( $return_policy_category ); ?>"
		 }
	}</code></pre>
											</div>
										</div>
									</div>
								</div>
							</div>
	
							<div class="giga-form-actions">
								<?php submit_button( __( 'Save Settings', 'giga-schema-pro' ), 'primary', 'giga_sp_woo_save' ); ?>
								
							</div>
						</form>
					</div>
	
					<!-- Product Fields Settings -->
					<div id="product-fields" class="giga-panel" style="display: none;">
						<form method="post" action="" class="giga-settings-form">
							<?php wp_nonce_field( 'giga_sp_woo_settings' ); ?>
							
							<div class="giga-settings-grid">
								<div class="giga-settings-column">
									<div class="giga-form-group">
										<label for="default_brand" class="giga-form-label">
											<span class="dashicons dashicons-tag"></span>
											<?php esc_html_e( 'Default Brand Name', 'giga-schema-pro' ); ?>
										</label>
										<input type="text" name="default_brand" id="default_brand" value="<?php echo esc_attr( $default_brand ); ?>" class="giga-form-input" placeholder="Your Brand Name">
										<p class="giga-form-description"><?php esc_html_e( 'Default brand name for products. Will be used if no product-specific brand is set.', 'giga-schema-pro' ); ?></p>
									</div>
	
									<div class="giga-form-group">
										<label for="gtin_field" class="giga-form-label">
											<span class="dashicons dashicons-barcode"></span>
											<?php esc_html_e( 'GTIN Custom Field', 'giga-schema-pro' ); ?>
										</label>
										<input type="text" name="gtin_field" id="gtin_field" value="<?php echo esc_attr( $gtin_field ); ?>" class="giga-form-input" placeholder="_gtin">
										<p class="giga-form-description"><?php esc_html_e( 'Custom field name for GTIN (Global Trade Item Number). Default: _gtin', 'giga-schema-pro' ); ?></p>
									</div>
	
									<div class="giga-form-group">
										<label for="mpn_field" class="giga-form-label">
											<span class="dashicons dashicons-id"></span>
											<?php esc_html_e( 'MPN Custom Field', 'giga-schema-pro' ); ?>
										</label>
										<input type="text" name="mpn_field" id="mpn_field" value="<?php echo esc_attr( $mpn_field ); ?>" class="giga-form-input" placeholder="_mpn">
										<p class="giga-form-description"><?php esc_html_e( 'Custom field name for MPN (Manufacturer Part Number). Default: _mpn', 'giga-schema-pro' ); ?></p>
									</div>
	
									<div class="giga-form-group">
										<label for="brand_field" class="giga-form-label">
											<span class="dashicons dashicons-store"></span>
											<?php esc_html_e( 'Brand Custom Field', 'giga-schema-pro' ); ?>
										</label>
										<input type="text" name="brand_field" id="brand_field" value="<?php echo esc_attr( $brand_field ); ?>" class="giga-form-input" placeholder="_brand">
										<p class="giga-form-description"><?php esc_html_e( 'Custom field name for Brand. Default: _brand', 'giga-schema-pro' ); ?></p>
									</div>
								</div>
	
								<div class="giga-settings-column">
									<div class="giga-field-guide">
										<div class="giga-guide-header">
											<span class="dashicons dashicons-book"></span>
											<h4><?php esc_html_e( 'Field Configuration Guide', 'giga-schema-pro' ); ?></h4>
										</div>
										<div class="giga-guide-content">
											<div class="giga-guide-section">
												<h5><?php esc_html_e( 'Product Custom Fields', 'giga-schema-pro' ); ?></h5>
												<p><?php esc_html_e( 'Configure these custom fields to enhance your product schema with additional identifiers:', 'giga-schema-pro' ); ?></p>
												<ul class="giga-guide-list">
													<li>
														<strong><?php esc_html_e( 'GTIN:', 'giga-schema-pro' ); ?></strong>
														<?php esc_html_e( 'Global Trade Item Number for product identification', 'giga-schema-pro' ); ?>
													</li>
													<li>
														<strong><?php esc_html_e( 'MPN:', 'giga-schema-pro' ); ?></strong>
														<?php esc_html_e( 'Manufacturer Part Number for specific product models', 'giga-schema-pro' ); ?>
													</li>
													<li>
														<strong><?php esc_html_e( 'Brand:', 'giga-schema-pro' ); ?></strong>
														<?php esc_html_e( 'Product brand name for better categorization', 'giga-schema-pro' ); ?>
													</li>
												</ul>
											</div>
											
											<div class="giga-guide-section">
												<h5><?php esc_html_e( 'How to Set Up Custom Fields', 'giga-schema-pro' ); ?></h5>
												<ol class="giga-guide-list">
													<li><?php esc_html_e( 'Go to WooCommerce → Products → Attributes', 'giga-schema-pro' ); ?></li>
													<li><?php esc_html_e( 'Create new attributes for GTIN, MPN, and Brand', 'giga-schema-pro' ); ?></li>
													<li><?php esc_html_e( 'Assign these attributes to your products', 'giga-schema-pro' ); ?></li>
													<li><?php esc_html_e( 'The schema will automatically include these values', 'giga-schema-pro' ); ?></li>
												</ol>
											</div>
										</div>
									</div>
								</div>
							</div>
	
							<div class="giga-form-actions">
								<?php submit_button( __( 'Save Settings', 'giga-schema-pro' ), 'primary', 'giga_sp_woo_save' ); ?>
							</div>
						</form>
					</div>
	
					<!-- Advanced Settings -->
					<div id="advanced-settings" class="giga-panel" style="display: none;">
						<form method="post" action="" class="giga-settings-form">
							<?php wp_nonce_field( 'giga_sp_woo_settings' ); ?>
							
							<div class="giga-settings-grid">
								<div class="giga-settings-column">
									<div class="giga-advanced-section">
										<h3><?php esc_html_e( 'Schema Configuration', 'giga-schema-pro' ); ?></h3>
										
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" name="enable_aggregate_rating" checked disabled>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Enable Aggregate Rating Schema', 'giga-schema-pro' ); ?></span>
											</label>
											<p class="giga-form-description"><?php esc_html_e( 'Automatically includes product ratings and reviews in schema markup (Pro feature).', 'giga-schema-pro' ); ?></p>
										</div>
	
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" name="enable_review_schema" checked disabled>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Enable Individual Review Schema', 'giga-schema-pro' ); ?></span>
											</label>
											<p class="giga-form-description"><?php esc_html_e( 'Includes individual customer reviews in schema markup (Pro feature).', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
	
									<div class="giga-advanced-section">
										<h3><?php esc_html_e( 'Performance Settings', 'giga-schema-pro' ); ?></h3>
										
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" name="auto_generate_schema" checked>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Auto-generate schema for all products', 'giga-schema-pro' ); ?></span>
											</label>
											<p class="giga-form-description"><?php esc_html_e( 'Automatically apply schema to all WooCommerce products.', 'giga-schema-pro' ); ?></p>
										</div>
	
										<div class="giga-form-group">
											<label class="giga-form-label">
												<input type="checkbox" name="include_images" checked>
												<span class="giga-checkbox-label"><?php esc_html_e( 'Include product images in schema', 'giga-schema-pro' ); ?></span>
											</label>
											<p class="giga-form-description"><?php esc_html_e( 'Add product images to schema markup for rich results.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>
	
								<div class="giga-settings-column">
									<div class="giga-debug-info">
										<div class="giga-debug-header">
											<span class="dashicons dashicons-bug"></span>
											<h4><?php esc_html_e( 'Debug Information', 'giga-schema-pro' ); ?></h4>
										</div>
										<div class="giga-debug-content">
											<div class="giga-debug-item">
												<span class="giga-debug-label"><?php esc_html_e( 'Active Products:', 'giga-schema-pro' ); ?></span>
												<span class="giga-debug-value"><?php echo esc_html( $this->get_product_count() ); ?></span>
											</div>
											<div class="giga-debug-item">
												<span class="giga-debug-label"><?php esc_html_e( 'Schema Generated:', 'giga-schema-pro' ); ?></span>
												<span class="giga-debug-value"><?php echo esc_html( $this->get_schema_count() ); ?></span>
											</div>
											<div class="giga-debug-item">
												<span class="giga-debug-label"><?php esc_html_e( 'Last Updated:', 'giga-schema-pro' ); ?></span>
												<span class="giga-debug-value"><?php echo esc_html( date( 'F j, Y, g:i a' ) ); ?></span>
											</div>
										</div>
									</div>
	
									<div class="giga-actions-card">
										<div class="giga-actions-header">
											<span class="dashicons dashicons-hammer"></span>
											<h4><?php esc_html_e( 'Maintenance Actions', 'giga-schema-pro' ); ?></h4>
										</div>
										<div class="giga-actions-content">
											<button type="button" class="giga-btn giga-btn-secondary" onclick="gigaRegenerateAllSchema();">
												<span class="dashicons dashicons-update"></span>
												<?php esc_html_e( 'Regenerate All Schema', 'giga-schema-pro' ); ?>
											</button>
											<button type="button" class="giga-btn giga-btn-secondary" onclick="gigaClearSchemaCache();">
												<span class="dashicons dashicons-trash"></span>
												<?php esc_html_e( 'Clear Schema Cache', 'giga-schema-pro' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
	
							<div class="giga-form-actions">
								<?php submit_button( __( 'Save Settings', 'giga-schema-pro' ), 'primary', 'giga_sp_woo_save' ); ?>
							</div>
						</form>
					</div>
	
				</div>
			</div>
			<?php
		}

		/**
		 * Render settings page
		 *
		 * @since 1.0.0
		 */
		public function render_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Save settings if form submitted
			if ( isset( $_POST['giga_sp_settings_save'] ) && check_admin_referer( 'giga_sp_settings' ) ) {
				$settings = [
					'organization_name' => isset( $_POST['organization_name'] ) ? sanitize_text_field( $_POST['organization_name'] ) : '',
					'organization_logo' => isset( $_POST['organization_logo'] ) ? esc_url_raw( $_POST['organization_logo'] ) : '',
					'organization_url' => isset( $_POST['organization_url'] ) ? esc_url_raw( $_POST['organization_url'] ) : '',
					'organization_description' => isset( $_POST['organization_description'] ) ? sanitize_textarea_field( $_POST['organization_description'] ) : '',
					'social_facebook' => isset( $_POST['social_facebook'] ) ? esc_url_raw( $_POST['social_facebook'] ) : '',
					'social_twitter' => isset( $_POST['social_twitter'] ) ? esc_url_raw( $_POST['social_twitter'] ) : '',
					'social_linkedin' => isset( $_POST['social_linkedin'] ) ? esc_url_raw( $_POST['social_linkedin'] ) : '',
					'social_instagram' => isset( $_POST['social_instagram'] ) ? esc_url_raw( $_POST['social_instagram'] ) : '',
				];
				update_option( 'giga_sp_settings', $settings );
				self::log_activity(
					__( 'Organization settings updated', 'giga-schema-pro' ),
					'dashicons-admin-settings',
					'giga-sp-pass',
					__( 'Saved', 'giga-schema-pro' )
				);
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully.', 'giga-schema-pro' ) . '</p></div>';
			}

			$settings = get_option( 'giga_sp_settings', [] );
			$org_name = isset( $settings['organization_name'] ) ? $settings['organization_name'] : get_bloginfo( 'name' );
			$org_logo = isset( $settings['organization_logo'] ) ? $settings['organization_logo'] : '';
			$org_url = isset( $settings['organization_url'] ) ? $settings['organization_url'] : home_url();
			$org_desc = isset( $settings['organization_description'] ) ? $settings['organization_description'] : get_bloginfo( 'description' );
			$social_facebook = isset( $settings['social_facebook'] ) ? $settings['social_facebook'] : '';
			$social_twitter = isset( $settings['social_twitter'] ) ? $settings['social_twitter'] : '';
			$social_linkedin = isset( $settings['social_linkedin'] ) ? $settings['social_linkedin'] : '';
			$social_instagram = isset( $settings['social_instagram'] ) ? $settings['social_instagram'] : '';
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Giga Schema Pro Settings', 'giga-schema-pro' ); ?></h1>
				
				<div class="giga-settings-header">
					<div class="giga-settings-intro">
						<p><?php esc_html_e( 'Configure your organization information and social profiles to enhance your schema markup.', 'giga-schema-pro' ); ?></p>
					</div>
				</div>

				<form method="post" action="" class="giga-settings-form">
					<?php wp_nonce_field( 'giga_sp_settings' ); ?>
					
					<div class="giga-tabs">
						<button type="button" class="giga-sp-tab active" data-tab="organization-tab">
							<span class="dashicons dashicons-business"></span>
							<?php esc_html_e( 'Organization', 'giga-schema-pro' ); ?>
						</button>
						<button type="button" class="giga-sp-tab" data-tab="social-tab">
							<span class="dashicons dashicons-share-alt"></span>
							<?php esc_html_e( 'Social Profiles', 'giga-schema-pro' ); ?>
						</button>
					</div>

					<div id="organization-tab" class="giga-tab-content giga-settings-section">
						<div class="giga-settings-grid">
							<div class="giga-settings-column">
								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-business"></span>
										<h3><?php esc_html_e( 'Basic Information', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="organization_name" class="giga-form-label">
												<span class="dashicons dashicons-admin-site"></span>
												<?php esc_html_e( 'Organization Name', 'giga-schema-pro' ); ?>
											</label>
											<input type="text" name="organization_name" id="organization_name" value="<?php echo esc_attr( $org_name ); ?>" class="giga-form-input" required>
											<p class="giga-form-description"><?php esc_html_e( 'The official name of your organization.', 'giga-schema-pro' ); ?></p>
										</div>

										<div class="giga-form-group">
											<label for="organization_url" class="giga-form-label">
												<span class="dashicons dashicons-admin-links"></span>
												<?php esc_html_e( 'Organization URL', 'giga-schema-pro' ); ?>
											</label>
											<input type="url" name="organization_url" id="organization_url" value="<?php echo esc_attr( $org_url ); ?>" class="giga-form-input" required>
											<p class="giga-form-description"><?php esc_html_e( 'The main website URL of your organization.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>

								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-images"></span>
										<h3><?php esc_html_e( 'Branding', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="organization_logo" class="giga-form-label">
												<span class="dashicons dashicons-image"></span>
												<?php esc_html_e( 'Organization Logo URL', 'giga-schema-pro' ); ?>
											</label>
											<input type="url" name="organization_logo" id="organization_logo" value="<?php echo esc_attr( $org_logo ); ?>" class="giga-form-input">
											<p class="giga-form-description"><?php esc_html_e( 'Full URL to your organization logo image (recommended: 600x600px).', 'giga-schema-pro' ); ?></p>
											
											<?php if ( ! empty( $org_logo ) ) : ?>
												<div class="giga-logo-preview">
													<img src="<?php echo esc_url( $org_logo ); ?>" alt="<?php esc_attr_e( 'Organization Logo', 'giga-schema-pro' ); ?>" class="giga-logo-img">
												</div>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>

							<div class="giga-settings-column">
								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-text-page"></span>
										<h3><?php esc_html_e( 'Description', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="organization_description" class="giga-form-label">
												<span class="dashicons dashicons-editor-alignleft"></span>
												<?php esc_html_e( 'Organization Description', 'giga-schema-pro' ); ?>
											</label>
											<textarea name="organization_description" id="organization_description" rows="8" class="giga-form-input" required><?php echo esc_textarea( $org_desc ); ?></textarea>
											<p class="giga-form-description"><?php esc_html_e( 'A detailed description of your organization. This will be used in schema markup.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>

								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-info"></span>
										<h3><?php esc_html_e( 'Settings Summary', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-debug-item">
											<span class="giga-debug-label"><?php esc_html_e( 'Organization Name', 'giga-schema-pro' ); ?></span>
											<span class="giga-debug-value"><?php echo ! empty( $org_name ) ? esc_html( $org_name ) : esc_html__( 'Not set', 'giga-schema-pro' ); ?></span>
										</div>
										<div class="giga-debug-item">
											<span class="giga-debug-label"><?php esc_html_e( 'Logo', 'giga-schema-pro' ); ?></span>
											<span class="giga-debug-value"><?php echo ! empty( $org_logo ) ? esc_html__( 'Set', 'giga-schema-pro' ) : esc_html__( 'Not set', 'giga-schema-pro' ); ?></span>
										</div>
										<div class="giga-debug-item">
											<span class="giga-debug-label"><?php esc_html_e( 'Social Profiles', 'giga-schema-pro' ); ?></span>
											<span class="giga-debug-value"><?php echo ( ! empty( $social_facebook ) || ! empty( $social_twitter ) || ! empty( $social_linkedin ) || ! empty( $social_instagram ) ) ? esc_html__( 'Connected', 'giga-schema-pro' ) : esc_html__( 'Not connected', 'giga-schema-pro' ); ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div id="social-tab" class="giga-tab-content giga-settings-section" style="display: none;">
						<div class="giga-settings-grid">
							<div class="giga-settings-column">
								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-facebook"></span>
										<h3><?php esc_html_e( 'Facebook', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="social_facebook" class="giga-form-label">
												<span class="dashicons dashicons-facebook-alt"></span>
												<?php esc_html_e( 'Facebook Profile URL', 'giga-schema-pro' ); ?>
											</label>
											<input type="url" name="social_facebook" id="social_facebook" value="<?php echo esc_attr( $social_facebook ); ?>" class="giga-form-input">
											<p class="giga-form-description"><?php esc_html_e( 'Your Facebook profile or page URL.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>

								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-twitter"></span>
										<h3><?php esc_html_e( 'Twitter', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="social_twitter" class="giga-form-label">
												<span class="dashicons dashicons-twitter"></span>
												<?php esc_html_e( 'Twitter Profile URL', 'giga-schema-pro' ); ?>
											</label>
											<input type="url" name="social_twitter" id="social_twitter" value="<?php echo esc_attr( $social_twitter ); ?>" class="giga-form-input">
											<p class="giga-form-description"><?php esc_html_e( 'Your Twitter profile URL.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>
							</div>

							<div class="giga-settings-column">
								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-linkedin"></span>
										<h3><?php esc_html_e( 'LinkedIn', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="social_linkedin" class="giga-form-label">
												<span class="dashicons dashicons-linkedin"></span>
												<?php esc_html_e( 'LinkedIn Profile URL', 'giga-schema-pro' ); ?>
											</label>
											<input type="url" name="social_linkedin" id="social_linkedin" value="<?php echo esc_attr( $social_linkedin ); ?>" class="giga-form-input">
											<p class="giga-form-description"><?php esc_html_e( 'Your LinkedIn profile or company page URL.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>

								<div class="giga-settings-card">
									<div class="giga-card-header">
										<span class="dashicons dashicons-instagram"></span>
										<h3><?php esc_html_e( 'Instagram', 'giga-schema-pro' ); ?></h3>
									</div>
									<div class="giga-card-content">
										<div class="giga-form-group">
											<label for="social_instagram" class="giga-form-label">
												<span class="dashicons dashicons-instagram"></span>
												<?php esc_html_e( 'Instagram Profile URL', 'giga-schema-pro' ); ?>
											</label>
											<input type="url" name="social_instagram" id="social_instagram" value="<?php echo esc_attr( $social_instagram ); ?>" class="giga-form-input">
											<p class="giga-form-description"><?php esc_html_e( 'Your Instagram profile URL.', 'giga-schema-pro' ); ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="giga-form-actions">
						<?php submit_button( __( 'Save Settings', 'giga-schema-pro' ), 'primary', 'giga_sp_settings_save' ); ?>
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Render placeholder for pages not yet implemented
		 *
		 * @since 1.0.0
		 * @deprecated 1.0.0 Use specific render methods instead
		 */
		public function render_placeholder() {
			echo '<div class="wrap"><h1>' . esc_html__( 'Coming Soon', 'giga-schema-pro' ) . '</h1></div>';
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
				$upgrade_url = 'https://gigaverse.com/products/giga-schema-pro/';
				echo '<p><a href="' . esc_url( $upgrade_url ) . '" target="_blank">' . esc_html__( 'Upgrade to Giga Schema Pro', 'giga-schema-pro' ) . '</a> ' . esc_html__( 'to unlock Custom JSON-LD insertion & Validation test runs.', 'giga-schema-pro' ) . '</p>';
			}
		}

		public function save_meta_boxes( $post_id ) {
			// Security: Verify nonce
			if ( ! isset( $_POST['giga_sp_meta_nonce_field'] ) || ! wp_verify_nonce( wp_unslash( $_POST['giga_sp_meta_nonce_field'] ), 'giga_sp_meta_nonce' ) ) {
				return;
			}

			// Security: Don't save on autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Security: Check user capabilities
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Save disabled schema types
			$disabled = isset( $_POST['giga_sp_disable'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['giga_sp_disable'] ) ) : [];
			update_post_meta( $post_id, '_giga_sp_disabled_types', $disabled );

			// Save custom JSON-LD with validation (Pro feature)
			if ( isset( $_POST['giga_sp_custom'] ) && current_user_can( 'unfiltered_html' ) ) {
				$custom_json = trim( wp_unslash( $_POST['giga_sp_custom'] ) );

				// Validate JSON structure
				if ( ! empty( $custom_json ) ) {
					json_decode( $custom_json );
					if ( json_last_error() !== JSON_ERROR_NONE ) {
						add_settings_error(
							'giga_sp_custom',
							'invalid_json',
							sprintf(
								/* translators: %s: JSON error message */
								__( 'Invalid JSON-LD format: %s', 'giga-schema-pro' ),
								json_last_error_msg()
							)
						);
						return;
					}
				}

				update_post_meta( $post_id, '_giga_sp_custom_json', $custom_json );
			}
		}

		/**
			* Get product count for WooCommerce
			*
			* @since 1.0.0
			* @return int
			*/
		private function get_product_count() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return 0;
			}
			
			$prod_counts = wp_count_posts( 'product' );
			return (int) $prod_counts->publish;
		}

		/**
			* Get schema count for WooCommerce products
			*
			* @since 1.0.0
			* @return int
			*/
		private function get_schema_count() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return 0;
			}
			
			$products = get_posts( [
				'post_type' => 'product',
				'posts_per_page' => -1,
				'fields' => 'ids',
			] );
			
			return count( $products );
		}

		/**
		 * Get validation statistics
		 *
		 * @since 1.0.0
		 * @return array
		 */
		private function get_validation_stats() {
			$stats = [
				'products' => 0,
				'validated' => 0,
				'warnings' => 0,
				'errors' => 0,
			];
			
			// Get product count if WooCommerce is active
			if ( class_exists( 'WooCommerce' ) ) {
				$prod_counts = wp_count_posts( 'product' );
				$stats['products'] = (int) $prod_counts->publish;
			}
			
			// Get validation results
			$validation_results = get_option( 'giga_sp_validation_results', [] );
			if ( !empty($validation_results) && isset($validation_results['passed']) ) {
				$stats['validated'] = (int) $validation_results['passed'];
				$stats['warnings'] = (int) ($validation_results['warnings'] ?? 0);
				$stats['errors'] = (int) ($validation_results['failed'] ?? 0);
			}
			
			return $stats;
		}

		/**
		 * Display validation results
		 *
		 * @since 1.0.0
		 * @param array $results Validation results
		 */
		private function display_validation_results( $results ) {
			$timestamp = isset($results['timestamp']) ? date('F j, Y, g:i a', $results['timestamp']) : '';
			$total = isset($results['total']) ? $results['total'] : 0;
			$passed = isset($results['passed']) ? $results['passed'] : 0;
			$failed = isset($results['failed']) ? $results['failed'] : 0;
			$warnings = isset($results['warnings']) ? $results['warnings'] : 0;
			$errors = isset($results['errors']) ? $results['errors'] : [];
			
			$success_rate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
			?>
			<div class="giga-validation-summary">
				<div class="giga-validation-meta">
					<div class="giga-validation-timestamp">
						<span class="dashicons dashicons-clock"></span>
						<?php printf( esc_html__( 'Last run: %s', 'giga-schema-pro' ), esc_html( $timestamp ) ); ?>
					</div>
					<div class="giga-validation-rate">
						<span class="dashicons dashicons-percentage"></span>
						<?php printf( esc_html__( '%s%% Success Rate', 'giga-schema-pro' ), esc_html( $success_rate ) ); ?>
					</div>
				</div>
				
				<div class="giga-validation-stats">
					<div class="giga-validation-stat-item">
						<div class="giga-validation-stat-value passed"><?php echo esc_html( $passed ); ?></div>
						<div class="giga-validation-stat-label"><?php esc_html_e( 'Passed', 'giga-schema-pro' ); ?></div>
					</div>
					<div class="giga-validation-stat-item">
						<div class="giga-validation-stat-value warning"><?php echo esc_html( $warnings ); ?></div>
						<div class="giga-validation-stat-label"><?php esc_html_e( 'Warnings', 'giga-schema-pro' ); ?></div>
					</div>
					<div class="giga-validation-stat-item">
						<div class="giga-validation-stat-value error"><?php echo esc_html( $failed ); ?></div>
						<div class="giga-validation-stat-label"><?php esc_html_e( 'Failed', 'giga-schema-pro' ); ?></div>
					</div>
				</div>
			</div>
			
			<?php if ( !empty($errors) ) : ?>
				<div class="giga-validation-errors">
					<h4><?php esc_html_e( 'Issues Found', 'giga-schema-pro' ); ?></h4>
					<div class="giga-error-list">
						<?php foreach ( $errors as $error ) : ?>
							<div class="giga-error-item">
								<div class="giga-error-type"><?php echo esc_html( $error['type'] ); ?></div>
								<div class="giga-error-message"><?php echo esc_html( $error['message'] ); ?></div>
								<div class="giga-error-pages">
									<?php
									/* translators: %s: comma-separated list of pages */
									printf( esc_html__( 'Pages: %s', 'giga-schema-pro' ), '<strong>' . esc_html( implode(', ', $error['pages']) ) . '</strong>' );
									?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
			
			<div class="giga-validation-actions">
				<button type="button" class="giga-btn giga-btn-secondary" id="giga-rerun-validation">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Re-run Validation', 'giga-schema-pro' ); ?>
				</button>
				<button type="button" class="giga-btn giga-btn-secondary" id="giga-export-validation">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export Report', 'giga-schema-pro' ); ?>
				</button>
			</div>
			<?php
		}
	}
}
