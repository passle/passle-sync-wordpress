<?php
// This file enqueues scripts and styles

defined( 'ABSPATH' ) or exit;

add_action( 'init', function() {

  add_filter( 'script_loader_tag', function( $tag, $handle ) {
    if ( ! preg_match( '/^passle-/', $handle ) ) { return $tag; }
    return str_replace( ' src', ' async defer src', $tag );
  }, 10, 2 );

  add_action( 'admin_enqueue_scripts', function() {
      $asset_manifest = json_decode( file_get_contents( PASSLE_SYNC_ASSET_MANIFEST ), true )['files'];

      if ( isset( $asset_manifest[ 'main.css' ] ) ) {
        wp_enqueue_style( 'passle', get_site_url() . $asset_manifest[ 'main.css' ] );
      }
  if ( isset( $asset_manifest[ 'runtime~main.js' ] ) ) {
    wp_enqueue_script( 'passle-runtime', get_site_url() . $asset_manifest[ 'runtime~main.js' ], array(), null, true );
  }

      wp_enqueue_script( 'passle-main', get_site_url() . $asset_manifest[ 'main.js' ], array(), null, true );

      foreach ( $asset_manifest as $key => $value ) {
        if ( preg_match( '@static/js/(.*)\.chunk\.js@', $key, $matches ) ) {
          if ( $matches && is_array( $matches ) && count( $matches ) === 2 ) {
            $name = "passle-" . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
            wp_enqueue_script( $name, get_site_url() . $value, array( 'passle-main' ), null, true );
          }
        }

        if ( preg_match( '@static/css/(.*)\.chunk\.css@', $key, $matches ) ) {
          if ( $matches && is_array( $matches ) && count( $matches ) == 2 ) {
            $name = "passle-" . preg_replace( '/[^A-Za-z0-9_]/', '-', $matches[1] );
            wp_enqueue_style( $name, get_site_url() . $value, array( 'passle' ), null );
          }
        }
      }
  });
});
