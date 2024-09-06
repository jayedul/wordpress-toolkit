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

	private $path = 'solidie-login';
	private $configs;

	public function __construct( $configs ) {
		$this->configs = $configs;
		add_action( 'init', 'alterLoginURL');
	}

	public function alterLoginURL() {

		global $pagenow;

		$permalink = trailingslashit( get_home_url() ) . $this->path . '/';
    
		if ( $pagenow == 'wp-login.php' && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			wp_redirect( $permalink );
			exit;
		}

		$current_url = ( is_ssl() ? 'https' : 'http' ) . '://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		if ( $current_url === $permalink ) {
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
					></div>
					<script src="<?php echo $this->configs->app_url . 'vendor/solidie/solidie-lib/dist/login.js?version=' . $this->configs->version; ?>"></script>
				</body>
			</html>
			<?php
			exit;
		}
	}
}
