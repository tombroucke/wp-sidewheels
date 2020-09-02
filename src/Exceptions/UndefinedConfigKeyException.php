<?php // phpcs:ignore
namespace Otomaties\WP_Sidewheels\Exceptions;

use \Exception;

/**
 * Exception in case a given key doesn't exists in the config file
 */
class UndefinedConfigKeyException extends Exception {

	/**
	 * Customize error message
	 *
	 * @param string $key The requested key.
	 */
	public function __construct( $key ) {
		$message = sprintf( '%s is not defined in your configuration file', $key );
		parent::__construct( $message, 1 );
	}
}
