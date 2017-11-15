<?php
/**
 * WP-Untappd-API (https://docs.business.untappd.com/#introduction)
 *
 * @package WP-Untappd-API
 */

/*
* Plugin Name: WP Untappd API
* Plugin URI: https://github.com/wp-api-libraries/wp-untappd-api
* Description: Perform API requests to Untappd in WordPress.
* Author: WP API Libraries
* Version: 1.0.0
* Author URI: https://wp-api-libraries.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-untappd-api
* GitHub Branch: master
*/

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) { exit; }


/* Check if class exists. */
if ( ! class_exists( 'WPUntappdAPI' ) ) {

	/**
	 * Untappd API Class.
	 */
	class WPUntappdAPI {

		/**
		 * Route being called.
		 *
		 * @var string
		 */
		protected $route = '';

		/**
		 * Untappd Site
		 *
		 * @var string
		 */
		static private $email;
		
		/**
		 * Untappd Site
		 *
		 * @var string
		 */
		static private $api_token;


		/**
		 * Untappd links for pagination
		 *
		 * @var string
		 */
		public $links;

		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://business.untappd.com/api/v1/';


		/**
		 * Construct.
		 *
		 * @access public
		 * @param mixed $api_key API Key.
		 * @param mixed $Untappd_site Untappd Site URL.
		 * @return void
		 */
		public function __construct( $email , $api_token ) {
			static::$email = trim($email);
			static::$api_token = trim($api_token);
		}

		/**
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $route, $args = array(), $method = 'GET' ) {
			// Headers get added first.
			$this->set_headers();

			// Add Method and Route.
			$this->args['method'] = $method;
			$this->route = $route;

			// Generate query string for GET requests.
			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $route );
			}
			// Add to body for all other requests. (Json encode if content-type is json).
			elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}

			return $this;
		}


		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch() {
			// Make the request.
			$response = wp_remote_request( $this->base_uri . $this->route, $this->args );

			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->clear();
			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-untappd-api' ), $code ), $body );
			}

			return $body;
		}

		/**
		 * Set request headers.
		 */
		protected function set_headers() {
			// Set request headers.
			$this->args['headers'] = array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode(  static::$email . ':' . static::$api_token ),
			);
		}

		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}

		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}

		/**
		 * Get Locations.
		 *
		 * @access public
		 * @param mixed $conditions Conditions.
		 * @return void
		 */
		public function get_locations( $args = array() ) {
			return $this->build_request( 'locations', $args )->fetch();
		}


	}
}
