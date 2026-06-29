<?php
/**
 * MediaHandler — image upload, validation, paste support, and retrieval.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Media
 */

namespace BePlusAdvancedReviews\Media;

use BePlusAdvancedReviews\Core\AbstractModule;
use BePlusAdvancedReviews\Core\HookManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MediaHandler extends AbstractModule {

	public function register(): void {
		add_action( 'wp_ajax_bpar_upload_media', array( $this, 'handle_ajax_upload' ) );
	}

	/**
	 * Handle file uploads from $_FILES.
	 *
	 * @param int   $comment_id Comment ID.
	 * @param array $files      $_FILES array structure.
	 * @return array Attachment IDs.
	 */
	public function upload_files( int $comment_id, array $files ): array {
		if ( ! beplus_advanced_reviews_is_images_enabled() ) {
			return array();
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$attachment_ids = array();

		if ( empty( $files['name'] ) ) {
			return $attachment_ids;
		}

		$files = $this->normalize_files_array( $files );

		foreach ( $files as $file ) {
			if ( ! empty( $file['error'] ) ) {
				continue;
			}

			$attachment_id = $this->process_upload( $comment_id, $file );
			if ( $attachment_id ) {
				$attachment_ids[] = $attachment_id;
			}
		}

		return $attachment_ids;
	}

	/**
	 * Handle a pasted/base64 image from clipboard.
	 *
	 * @param int    $comment_id Comment ID.
	 * @param string $base64_data Data URL from clipboard paste.
	 * @return int|null Attachment ID or null on failure.
	 */
	public function upload_pasted_image( int $comment_id, string $base64_data ): ?int {
		if ( ! beplus_advanced_reviews_is_paste_enabled() ) {
			return null;
		}

		if ( ! function_exists( 'wp_upload_dir' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		if ( ! preg_match( '/^data:image\/(jpeg|png|webp);base64,/', $base64_data, $matches ) ) {
			return null;
		}

		$extension = $matches[1];
		if ( 'jpeg' === $extension ) {
			$extension = 'jpg';
		}

		$base64_body = substr( $base64_data, strpos( $base64_data, ',' ) + 1 );
		$decoded     = base64_decode( $base64_body ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( ! $decoded ) {
			return null;
		}

		$max_size = beplus_advanced_reviews_get_max_image_size();
		if ( strlen( $decoded ) > $max_size ) {
			return null;
		}

		$upload_dir = wp_upload_dir();
		$filename   = 'paste-' . $comment_id . '-' . wp_generate_password( 8, false ) . '.' . $extension;
		$file_path  = $upload_dir['path'] . '/' . $filename;

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->put_contents( $file_path, $decoded, FS_CHMOD_FILE );

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $file_path,
			'size'     => strlen( $decoded ),
		);

		$check = wp_check_filetype_and_ext( $file_path, $filename );
		if ( ! $check['ext'] || ! $check['type'] ) {
			$wp_filesystem->delete( $file_path );
			return null;
		}

		$allowed_types = array( 'jpg', 'jpeg', 'png', 'webp' );
		if ( ! in_array( $check['ext'], $allowed_types, true ) ) {
			$wp_filesystem->delete( $file_path );
			return null;
		}

		$attachment_id = $this->insert_attachment( $comment_id, $file_array, $file_path );
		return $attachment_id;
	}

	/**
	 * Get media attached to a review.
	 *
	 * @param int $comment_id Comment ID.
	 * @return array List of attachment data.
	 */
	public function get_review_media( int $comment_id ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT attachment_id FROM {$wpdb->prefix}bpar_review_media WHERE comment_id = %d ORDER BY sort_order ASC",
				$comment_id
			)
		);

		if ( empty( $rows ) ) {
			return array();
		}

		$media = array();
		foreach ( $rows as $row ) {
			$attachment_id = (int) $row->attachment_id;
			$url           = wp_get_attachment_url( $attachment_id );
			$thumbnail     = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

			if ( $url ) {
				$media[] = array(
					'id'         => $attachment_id,
					'url'        => $url,
					'thumbnail'  => $thumbnail ?: $url,
				);
			}
		}

		return $media;
	}

	/**
	 * Handle AJAX media upload.
	 *
	 * @return void
	 */
	public function handle_ajax_upload(): void {
		check_ajax_referer( 'wp_rest', '_wpnonce' );

		if ( ! isset( $_POST['comment_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing comment ID.', 'beplus-advanced-reviews' ) ) );
		}

		$comment_id = absint( $_POST['comment_id'] );

		if ( empty( $_FILES ) ) {
			wp_send_json_error( array( 'message' => __( 'No files uploaded.', 'beplus-advanced-reviews' ) ) );
		}

		$attachment_ids = array();
		foreach ( $_FILES as $input_name => $file_data ) {
			if ( ! empty( $file_data['name'] ) ) {
				$result = $this->upload_files( $comment_id, $file_data );
				$attachment_ids = array_merge( $attachment_ids, $result );
			}
		}

		if ( empty( $attachment_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Upload failed.', 'beplus-advanced-reviews' ) ) );
		}

		wp_send_json_success( array(
			'ids'   => $attachment_ids,
			'media' => $this->get_review_media( $comment_id ),
		) );
	}

	/**
	 * Process a single file upload and attach to review.
	 *
	 * @param int   $comment_id Comment ID.
	 * @param array $file       Single file array.
	 * @return int|null Attachment ID.
	 */
	private function process_upload( int $comment_id, array $file ): ?int {
		$max_size     = beplus_advanced_reviews_get_max_image_size();
		$file_size    = (int) ( $file['size'] ?? 0 );

		if ( $file_size > $max_size ) {
			return null;
		}

		$allowed_types = array( 'image/jpeg', 'image/png', 'image/webp' );

		$file_type = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		if ( ! $file_type['type'] || ! in_array( $file_type['type'], $allowed_types, true ) ) {
			return null;
		}

		$override = array(
			'test_form' => false,
			'mimes'     => array(
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
				'webp' => 'image/webp',
			),
		);

		$result = wp_handle_upload( $file, $override );

		if ( isset( $result['error'] ) ) {
			return null;
		}

		return $this->insert_attachment( $comment_id, $file, $result['file'] ?? '' );
	}

	/**
	 * Insert a media attachment and link to the review.
	 *
	 * @param int    $comment_id Comment ID.
	 * @param array  $file       File data array.
	 * @param string $file_path  Actual file path on disk.
	 * @return int|null Attachment ID.
	 */
	private function insert_attachment( int $comment_id, array $file, string $file_path ): ?int {
		global $wpdb;

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => mime_content_type( $file_path ),
				'post_title'     => sanitize_file_name( $file['name'] ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$file_path
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return null;
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		$inserted = $wpdb->insert(
			$wpdb->prefix . 'bpar_review_media',
			array(
				'comment_id'    => $comment_id,
				'attachment_id' => $attachment_id,
				'sort_order'    => 0,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s' )
		);

		if ( false === $inserted ) {
			error_log( 'BePlus Advanced Reviews: Failed to insert review media link. comment_id=' . $comment_id . ' attachment_id=' . $attachment_id . ' error=' . $wpdb->last_error );
		}

		do_action( HookManager::MEDIA_UPLOADED, $comment_id, $attachment_id );

		return $attachment_id;
	}

	/**
	 * Normalize the $_FILES array into a cleaner structure.
	 *
	 * @param array $files $_FILES array.
	 * @return array
	 */
	private function normalize_files_array( array $files ): array {
		$normalized = array();

		if ( ! is_array( $files['name'] ) ) {
			return array( $files );
		}

		foreach ( $files['name'] as $index => $name ) {
			$normalized[] = array(
				'name'     => $name,
				'type'     => $files['type'][ $index ] ?? '',
				'tmp_name' => $files['tmp_name'][ $index ] ?? '',
				'error'    => $files['error'][ $index ] ?? UPLOAD_ERR_NO_FILE,
				'size'     => $files['size'][ $index ] ?? 0,
			);
		}

		return $normalized;
	}
}
