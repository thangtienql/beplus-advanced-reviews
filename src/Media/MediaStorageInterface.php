<?php
/**
 * MediaStorageInterface — contract for media storage backends.
 *
 * Implement this interface to swap the storage layer (e.g. local WP Media Library,
 * Cloudflare R2, AWS S3, etc.) without touching review logic.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage Media
 */

namespace BeplusAdvancedReviewsForWoocommerce\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MediaStorageInterface {

	/**
	 * Store a file and return a unique storage identifier.
	 *
	 * For local storage this is a WP attachment ID (int).
	 * For cloud storage it could be an object key (string).
	 *
	 * @param string $file_path Absolute path to the temp file on disk.
	 * @param string $filename  Original filename (for MIME detection / naming).
	 * @return int|\WP_Error Storage ID on success, WP_Error on failure.
	 */
	public function store( string $file_path, string $filename );

	/**
	 * Delete a stored file by its storage identifier.
	 *
	 * @param int|string $storage_id The ID returned by store().
	 * @return bool True on success.
	 */
	public function delete( $storage_id ): bool;

	/**
	 * Get the public URL for a stored file.
	 *
	 * @param int|string $storage_id The ID returned by store().
	 * @return string|null URL or null if not found.
	 */
	public function get_url( $storage_id ): ?string;

	/**
	 * Get a thumbnail URL for a stored file.
	 *
	 * @param int|string $storage_id The ID returned by store().
	 * @param string     $size       Thumbnail size slug (e.g. 'thumbnail', 'medium').
	 * @return string|null URL or null if not available.
	 */
	public function get_thumbnail_url( $storage_id, string $size = 'thumbnail' ): ?string;

	/**
	 * Get the MIME type of a stored file.
	 *
	 * @param int|string $storage_id The ID returned by store().
	 * @return string|null MIME type or null.
	 */
	public function get_mime_type( $storage_id ): ?string;

	/**
	 * Generate metadata (thumbnails, sub-sizes) for a stored file.
	 *
	 * Called after store() for image files. Cloud backends that handle
	 * processing externally can no-op this.
	 *
	 * @param int|string $storage_id The ID returned by store().
	 * @param string     $file_path  Absolute path to the file on disk.
	 * @return void
	 */
	public function generate_metadata( $storage_id, string $file_path ): void;
}
