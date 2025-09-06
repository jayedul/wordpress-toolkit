<?php
/**
 * String related functions
 *
 * @package solidie/solidie-lib
 */

namespace SolidieLib;

/**
 * String handler class
 */
class _String {
	
	/**
	 * Generate random string
	 *
	 * @param stirng $prefix Prefix
	 * @param stirng $postfix Postfix
	 *
	 * @return string
	 */
	public static function getRandomString( $prefix = 'r', $postfix = 'r' ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$ms         = (string) microtime( true );
		$ms         = str_replace( '.', '', $ms );
		$string     = $prefix . $ms;

		for ( $i = 0; $i < 5; $i++ ) {
			$string .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $string . $postfix;
	}

	/**
	 * Check if a value is float
	 *
	 * @param string|int|float $numeric_string The value to check if float
	 * @return boolean
	 */
	public static function isFloat( $numeric_string ) {
		return is_numeric( $numeric_string ) && strpos( $numeric_string, '.' ) !== false;
	}

	/**
	 * Cast a string value to nearest data type
	 *
	 * @param string $value The value to convert to nearest data type
	 *
	 * @return mixed
	 */
	public static function castValue( $value ) {

		if ( is_string( $value ) ) {

			if ( is_numeric( $value ) ) {
				// Cast number
				$value = self::isFloat( $value ) ? (float) $value : ( ( strpos( $value, '0' ) !== 0 || strlen( $value ) === 1 ) ? (int) $value : $value );

			} elseif ( 'true' === $value ) {
				// Cast boolean true
				$value = true;

			} elseif ( 'false' === $value ) {
				// Cast boolean false
				$value = false;

			} elseif ( 'null' === $value ) {
				// Cast null
				$value = null;

			} elseif ( '[]' === $value ) {
				// Cast empty array
				$value = array();

			} else {
				// Maybe unserialize
				$value = maybe_unserialize( $value );
			}
		}

		return $value;
	}

	/**
	 * Return prepared implode for SQL in array clause
	 *
	 * @param array $data
	 * @param string $data_type
	 * @return string
	 */
	public static function getSQLImplodesPrepared( array $data, string $data_type = '%d' ) {
		
		global $wpdb;

		foreach ( $data as $index => $value ) {
			$data[ $index ] = $wpdb->prepare( $data_type, $value );
		}
		
		return implode( ', ', array_values( $data ) );
	}

	/**
	 * Consolidate string
	 *
	 * @param string  $input_string Source string
	 * @param boolean $replace_newlines Replace new line flag bool
	 *
	 * @return string
	 */
	public static function consolidate( string $input_string, $replace_newlines = false ) {
		$pattern = $replace_newlines ? '/[\s\t\r\n]+/' : '/[\s\t]+/';
		return preg_replace( $pattern, ' ', trim( $input_string ) );
	}

	/**
	 * Clean base path
	 *
	 * @param string $str The string to purge
	 *
	 * @return string
	 */
	public static function purgeBasePath( $str ) {
		// Remove non-alphanumeric characters (except spaces and hyphens)
		$cleaned_str = preg_replace( '/[^a-zA-Z0-9\s-]/', '', $str );

		// Replace spaces with hyphens
		$cleaned_str = preg_replace( '/\s+/', '-', $cleaned_str );

		// Consolidate multiple hyphens into a single hyphen
		$cleaned_str = preg_replace( '/-+/', '-', $cleaned_str );

		// Return the cleaned string
		return $cleaned_str;
	}
}
