<?php
namespace SolidieLib;

class Updater {

	private $app_name;
	private $option_key;
	private $page_slug;
	private $api_endpoint;
	private $root_menu;
	private $user_role;
	private $screen_label;
	private $app_configs;

	const PREREQUISITES = array(
		'licenseKeySubmit' => array(
			'role' => array(
				'administrator'
			)
		)
	);

	/**
	 * Updater constructor
	 *
	 * @param array $configs
	 *
	 * @return void
	 */
	function __construct( $configs ) {
		
		$this->app_name     = $configs['app_name'];
		$this->option_key   = $this->app_name . '-license-data';
		$this->page_slug    = $this->app_name . '-license';
		$this->api_endpoint = $configs['api_endpoint'];
		$this->root_menu    = $configs['root_menu'];
		$this->user_role    = $configs['user_role'];
		$this->screen_label = $configs['screen_label'];
		$this->app_configs  = $configs['app_configs'];
		
		// Register license page hooks if parent page slug defined, it means the content is not free and requires license activation to get updates. 
		add_action( 'admin_menu', array( $this, 'addLicensePage' ), 100 );

		// Register plugin api request hooks
		add_filter( 'plugins_api', array( $this, 'getPluginInfo' ), 20, 3 );

		// Check for plugin update
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'checkUpdate' ) );

		// License key notice
		add_action( 'admin_notices', array( $this, 'showLicenseNotice' ) );

		// Load license page script
		add_action( 'admin_enqueue_scripts', array( $this, 'loadScript' ) );
	}

	/**
	 * License key error
	 *
	 * @return void
	 */
	public function showLicenseNotice() {

		if ( is_admin() && ! $this->isLicenseActive()) {

			$message = sprintf(
				__( 'There is an error with your Solidie Pro License. Automatic update has been turned off. %sResolve Now%s' ),
				"<a href='" . admin_url( 'admin.php?page=' . $this->page_slug ) . "'>",
				'</a>'
			);

			printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
		}
	}

	/**
	 * Add license key submission as a sub menu under defined parent.
	 *
	 * @return void
	 */
	public function addLicensePage() {
		add_submenu_page(
			$this->root_menu,
			$this->screen_label, 
			$this->screen_label, 
			$this->user_role, 
			$this->page_slug, 
			array( $this, 'licenseForm' ) 
		);
	}

	/**
	 * License key submission page html contents
	 *
	 * @return void
	 */
	public function licenseForm() {
		// Refresh license state before page load
		$this->APICall();

		$license = $this->getSavedLicense();

		// Load the form now
		echo '<div id="solidie_license_page" data-license="' . ( $license ? esc_attr( wp_json_encode( $license ) ) : '' ) . '"></div>';
	}

	/**
	 * Return prepared request
	 *
	 * @param string|null $action
	 * @return object|null
	 */
	private function APICall( $action = null, $license_key = null ) {
		if ( empty( $action ) ) {
			$action = 'update-check';
		}

		if ( ! $license_key ) {
			$license_info = $this->getSavedLicense();
			$license_key  = is_array( $license_info ) ? ( $license_info['license_key'] ?? '' ) : '';

			// If the license is not activated, no need to check update.
			if ( 'update-check' === $action && ! $this->isLicenseActive() ) {
				return null;
			}
		}

		$payload =  array(
			'license_key' => $license_key,
			'endpoint'    => get_home_url(),
			'app_name'    => $this->app_name,
			'action'      => $action,
		);

		$request  = wp_remote_post( $this->api_endpoint, array( 'body' => $payload ) );
		$response = ( ! is_wp_error( $request ) && is_array( $request ) ) ? @json_decode( $request['body'] ?? null ) : null;

		// Set fall back
		$response          = is_object( $response ) ? $response : new \stdClass();
		$response->success = $response->success ?? false;
		$response->data    = $response->data ?? new \stdClass();
		
		// Deactivate key if any request send falsy
		if ( $response && isset( $response->data->activated ) && $response->data->activated === false ) {
			update_option(
				$this->option_key, 
				array( 
					'activated'   => false, 
					'license_key' => $license_key,
					'message'     => $response->data->message ?? esc_html__( 'The license key is expired or revoked!' ) 
				) 
			);
		}

		return $response;
	}

	/**
	 * Activate license key on submit. Only this one will be called from user ajax request.
	 *
	 * @param string $license_key The license key setup
	 * 
	 * @return void
	 */
	public function licenseKeySubmit( string $license_key ) {

		$response    = $this->APICall( 'activate-license', $license_key );

		if ( is_object( $response ) &&  $response->success ) {
			$license_info = array(
				'license_key' => $license_key,
				'activated'   => $response->data->activated ? true : false,
				'licensee'    => $response->data->licensee ?? null,
				'expires_on'  => $response->data->expires_on ?? null,
				'plan_name'   => $response->data->plan_name ?? null,
				'message'     => $response->data->message ?? null,
			);

			update_option( $this->option_key, $license_info );
			wp_send_json_success( array( 'license' => $license_info ) );

		} else {
			$message = is_object( $response ) ? ( $response->data->message ?? null ) : null;
			wp_send_json_error( array( 'message' => $message ?? esc_html__( 'Request error!' ) ) );
		}

		exit;
	}

	/**
	 * @param $res
	 * @param $action
	 * @param $args
	 *
	 * @return bool|\stdClass
	 *
	 * Get the plugin info from server
	 */
	function getPluginInfo( $res, $action, $args ) {

		if ( $action !== 'plugin_information' || ( $this->app_configs->basename !== $args->slug ) ) {
			return false;
		}

		$remote       = $this->APICall();
		$res          = new \stdClass();
		$res->slug    = $this->app_name;
		$res->name    = 'Solidie Pro';
		$res->version = $this->app_configs->version;
		
		if ( is_object( $remote ) && $remote->success ) {
			$res->version      = $remote->data->version;
			$res->last_updated = date_format( date_create( '@' . $remote->data->release_timestamp ), "Y-m-d H:i:s" );
			$res->sections     = array(
				'changelog' => nl2br( ( string ) $remote->data->changelog ?? '' ),
			);
		}
		
		return $res;
	}

	/**
	 * @param $transient
	 *
	 * @return mixed
	 */
	public function checkUpdate( $transient ) {
		
		$update_info  = null;
		$request_body = $this->APICall();

		if ( 
			is_object( $request_body ) && 
			$request_body->success && 
			version_compare( $this->app_configs->version, $request_body->data->version, '<' )
		) {
			$update_info = array(
				'new_version'   => $request_body->data->version,
				'package'       => $request_body->data->download_url,
				'slug'          => $this->app_configs->basename,
				'url'           => $request_body->data->content_permalink,
			);
		}

		// Now update this content data in the transient
		if ( is_object( $transient ) ) {
			$transient->response[ $this->app_configs->basename ] = $update_info ? (object)$update_info : null;
		}

		return $transient;
	}

	/**
	 * Get saved license
	 *
	 * @return void
	 */
	private function getSavedLicense() {
		// Get from option. Not submitted yet if it is empty.
		$license_option = get_option( $this->option_key, null );
		if ( empty( $license_option ) ) {
			return null;
		}

		// Unsrialize the license info
		$license = maybe_unserialize( $license_option );
		$license = is_array( $license ) ? $license : array();

		$keys = array(
			'activated',
			'license_key',
			'licensee',
			'expires_on',
			'plan_name',
			'message'
		);

		foreach ( $keys as $key ) {
			$license[ $key ] = ! empty( $license[ $key ] ) ? $license[ $key ] : null;
		}

		return $license;
	}

	/**
	 * Check if license active
	 * 
	 * @return bool
	 */
	private function isLicenseActive() {
		$license = $this->getSavedLicense();
		return is_array( $license ) && ( $license['activated'] ?? false ) == true;
	}

	public function loadScript() {
		wp_enqueue_script( 'solidie-license-script',  $this->app_configs->dist_url . 'license.js', array( 'jquery', 'wp-i18n' ), $this->app_configs->version, true );
	}
}
