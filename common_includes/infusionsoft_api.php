<?php
global $infusionsoft;

require_once 'class-IXR.php';
require_once 'class-wp-http-ixr-client.php';

if ( !class_exists( 'Infusionsoft_4578' )){
	class Infusionsoft_4578 {
		public $api_key;
		public $error = FALSE;
		public $subdomain;

		public function __construct( $subdomain = NULL, $api_key = NULL ) {
			$this->subdomain = $subdomain;
			$this->api_key = $api_key;

			if ( empty( $this->subdomain ) || empty( $this->api_key ) ) {
				$this->error = new Exception( 'invalid-request: You must provide a subdomain and API key for your Infusionsoft application.');
			}
		}

		public function __call( $name, $arguments ) {
			// Make sure no error already exists
			if ( $this->error ) {
				return new Exception( 'invalid-request: You must provide a subdomain and API key for your Infusionsoft application.');
			}

			// Get the full method name with the service and method
			$method = ucfirst( $name ) . 'Service' . '.' . array_shift( $arguments );
			$arguments = array_merge( array( $method, $this->api_key ), $arguments );

			// Initialize the client
			$client = new WP_HTTP_IXR_Client( 'https://' . $this->subdomain . '.infusionsoft.com/api/xmlrpc' );

			// Call the function and return any error that happens
			if ( ! call_user_func_array( array( $client, 'query' ), $arguments ) ) {
				return new Exception( 'invalid-request: '. $client->getErrorMessage() );
			}

			// Pass the response directly to the user
			return $client->getResponse();
		}
	}
}//end exists class
//please do not open following comment. It is to show syntax
//$infusionsoft = new Infusionsoft( $subdomain, $api_key );


