<?php
declare(strict_types=1);
namespace MediaManager\Security;

use MediaManager\Data\IpRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IpBlocker
 *
 * Phase 12 — Hooks into `init` to check the visitor's IP against the
 * mm_blocked_ips table and returns a 403 if blocked.
 *
 * Does NOT run in wp-admin. Only active when `mm_ip_blocking_enabled` = '1'.
 */
final class IpBlocker {

	public function register( \MediaManager\Core\Loader $loader ): void {
		$loader->add_action( 'init', $this, 'maybe_block', 1 );
	}

	// -----------------------------------------------------------------------
	// Runtime
	// -----------------------------------------------------------------------

	/**
	 * Check whether the current visitor should be blocked.
	 * Called on `init` at priority 1.
	 */
	public function maybe_block(): void {
		if ( is_admin() ) {
			return;
		}

		if ( get_option( 'mm_ip_blocking_enabled', '0' ) !== '1' ) {
			return;
		}

		$ip = $this->get_client_ip();

		if ( ! $ip ) {
			return;
		}

		if ( IpRepository::is_blocked( $ip ) ) {
			wp_die(
				esc_html__( 'Access denied.', 'media-manager' ),
				esc_html__( 'Forbidden', 'media-manager' ),
				[ 'response' => 403 ]
			);
		}
	}

	// -----------------------------------------------------------------------
	// Admin helpers (called from AjaxHandler)
	// -----------------------------------------------------------------------

	/**
	 * Add an IP to the block list.
	 *
	 * @return true|\WP_Error
	 */
	public function block_ip( string $ip ): bool|\WP_Error {
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return new \WP_Error( 'mm_invalid_ip', __( 'Invalid IP address.', 'media-manager' ) );
		}

		$result = IpRepository::insert( $ip );

		if ( ! $result ) {
			return new \WP_Error( 'mm_ip_exists', __( 'IP address is already blocked.', 'media-manager' ) );
		}

		return true;
	}

	/**
	 * Remove an IP from the block list by ID.
	 *
	 * @return true|\WP_Error
	 */
	public function unblock_ip( int $id ): bool|\WP_Error {
		IpRepository::delete( $id );
		return true;
	}

	/**
	 * Return all blocked IPs.
	 *
	 * @return object[]
	 */
	public function get_all(): array {
		return IpRepository::get_all();
	}

	// -----------------------------------------------------------------------
	// Private
	// -----------------------------------------------------------------------

	private function get_client_ip(): string {
		$headers = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		];

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = trim( explode( ',', $_SERVER[ $header ] )[0] );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '';
	}
}
