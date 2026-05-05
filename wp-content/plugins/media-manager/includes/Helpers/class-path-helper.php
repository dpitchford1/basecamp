<?php
declare(strict_types=1);
namespace MediaManager\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PathHelper
 *
 * Utility methods for converting between server paths and URLs, and for
 * validating that a child path is safely contained within a parent path
 * (path-traversal guard).
 */
final class PathHelper {

	/** @var array|null Cached result of wp_upload_dir(). */
	private static ?array $upload_dir = null;

	// -----------------------------------------------------------------------
	// Upload dir (cached)
	// -----------------------------------------------------------------------

	public static function upload_dir(): array {
		if ( null === self::$upload_dir ) {
			self::$upload_dir = wp_upload_dir();
		}
		return self::$upload_dir;
	}

	public static function upload_basedir(): string {
		return rtrim( self::upload_dir()['basedir'], DIRECTORY_SEPARATOR );
	}

	public static function upload_baseurl(): string {
		return rtrim( self::upload_dir()['baseurl'], '/' );
	}

	// -----------------------------------------------------------------------
	// Conversion
	// -----------------------------------------------------------------------

	/**
	 * Convert an absolute server path to a site URL.
	 * Returns empty string if the path is not inside the uploads directory.
	 */
	public static function path_to_url( string $path ): string {
		$basedir = self::upload_basedir();
		$baseurl = self::upload_baseurl();

		$path = wp_normalize_path( $path );

		if ( str_starts_with( $path, $basedir ) ) {
			return $baseurl . substr( $path, strlen( $basedir ) );
		}

		return '';
	}

	/**
	 * Convert a URL to an absolute server path.
	 * Returns empty string if the URL is not within the uploads base URL.
	 */
	public static function url_to_path( string $url ): string {
		$basedir = self::upload_basedir();
		$baseurl = self::upload_baseurl();

		// Strip query strings / fragments.
		$url = strtok( $url, '?' );

		if ( str_starts_with( $url, $baseurl ) ) {
			return $basedir . substr( $url, strlen( $baseurl ) );
		}

		return '';
	}

	/**
	 * Convert a relative upload path (as stored in _wp_attached_file meta)
	 * to an absolute server path.
	 */
	public static function relative_to_path( string $relative ): string {
		return self::upload_basedir() . DIRECTORY_SEPARATOR . ltrim( $relative, '/' );
	}

	/**
	 * Given an absolute file path, return the absolute path of its parent
	 * directory.
	 */
	public static function parent_dir( string $path ): string {
		return dirname( wp_normalize_path( $path ) );
	}

	// -----------------------------------------------------------------------
	// Security guard
	// -----------------------------------------------------------------------

	/**
	 * Returns true if $child is inside $parent (both are absolute paths).
	 * Resolves symlinks via realpath(). Returns false if either path does
	 * not exist on disk.
	 *
	 * Use this before any operation that writes to or reads from a
	 * user-supplied path to prevent path-traversal attacks.
	 */
	public static function is_path_inside( string $parent, string $child ): bool {
		$real_parent = realpath( $parent );
		$real_child  = realpath( $child );

		if ( false === $real_parent || false === $real_child ) {
			return false;
		}

		// Ensure trailing separator on parent so /uploads-extra doesn't match /uploads.
		$real_parent = rtrim( $real_parent, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		return str_starts_with( $real_child . DIRECTORY_SEPARATOR, $real_parent );
	}
}
