<?php
/**
 * Untappd Business API Client
 *
 * @package OnTap\API
 * @since   1.0.0
 */

namespace OnTap\API;

/**
 * Untappd Client class
 */
class Untappd_Client {

	/**
	 * API Base URL
	 */
	const API_BASE = 'https://business.untappd.com/api/v1';

	/**
	 * User email
	 *
	 * @var string
	 */
	private $email;

	/**
	 * API token
	 *
	 * @var string
	 */
	private $token;

	/**
	 * Constructor
	 *
	 * @param string $email Email address.
	 * @param string $token API token.
	 */
	public function __construct( $email = '', $token = '' ) {
		$settings = get_option( 'ontap_settings', array() );

		$this->email = $email ?: ( $settings['untappd_email'] ?? '' );
		$this->token = $token ?: ( $settings['untappd_api_token'] ?? '' );
	}

	/**
	 * Authenticate with email and password to get API token
	 *
	 * @param string $email    Email address.
	 * @param string $password Password.
	 * @param bool   $read_only Get read-only token (default: false for read/write).
	 * @return array|WP_Error Response with auth_token or error
	 */
	public static function authenticate( $email, $password, $read_only = false ) {
		$response = wp_remote_post(
			self::API_BASE . '/sessions',
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'email'    => $email,
						'password' => $password,
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return new \WP_Error(
				'untappd_auth_failed',
				isset( $body['error'] ) ? $body['error'] : __( 'Authentication failed', 'ontap' ),
				array( 'status' => $code )
			);
		}

		// Return the appropriate token
		$token_key = $read_only ? 'auth_token_read_only' : 'auth_token';

		return array(
			'email' => $body['user']['email'],
			'token' => $body['user'][ $token_key ],
		);
	}

	/**
	 * Get authorization header value
	 *
	 * @return string Base64 encoded email:token
	 */
	private function get_auth_header() {
		return 'Basic ' . base64_encode( $this->email . ':' . $this->token );
	}

	/**
	 * Make API request
	 *
	 * @param string $endpoint API endpoint (without base URL).
	 * @param array  $args     Additional request arguments.
	 * @return array|WP_Error Response data or error
	 */
	private function request( $endpoint, $args = array() ) {
		$defaults = array(
			'headers' => array(
				'Authorization' => $this->get_auth_header(),
				'Accept'        => 'application/json',
			),
			'timeout' => 30,
		);

		$args = wp_parse_args( $args, $defaults );
		$url  = self::API_BASE . $endpoint;

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return new \WP_Error(
				'untappd_api_error',
				isset( $body['error'] ) ? $body['error'] : __( 'API request failed', 'ontap' ),
				array( 'status' => $code )
			);
		}

		return $body;
	}

	/**
	 * Get all locations
	 *
	 * @return array|WP_Error Array of locations or error
	 */
	public function get_locations() {
		$cache_key = 'ontap_locations_' . md5( $this->email );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = $this->request( '/locations' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$locations = $response['locations'] ?? array();

		// Cache for 1 hour
		set_transient( $cache_key, $locations, HOUR_IN_SECONDS );

		return $locations;
	}

	/**
	 * Get single location
	 *
	 * @param int $location_id Location ID.
	 * @return array|WP_Error Location data or error
	 */
	public function get_location( $location_id ) {
		$cache_key = 'ontap_location_' . $location_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = $this->request( '/locations/' . $location_id );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$location = $response['location'] ?? null;

		if ( $location ) {
			set_transient( $cache_key, $location, HOUR_IN_SECONDS );
		}

		return $location;
	}

	/**
	 * Get menus for a location
	 *
	 * @param int $location_id Location ID.
	 * @return array|WP_Error Array of menus or error
	 */
	public function get_menus( $location_id ) {
		$cache_key = 'ontap_menus_' . $location_id;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = $this->request( '/locations/' . $location_id . '/menus' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$menus = $response['menus'] ?? array();

		set_transient( $cache_key, $menus, HOUR_IN_SECONDS );

		return $menus;
	}

	/**
	 * Get full menu with items
	 *
	 * @param int $menu_id Menu ID.
	 * @return array|WP_Error Menu data with items or error
	 */
	public function get_menu( $menu_id ) {
		$settings      = get_option( 'ontap_settings', array() );
		$cache_duration = $settings['cache_duration'] ?? 3600;
		$cache_key     = 'ontap_menu_' . $menu_id;
		$cached        = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = $this->request( '/menus/' . $menu_id . '?full=true' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$menu = $response['menu'] ?? null;

		if ( $menu ) {
			set_transient( $cache_key, $menu, $cache_duration );
		}

		return $menu;
	}

	/**
	 * Test connection
	 *
	 * @return bool|WP_Error True if connection successful, error otherwise
	 */
	public function test_connection() {
		$response = $this->get_locations();

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return true;
	}

	/**
	 * Clear all cached data
	 *
	 * @return void
	 */
	public static function clear_cache() {
		global $wpdb;

		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_ontap_location%'
			OR option_name LIKE '_transient_timeout_ontap_location%'
			OR option_name LIKE '_transient_ontap_menu%'
			OR option_name LIKE '_transient_timeout_ontap_menu%'"
		);
	}
}
