<?php
/**
 * URL path prefix list parsing.
 *
 * @package HeadwallPageConditions
 */

namespace Headwall_Page_Conditions;

defined( 'ABSPATH' ) || die();

/**
 * Parses the list of URL path prefixes used by the URL path condition. Each
 * prefix is normalised to "/segment/.../" and matches whole path segments, so
 * "/docs" covers "/docs/" and "/docs/installation/" but not "/docsearch/".
 * The format is described in docs/developers/how-it-works.md.
 */
class Path_List {

	/**
	 * Normalise a user-entered prefix list, dropping anything unusable and any duplicates.
	 *
	 * @param string $raw_list  Raw prefix list, e.g. "/docs/, /guides".
	 * @param string $home_path Path of the site's home URL, stripped from pasted full URLs.
	 *
	 * @return string The normalised prefixes, comma separated.
	 */
	public static function sanitise( string $raw_list, string $home_path = '/' ): string {
		$prefixes      = array();
		$lowercase_set = array();

		foreach ( self::split( $raw_list ) as $segment ) {
			$prefix = self::normalise( $segment, $home_path );

			if ( '' !== $prefix && ! in_array( self::fold_case( $prefix ), $lowercase_set, true ) ) {
				$prefixes[]      = $prefix;
				$lowercase_set[] = self::fold_case( $prefix );
			}
		}

		return implode( ', ', $prefixes );
	}

	/**
	 * Whether a request path starts with any prefix in a list, ignoring case.
	 *
	 * @param string $request_path The decoded path being viewed, relative to the home URL.
	 * @param string $raw_list     Raw or sanitised prefix list.
	 *
	 * @return bool
	 */
	public static function matches( string $request_path, string $raw_list ): bool {
		$is_matched = false;
		$directory  = self::fold_case( self::to_directory_path( $request_path ) );

		foreach ( self::split( $raw_list ) as $segment ) {
			$prefix = self::normalise( $segment, '/' );

			if ( '' !== $prefix && str_starts_with( $directory, self::fold_case( $prefix ) ) ) {
				$is_matched = true;
				break;
			}
		}

		return $is_matched;
	}

	/**
	 * Split a raw list on commas and whitespace into non-empty segments.
	 *
	 * @param string $raw_list Raw prefix list.
	 *
	 * @return string[] The segments, in the order they were written.
	 */
	private static function split( string $raw_list ): array {
		// Query strings and fragments go first, so a comma inside one can't start a new entry.
		$without_queries = (string) preg_replace( '/[?#]\S*/', '', $raw_list );

		return array_values( array_filter( preg_split( '/[\s,]+/', $without_queries ), 'strlen' ) );
	}

	/**
	 * Lowercase a path, including non-ASCII letters where mbstring is available.
	 *
	 * @param string $path A URL path.
	 *
	 * @return string
	 */
	private static function fold_case( string $path ): string {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $path, 'UTF-8' ) : strtolower( $path );
	}

	/**
	 * Turn one segment into a "/segment/.../" prefix, or an empty string if it is unusable.
	 *
	 * @param string $segment   A single entry: a path, or a full URL on this site.
	 * @param string $home_path Path of the site's home URL.
	 *
	 * @return string
	 */
	private static function normalise( string $segment, string $home_path ): string {
		$path = $segment;

		if ( 1 === preg_match( '#^https?://#i', $path ) ) {
			$path      = (string) wp_parse_url( $path, PHP_URL_PATH );
			$home_root = self::fold_case( self::to_directory_path( $home_path ) );

			// A full URL carries the home path of a subdirectory install; stored prefixes are relative to it.
			if ( '/' !== $home_root && str_starts_with( self::fold_case( self::to_directory_path( $path ) ), $home_root ) ) {
				$path = substr( self::to_directory_path( $path ), strlen( $home_root ) - 1 );
			}
		}

		$path      = (string) preg_replace( '/[?#].*$/s', '', $path );
		$is_usable = '' !== $path && 1 !== preg_match( '/[<>"\'`\\\\\x00-\x1f\x7f]/', $path );

		return $is_usable ? self::to_directory_path( $path ) : '';
	}

	/**
	 * Collapse repeated slashes and wrap a path in single leading and trailing slashes.
	 *
	 * @param string $path A URL path.
	 *
	 * @return string The path as "/a/b/", or "/" for the root.
	 */
	private static function to_directory_path( string $path ): string {
		$trimmed = trim( (string) preg_replace( '#/+#', '/', $path ), '/' );

		return '' === $trimmed ? '/' : '/' . $trimmed . '/';
	}
}
