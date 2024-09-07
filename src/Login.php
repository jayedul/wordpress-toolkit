<?php
/**
 * Login reg functionalities
 *
 * @package solidie/solidie-lib
 */

namespace SolidieLib;

/**
 * File and directory handler class
 */
class Login {

	private string $path = 'solidie-login';
	private array $configs;

	public function __construct( $configs ) {
		$this->configs = $configs;
		add_action( 'init', array( $this, 'alterLoginURL' ) );
	}

	public function alterLoginURL() {

		global $pagenow;

		$permalink = trailingslashit( get_home_url() ) . $this->path . '/';
    
		if ( $pagenow == 'wp-login.php' && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			wp_redirect( add_query_arg( $permalink, ( is_array( $_GET ) ? $_GET : array() ) ) );
			exit;
		}

		$current_url = ( is_ssl() ? 'https' : 'http' ) . '://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		$parsed      = parse_url( $current_url );
		$current_per = trailingslashit( $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'] );
		
		if ( $current_per === $permalink ) {
			?>
			<!doctype html>
			<html data-theme="light">
				<head>
					<meta charset="UTF-8" />
					<meta name="viewport" content="width=device-width, initial-scale=1" />
					<title>Login | <?php echo bloginfo( 'name' ) ?></title>
				</head>
				<body style="margin: 0; padding: 0;">
					<div 
						id="solidie_login_screen" 
						class="height-p-100 width-p-100"
						data-redirect_to="<?php echo esc_url( ! empty( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : get_home_url() ) ?>"
					></div>
					<script src="<?php echo $this->configs['app_url'] . 'vendor/solidie/solidie-lib/dist/login.js?version=' . $this->configs['version']; ?>"></script>
				</body>
			</html>
			<?php
			exit;
		}
	}
}
