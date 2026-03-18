<?php
/**
 * Plugin Name: WebP by Perfecten
 * Plugin URI: https://perfecten.example
 * Description: Convierte imagenes subidas a WebP de forma segura, con panel propio en WordPress y optimizacion basada en Imagick.
 * Version: 1.1.0
 * Author: Perfecten
 * Author URI: https://perfecten.example
 * Text Domain: webp-by-perfecten
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WebP_By_Perfecten' ) ) {
	final class WebP_By_Perfecten {
		private const OPTION_ENABLED    = 'wbbf_enabled';
		private const OPTION_MAX_WIDTH  = 'wbbf_max_width';
		private const OPTION_MAX_HEIGHT = 'wbbf_max_height';
		private const OPTION_QUALITY    = 'wbbf_quality';

		private const DEFAULT_ENABLED    = false;
		private const DEFAULT_MAX_WIDTH  = 1920;
		private const DEFAULT_MAX_HEIGHT = 1080;
		private const DEFAULT_QUALITY    = 82;

		private const SETTINGS_GROUP = 'wbbf_settings_group';
		private const MENU_SLUG      = 'webp-by-perfecten';

		public static function boot(): void {
			$instance = new self();
			$instance->register_hooks();
		}

		private function register_hooks(): void {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
			add_filter( 'wp_handle_upload', array( $this, 'handle_upload' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );

			register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		}

		public static function activate(): void {
			add_option( self::OPTION_ENABLED, self::DEFAULT_ENABLED );
			add_option( self::OPTION_MAX_WIDTH, self::DEFAULT_MAX_WIDTH );
			add_option( self::OPTION_MAX_HEIGHT, self::DEFAULT_MAX_HEIGHT );
			add_option( self::OPTION_QUALITY, self::DEFAULT_QUALITY );
		}

		public function register_settings(): void {
			register_setting(
				self::SETTINGS_GROUP,
				self::OPTION_ENABLED,
				array(
					'type'              => 'boolean',
					'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
					'default'           => self::DEFAULT_ENABLED,
				)
			);

			register_setting(
				self::SETTINGS_GROUP,
				self::OPTION_MAX_WIDTH,
				array(
					'type'              => 'integer',
					'sanitize_callback' => array( $this, 'sanitize_dimension' ),
					'default'           => self::DEFAULT_MAX_WIDTH,
				)
			);

			register_setting(
				self::SETTINGS_GROUP,
				self::OPTION_MAX_HEIGHT,
				array(
					'type'              => 'integer',
					'sanitize_callback' => array( $this, 'sanitize_dimension' ),
					'default'           => self::DEFAULT_MAX_HEIGHT,
				)
			);

			register_setting(
				self::SETTINGS_GROUP,
				self::OPTION_QUALITY,
				array(
					'type'              => 'integer',
					'sanitize_callback' => array( $this, 'sanitize_quality' ),
					'default'           => self::DEFAULT_QUALITY,
				)
			);
		}

		public function register_admin_menu(): void {
			add_menu_page(
				__( 'WebP by Perfecten', 'webp-by-perfecten' ),
				__( 'WebP by Perfecten', 'webp-by-perfecten' ),
				'manage_options',
				self::MENU_SLUG,
				array( $this, 'render_settings_page' ),
				'dashicons-format-image',
				58
			);
		}

		public function add_plugin_action_links( array $links ): array {
			$settings_link = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ),
				esc_html__( 'Settings', 'webp-by-perfecten' )
			);

			array_unshift( $links, $settings_link );

			return $links;
		}

		public function render_settings_page(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WebP by Perfecten', 'webp-by-perfecten' ); ?></h1>
				<p><?php esc_html_e( 'Configura la conversion automatica de imagenes a WebP desde este panel propio del plugin.', 'webp-by-perfecten' ); ?></p>

				<div style="max-width: 920px; background: #fff; border: 1px solid #dcdcde; border-radius: 10px; padding: 24px; margin-top: 20px;">
					<h2 style="margin-top: 0;"><?php esc_html_e( 'General Settings', 'webp-by-perfecten' ); ?></h2>
					<form action="options.php" method="post">
						<?php settings_fields( self::SETTINGS_GROUP ); ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Automatic conversion', 'webp-by-perfecten' ); ?></th>
								<td>
									<label for="<?php echo esc_attr( self::OPTION_ENABLED ); ?>">
										<input
											id="<?php echo esc_attr( self::OPTION_ENABLED ); ?>"
											name="<?php echo esc_attr( self::OPTION_ENABLED ); ?>"
											type="checkbox"
											value="1"
											<?php checked( (bool) get_option( self::OPTION_ENABLED, self::DEFAULT_ENABLED ) ); ?>
										/>
										<?php esc_html_e( 'Enable WebP conversion on upload', 'webp-by-perfecten' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'El archivo original se mantiene. Solo se sustituye el usado por WordPress si el WebP final pesa menos.', 'webp-by-perfecten' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Maximum width', 'webp-by-perfecten' ); ?></th>
								<td>
									<input
										name="<?php echo esc_attr( self::OPTION_MAX_WIDTH ); ?>"
										type="number"
										min="1"
										step="1"
										value="<?php echo esc_attr( (int) get_option( self::OPTION_MAX_WIDTH, self::DEFAULT_MAX_WIDTH ) ); ?>"
										class="small-text"
									/>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Maximum height', 'webp-by-perfecten' ); ?></th>
								<td>
									<input
										name="<?php echo esc_attr( self::OPTION_MAX_HEIGHT ); ?>"
										type="number"
										min="1"
										step="1"
										value="<?php echo esc_attr( (int) get_option( self::OPTION_MAX_HEIGHT, self::DEFAULT_MAX_HEIGHT ) ); ?>"
										class="small-text"
									/>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'WebP quality', 'webp-by-perfecten' ); ?></th>
								<td>
									<input
										name="<?php echo esc_attr( self::OPTION_QUALITY ); ?>"
										type="number"
										min="1"
										max="100"
										step="1"
										value="<?php echo esc_attr( (int) get_option( self::OPTION_QUALITY, self::DEFAULT_QUALITY ) ); ?>"
										class="small-text"
									/>
									<p class="description"><?php esc_html_e( 'Un valor entre 80 y 85 suele funcionar bien para sitios corporativos y ecommerce.', 'webp-by-perfecten' ); ?></p>
								</td>
							</tr>
						</table>
						<?php submit_button( __( 'Save Settings', 'webp-by-perfecten' ) ); ?>
					</form>
				</div>

				<div style="max-width: 920px; background: #fff; border: 1px solid #dcdcde; border-radius: 10px; padding: 24px; margin-top: 20px;">
					<h2 style="margin-top: 0;"><?php esc_html_e( 'Server status', 'webp-by-perfecten' ); ?></h2>
					<ul style="list-style: disc; padding-left: 20px;">
						<li><?php echo esc_html( extension_loaded( 'imagick' ) ? 'Imagick: OK' : 'Imagick: Missing' ); ?></li>
						<li><?php echo esc_html( $this->supports_webp() ? 'WebP support: OK' : 'WebP support: Missing' ); ?></li>
						<li><?php echo esc_html( $this->is_enabled() ? 'Automatic conversion: Enabled' : 'Automatic conversion: Disabled' ); ?></li>
					</ul>
				</div>
			</div>
			<?php
		}

		public function render_admin_notice(): void {
			if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'get_current_screen' ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( ! $screen || self::MENU_SLUG !== $screen->base && 'toplevel_page_' . self::MENU_SLUG !== $screen->id ) {
				return;
			}

			if ( ! extension_loaded( 'imagick' ) ) {
				$this->print_notice( 'warning', __( 'Imagick no esta instalado. La conversion a WebP no funcionara hasta habilitarlo en el servidor.', 'webp-by-perfecten' ) );
				return;
			}

			if ( ! $this->supports_webp() ) {
				$this->print_notice( 'warning', __( 'Imagick esta activo, pero no tiene soporte WebP habilitado.', 'webp-by-perfecten' ) );
			}
		}

		public function handle_upload( array $upload ): array {
			if ( ! $this->is_enabled() || ! $this->can_convert_images() ) {
				return $upload;
			}

			if ( empty( $upload['file'] ) || empty( $upload['type'] ) || ! file_exists( $upload['file'] ) ) {
				return $upload;
			}

			if ( ! $this->is_supported_mime_type( $upload['type'] ) ) {
				return $upload;
			}

			try {
				$imagick = new Imagick( $upload['file'] );

				if ( $imagick->getNumberImages() > 1 ) {
					$imagick->setIteratorIndex( 0 );
				}

				$this->normalize_orientation( $imagick );
				$this->resize_image( $imagick );
				$this->strip_nonessential_profiles( $imagick );
				$this->convert_to_webp( $imagick );

				$webp_file = $this->get_webp_path( $upload['file'] );
				$imagick->writeImage( $webp_file );
				$imagick->clear();
				$imagick->destroy();

				$original_size = filesize( $upload['file'] );
				$webp_size     = file_exists( $webp_file ) ? filesize( $webp_file ) : false;

				if ( false === $webp_size || false === $original_size || $webp_size >= $original_size ) {
					if ( file_exists( $webp_file ) ) {
						wp_delete_file( $webp_file );
					}

					return $upload;
				}

				$upload['file'] = $webp_file;
				$upload['type'] = 'image/webp';
				$upload['url']  = $this->replace_uploaded_filename_in_url( $upload['url'], basename( $webp_file ) );
			} catch ( Throwable $throwable ) {
				error_log( '[WebP by Perfecten] ' . $throwable->getMessage() );
			}

			return $upload;
		}

		public function sanitize_checkbox( $value ): bool {
			return (bool) rest_sanitize_boolean( $value );
		}

		public function sanitize_dimension( $value ): int {
			$value = absint( $value );
			return $value < 1 ? 1 : $value;
		}

		public function sanitize_quality( $value ): int {
			return max( 1, min( 100, absint( $value ) ) );
		}

		private function is_enabled(): bool {
			return (bool) get_option( self::OPTION_ENABLED, self::DEFAULT_ENABLED );
		}

		private function is_supported_mime_type( string $mime_type ): bool {
			$allowed_mime_types = array(
				'image/jpeg',
				'image/png',
				'image/gif',
				'image/heic',
				'image/heif',
			);

			return in_array( strtolower( $mime_type ), $allowed_mime_types, true );
		}

		private function can_convert_images(): bool {
			return extension_loaded( 'imagick' ) && $this->supports_webp();
		}

		private function supports_webp(): bool {
			if ( ! class_exists( 'Imagick' ) ) {
				return false;
			}

			try {
				$formats = Imagick::queryFormats( 'WEBP' );
			} catch ( Throwable $throwable ) {
				return false;
			}

			return ! empty( $formats );
		}

		private function normalize_orientation( Imagick $imagick ): void {
			switch ( $imagick->getImageOrientation() ) {
				case Imagick::ORIENTATION_BOTTOMRIGHT:
					$imagick->rotateImage( '#000', 180 );
					break;
				case Imagick::ORIENTATION_RIGHTTOP:
					$imagick->rotateImage( '#000', 90 );
					break;
				case Imagick::ORIENTATION_LEFTBOTTOM:
					$imagick->rotateImage( '#000', -90 );
					break;
			}

			$imagick->setImageOrientation( Imagick::ORIENTATION_TOPLEFT );
		}

		private function resize_image( Imagick $imagick ): void {
			$max_width  = (int) get_option( self::OPTION_MAX_WIDTH, self::DEFAULT_MAX_WIDTH );
			$max_height = (int) get_option( self::OPTION_MAX_HEIGHT, self::DEFAULT_MAX_HEIGHT );
			$width      = $imagick->getImageWidth();
			$height     = $imagick->getImageHeight();

			if ( $width <= $max_width && $height <= $max_height ) {
				return;
			}

			$imagick->resizeImage( $max_width, $max_height, Imagick::FILTER_LANCZOS, 1, true );
		}

		private function strip_nonessential_profiles( Imagick $imagick ): void {
			try {
				$imagick->stripImage();
			} catch ( Throwable $throwable ) {
				// Continue without stripping metadata if the server blocks this operation.
			}
		}

		private function convert_to_webp( Imagick $imagick ): void {
			$quality = (int) get_option( self::OPTION_QUALITY, self::DEFAULT_QUALITY );

			$imagick->setImageFormat( 'webp' );
			$imagick->setImageCompressionQuality( $quality );
			$imagick->setOption( 'webp:method', '6' );
			$imagick->setOption( 'webp:auto-filter', 'true' );
		}

		private function get_webp_path( string $original_file ): string {
			$path = pathinfo( $original_file );
			return trailingslashit( $path['dirname'] ) . $path['filename'] . '.webp';
		}

		private function replace_uploaded_filename_in_url( string $url, string $new_filename ): string {
			return preg_replace( '#[^/]+$#', $new_filename, $url ) ?: $url;
		}

		private function print_notice( string $type, string $message ): void {
			printf(
				'<div class="notice notice-%1$s"><p><strong>WebP by Perfecten:</strong> %2$s</p></div>',
				esc_attr( $type ),
				esc_html( $message )
			);
		}
	}

	WebP_By_Perfecten::boot();
}
