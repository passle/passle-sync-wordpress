<?php

if (defined('PASSLE_SYNC_DEV_MODE') && PASSLE_SYNC_DEV_MODE) {

  if ( !function_exists('write_log')) {
    /**
    * Logs debug messages to a private log file.
    *
    * @param mixed  $data     The data to log.
    * @param string $context  Optional context label.
    */
    function write_log( $data, $context = 'debug' ) {
      $log_dir  = WP_CONTENT_DIR . '/passle-sync-logs';
      $log_file = $log_dir . '/debug.log';
      $htaccess = $log_dir . '/.htaccess';

      // Create log folder if needed
      if (!file_exists( $log_dir )) {
        wp_mkdir_p($log_dir);
      }

      // Add .htaccess for security (Apache)
      if (!file_exists( $htaccess )) {
        file_put_contents( $htaccess, "Deny from all\n" );
      }

      // Format the message
      $message = is_scalar($data) ? $data : wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $entry = sprintf("[%s] [%s] %s\n", gmdate('Y-m-d H:i:s'), $context, $message);

      // Write to log file
      file_put_contents( $log_file, $entry, FILE_APPEND );
    }
  }
}
