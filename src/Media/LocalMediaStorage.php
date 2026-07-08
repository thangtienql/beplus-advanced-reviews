<?php
/**
 * LocalMediaStorage — WordPress Media Library storage backend.
 *
 * Implements MediaStorageInterface using wp_insert_attachment /
 * wp_delete_attachment. This is the default backend; swap it out
 * via the container binding for cloud storage (Cloudflare R2, S3, etc.).
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage Media
 */

namespace BeplusAdvancedReviewsForWoocommerce\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LocalMediaStorage implements MediaStorageInterface {

	/**
	 * Store a file as a WordPress attachment.
	 *
	 * @param string $file_path Absolute path to the file on disk.
	 * @param string $filename  Original filename.
	 * @return int|\WP_Error Attachment ID on success, WP_Error on failure.
	 */
	public function store( string $file_path, string $filename ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$filetype = wp_check_filetype( basename( $file_path ), null );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_file_name( $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$file_path
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		if ( ! $attachment_id ) {
			return new \WP_Error( 'insert_failed', __( 'Failed to insert attachment.', 'beplus-advanced-reviews-for-woocommerce' ) );
		}

		return (int) $attachment_id;
	}

	/**
	 * Delete a WordPress attachment (post + files on disk).
	 *
	 * @param int|string $storage_id Attachment ID.
	 * @return bool
	 */
	public function delete( $storage_id ): bool {
		$attachment_id = (int) $storage_id;
		if ( $attachment_id < 1 ) {
			return false;
		}

		$deleted = wp_delete_attachment( $attachment_id, true );
		return (bool) $deleted;
	}

	/**
	 * Get the public URL of an attachment.
	 *
	 * @param int|string $storage_id Attachment ID.
	 * @return string|null
	 */
	public function get_url( $storage_id ): ?string {
		$url = wp_get_attachment_url( (int) $storage_id );
		return $url ?: null;
	}

	/**
	 * Get a thumbnail URL for an attachment.
	 *
	 * @param int|string $storage_id Attachment ID.
	 * @param string     $size       Thumbnail size slug.
	 * @return string|null
	 */
	public function get_thumbnail_url( $storage_id, string $size = 'thumbnail' ): ?string {
		$url = wp_get_attachment_image_url( (int) $storage_id, $size );
		return $url ?: null;
	}

	/**
	 * Get the MIME type of an attachment.
	 *
	 * @param int|string $storage_id Attachment ID.
	 * @return string|null
	 */
	public function get_mime_type( $storage_id ): ?string {
		$mime = get_post_mime_type( (int) $storage_id );
		return $mime ?: null;
	}

	/**
	 * Generate attachment metadata (thumbnails, sub-sizes).
	 *
	 * @param int|string $storage_id Attachment ID.
	 * @param string     $file_path  Absolute path to the file on disk.
	 * @return void
	 */
	public function generate_metadata( $storage_id, string $file_path ): void {
		$attachment_id = (int) $storage_id;
		$mime_type     = get_post_mime_type( $attachment_id );

		if ( $mime_type && str_starts_with( $mime_type, 'image/' ) ) {
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			ob_start();
			$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
			wp_update_attachment_metadata( $attachment_id, $metadata );
			ob_end_clean();
		}
	}
}
