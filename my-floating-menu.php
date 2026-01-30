<?php
/**
 * Plugin Name: Ru Floating Menu
 * Plugin URI: https://rumedia.vn
 * Description: A floating menu plugin with configurable items.
 * Version: 1.0.0
 * Author: RU Media
 * Author URI: https://rumedia.vn
 * License: GPLv2 or later
 * Text Domain: ru-floating-menu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ru_Floating_Menu {

	private $option_name = 'ru_fm_items';

	public function __construct() {
		// Admin hooks
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_post_ru_fm_save', array( $this, 'save_settings' ) );

		// Frontend hooks
		add_action( 'wp_footer', array( $this, 'render_menu' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			'Floating Menu',
			'Floating Menu',
			'manage_options',
			'ru-floating-menu',
			array( $this, 'render_admin_page' ),
			'dashicons-share-alt',
			100
		);
	}

	public function admin_scripts( $hook ) {
		if ( 'toplevel_page_ru-floating-menu' !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'ru-fm-admin', plugin_dir_url( __FILE__ ) . 'admin-script.js', array( 'jquery' ), '1.0.0', true );
		// Pass existing items to JS
		$items = get_option( $this->option_name, array() );
		wp_localize_script( 'ru-fm-admin', 'ruFmData', array(
			'items' => $items
		) );
		
		// Add some basic admin styles inline for simplicity
		wp_add_inline_style( 'common', '
			.ru-fm-item-row { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #fff; display: flex; align-items: center; gap: 15px; }
			.ru-fm-item-handle { cursor: move; color: #aaa; font-size: 20px; line-height: 1; }
			.ru-fm-icon-preview { width: 50px; height: 50px; flex-shrink: 0; background-color: #f5f5f5; border: 1px solid #e5e5e5; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
			.ru-fm-icon-preview img { width: 100%; height: 100%; object-fit: contain; display: block; }
			.ru-fm-inputs { flex: 1; display: grid; grid-template-columns: 2fr 3fr 1fr auto; gap: 15px; align-items: center; }
			.ru-fm-actions { display: flex; gap: 5px; align-items: center; }
			.ru-fm-actions .button { display: inline-flex; align-items: center; justify-content: center; padding: 0 5px; }
		' );
	}
	
	public function frontend_scripts() {
		wp_enqueue_style( 'ru-fm-style', plugin_dir_url( __FILE__ ) . 'style.css', array(), '1.1.0' );
		wp_enqueue_script( 'ru-fm-frontend', plugin_dir_url( __FILE__ ) . 'frontend-script.js', array( 'jquery' ), '1.0.0', true );
		
		$items = get_option( $this->option_name, array() );
		wp_localize_script( 'ru-fm-frontend', 'ruFmFrontend', array(
			'items' => $items
		) );
	}

	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1>Floating Menu Settings</h1>
			<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
				<input type="hidden" name="action" value="ru_fm_save">
				<?php wp_nonce_field( 'ru_fm_save_action', 'ru_fm_nonce' ); ?>
				
				<div id="ru-fm-items-container">
					<!-- Items will be injected here by JS -->
				</div>

				<button type="button" class="button" id="ru-fm-add-item">Add Item</button>

				<!-- Hidden input to store the JSON string before submit -->
				<textarea name="ru_fm_items_json" id="ru_fm_items_json" style="display:none;"></textarea>
				
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
				</p>
			</form>
			<p style="margin-top: 20px; font-style: italic; color: #666;">Plugin by <a href="https://rumedia.vn" target="_blank">RU Media</a></p>
		</div>
		<?php
	}

	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'ru_fm_save_action', 'ru_fm_nonce' );

		if ( isset( $_POST['ru_fm_items_json'] ) ) {
			$json_data = stripslashes( $_POST['ru_fm_items_json'] );
			$items = json_decode( $json_data, true );
			
			// Basic sanitization
			if ( is_array( $items ) ) {
				$sanitized_items = array();
				foreach ( $items as $item ) {
					$sanitized_items[] = array(
						'icon'   => esc_url_raw( $item['icon'] ),
						'name'   => sanitize_text_field( $item['name'] ),
						'link'   => esc_url_raw( $item['link'] ),
						'target' => sanitize_text_field( $item['target'] )
					);
				}
				update_option( $this->option_name, $sanitized_items );
			}
		}

		wp_redirect( add_query_arg( 'page', 'ru-floating-menu', admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render_menu() {
		$items = get_option( $this->option_name, array() );
		if ( empty( $items ) ) {
			return;
		}
		?>
		<!-- Desktop Menu -->
		<div class="ru-floating-menu">
			<div class="ru-fm-toggle" title="Thu gọn / Mở rộng">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
			</div>
			<ul>
				<?php foreach ( $items as $item ) : ?>
					<li class="ru-fm-item">
						<a href="<?php echo esc_url( $item['link'] ); ?>" target="<?php echo esc_attr( $item['target'] ); ?>" title="<?php echo esc_attr( $item['name'] ); ?>">
							<span class="ru-fm-icon">
								<?php if ( ! empty( $item['icon'] ) ) : ?>
									<img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>">
								<?php endif; ?>
							</span>
							<span class="ru-fm-label"><?php echo esc_html( $item['name'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Mobile Wrapper -->
		<div class="ru-fm-mobile-wrapper">
			<div class="ru-fm-mobile-trigger">
				<!-- Icons will be cycled here via JS -->
				<div class="ru-fm-rotating-icon">
					<?php if ( ! empty( $items[0]['icon'] ) ) : ?>
						<img src="<?php echo esc_url( $items[0]['icon'] ); ?>" alt="Menu">
					<?php endif; ?>
				</div>
			</div>

			<div class="ru-fm-mobile-popup">
				<div class="ru-fm-popup-header">
					<span>Liên hệ với chúng tôi</span>
					<div class="ru-fm-popup-close">&times;</div>
				</div>
				<div class="ru-fm-popup-body">
					<ul>
						<?php foreach ( $items as $item ) : ?>
							<li>
								<a href="<?php echo esc_url( $item['link'] ); ?>" target="<?php echo esc_attr( $item['target'] ); ?>">
									<span class="ru-fm-popup-icon">
										<?php if ( ! empty( $item['icon'] ) ) : ?>
											<img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>">
										<?php endif; ?>
									</span>
									<span class="ru-fm-popup-label"><?php echo esc_html( $item['name'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<div class="ru-fm-mobile-backdrop"></div>
		</div>
		<?php
	}
}

new Ru_Floating_Menu();