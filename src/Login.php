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
	private string $login_link;

	public function __construct( $configs ) {
		
		$this->configs = $configs;
		$this->login_link = trailingslashit( get_home_url() ) . $this->path . '/';

		add_action( 'init', array( $this, 'alterLoginURL' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadScript' ) );
		add_filter( 'template_include', array( $this, 'registerTemplate' ) );
	}

	private function isLoginPage() {

		$current_host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) );
		$current_url  = ( is_ssl() ? 'https' : 'http' ) . '://' . $current_host . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		$parsed       = parse_url( $current_url );
		$current_per  = trailingslashit( $parsed['scheme'] . '://' . $current_host . $parsed['path'] );
		
		return $current_per === $this->login_link;
	}

	public function alterLoginURL() {
		global $pagenow;    
		if ( $pagenow == 'wp-login.php' && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			wp_redirect(add_query_arg( ( is_array( $_GET ) ? $_GET : array() ), $this->login_link ) );
			exit;
		}
	}

	public function loadScript() {
		if ( ! is_admin() && $this->isLoginPage() ) {
			wp_enqueue_script( 'solidie-login-scripts', $this->configs['app_url'] . 'vendor/solidie/solidie-lib/dist/login.js', array( 'jquery' ), $this->configs['version'], true );
		}
	}

	public function registerTemplate( $template ) {
		if ( $this->isLoginPage() ) {
			$template = dirname( __DIR__ ) . '/templates/login.php'; 
		}
		return $template;
	}
}
