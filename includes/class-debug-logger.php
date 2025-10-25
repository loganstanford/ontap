<?php
/**
 * Debug Logger - Stores debug information for troubleshooting
 *
 * @package OnTap
 * @since   1.0.0
 */

namespace OnTap;

/**
 * Debug Logger class
 */
class Debug_Logger {

	/**
	 * Maximum number of log entries to keep
	 *
	 * @var int
	 */
	const MAX_LOGS = 500;

	/**
	 * Option name for storing logs
	 *
	 * @var string
	 */
	const OPTION_NAME = 'ontap_debug_logs';

	/**
	 * Log a debug message
	 *
	 * @param string $message Log message.
	 * @param string $level   Log level (info, warning, error).
	 * @param array  $context Additional context data.
	 * @return void
	 */
	public static function log( $message, $level = 'info', $context = array() ) {
		$logs = get_option( self::OPTION_NAME, array() );

		$entry = array(
			'timestamp' => current_time( 'mysql' ),
			'level'     => $level,
			'message'   => $message,
			'context'   => $context,
		);

		// Add to beginning of array
		array_unshift( $logs, $entry );

		// Limit log size
		if ( count( $logs ) > self::MAX_LOGS ) {
			$logs = array_slice( $logs, 0, self::MAX_LOGS );
		}

		update_option( self::OPTION_NAME, $logs, false );
	}

	/**
	 * Get all log entries
	 *
	 * @param int $limit Number of entries to retrieve.
	 * @return array Log entries
	 */
	public static function get_logs( $limit = 100 ) {
		$logs = get_option( self::OPTION_NAME, array() );

		if ( $limit > 0 && count( $logs ) > $limit ) {
			$logs = array_slice( $logs, 0, $limit );
		}

		return $logs;
	}

	/**
	 * Clear all logs
	 *
	 * @return void
	 */
	public static function clear_logs() {
		delete_option( self::OPTION_NAME );
	}

	/**
	 * Get formatted log output for display
	 *
	 * @param int $limit Number of entries to retrieve.
	 * @return string HTML formatted log output
	 */
	public static function get_formatted_logs( $limit = 100 ) {
		$logs = self::get_logs( $limit );

		if ( empty( $logs ) ) {
			return '<p class="description">' . esc_html__( 'No debug logs available.', 'ontap' ) . '</p>';
		}

		$output = '<div class="ontap-debug-logs" style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 12px;">';

		foreach ( $logs as $log ) {
			$level_class = 'info';
			$level_color = '#0073aa';

			if ( 'warning' === $log['level'] ) {
				$level_class = 'warning';
				$level_color = '#f0b849';
			} elseif ( 'error' === $log['level'] ) {
				$level_class = 'error';
				$level_color = '#dc3232';
			}

			$output .= sprintf(
				'<div style="margin-bottom: 10px; padding: 8px; background: white; border-left: 4px solid %s;">',
				esc_attr( $level_color )
			);

			$output .= sprintf(
				'<strong style="color: %s;">[%s]</strong> <span style="color: #666;">%s</span><br>',
				esc_attr( $level_color ),
				esc_html( strtoupper( $log['level'] ) ),
				esc_html( $log['timestamp'] )
			);

			$output .= '<div style="margin-top: 5px;">' . esc_html( $log['message'] ) . '</div>';

			if ( ! empty( $log['context'] ) ) {
				$output .= '<details style="margin-top: 5px; color: #666;">';
				$output .= '<summary style="cursor: pointer;">Context Data</summary>';
				$output .= '<pre style="margin: 5px 0; padding: 5px; background: #f9f9f9; overflow-x: auto;">';
				$output .= esc_html( print_r( $log['context'], true ) );
				$output .= '</pre>';
				$output .= '</details>';
			}

			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}
}
