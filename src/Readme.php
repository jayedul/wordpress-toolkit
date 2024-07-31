<?php
/**
 * Color pallete
 *
 * @package solidie
 */

namespace SolidieLib;

class Readme {

	private $path;

	public function __construct( string $path ) {
		$this->path = $path;
	}

	/**
	 * Get latest changelog from readme.txt file.
	 *
	 * @return array
	 */
	public function getLatestChangelog() {

		$changelog = file_get_contents( $this->path );
		$changelog = trim( end( explode( '== Changelog ==', $changelog ) ) );
		$changelog = explode( PHP_EOL . PHP_EOL, $changelog )[0];
		$lines     = array_slice( explode( PHP_EOL, $changelog ), 2 );
		$lines     = array_map(
			function ( $line ) {
				return trim( trim( $line, '*' ) );
			},
			$lines
		);

		return array_filter( $lines );
	}
}