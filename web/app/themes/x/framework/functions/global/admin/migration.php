<?php

// =============================================================================
// FUNCTIONS/GLOBAL/ADMIN/MIGRATION.PHP
// -----------------------------------------------------------------------------
// Handles theme migration.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Version Migration
//   02. Pairing Notice
//   03. Version Migration Notice
//   04. Theme Migration
// =============================================================================

// Version Migration
// =============================================================================

function x_version_migration() {

  $prior = get_option( 'x_version', '1.0.0' );

  if ( version_compare( $prior, X_VERSION, '<' ) ) {

    //
    // If $prior is less than 2.2.0.
    //

    if ( version_compare( $prior, '2.2.0', '<' ) ) {

      $mods = get_theme_mods();

      foreach( $mods as $key => $value ) {
        update_option( $key, $value );
      }

    }


    //
    // If $prior is less than 3.1.0.
    //

    if ( version_compare( $prior, '3.1.0', '<' ) ) {

      $stack      = get_option( 'x_stack' );
      $design     = ( $stack == 'integrity' ) ? '_' . get_option( 'x_integrity_design' ) : '';
      $stack_safe = ( $stack == 'icon' ) ? 'integrity' : $stack;

      $updated = array(
        'x_layout_site'               => get_option( 'x_' . $stack . '_layout_site' ),
        'x_layout_site_max_width'     => get_option( 'x_' . $stack . '_sizing_site_max_width' ),
        'x_layout_site_width'         => get_option( 'x_' . $stack . '_sizing_site_width' ),
        'x_layout_content'            => get_option( 'x_' . $stack . '_layout_content' ),
        'x_layout_content_width'      => get_option( 'x_' . $stack_safe . '_sizing_content_width' ),
        'x_layout_sidebar_width'      => get_option( 'x_icon_sidebar_width' ),
        'x_design_bg_color'           => get_option( 'x_' . $stack . $design . '_bg_color' ),
        'x_design_bg_image_pattern'   => get_option( 'x_' . $stack . $design . '_bg_image_pattern' ),
        'x_design_bg_image_full'      => get_option( 'x_' . $stack . $design . '_bg_image_full' ),
        'x_design_bg_image_full_fade' => get_option( 'x_' . $stack . $design . '_bg_image_full_fade' )
      );

      foreach ( $updated as $key => $value ) {
        update_option( $key, $value );
      }

    }


    //
    // Update stored version number.
    //

    update_option( 'x_version', X_VERSION );


    //
    // Turn on the version migration notice.
    //

    update_option( 'x_version_migration_notice', true );

  }

}

add_action( 'admin_init', 'x_version_migration' );



// Pairing Notice
// =============================================================================

//
// Define current version of plugin and prompt for plugin update if:
//
// 1. Plugin doesn't specify current theme constant (i.e. is too old).
// 2. Plugin is older than what the theme desires it to be
//

define( 'X_SHORTCODES_CURRENT', '3.0.5' );

function x_pairing_notice() {

  if ( x_plugin_shortcodes_exists() ) {
    if ( ! defined( 'X_CURRENT' ) || version_compare( X_SHORTCODES_VERSION, X_SHORTCODES_CURRENT, '<' ) ) { ?>

      <div class="updated x-notice warning">
        <p><strong>IMPORTANT: Please update X &ndash; Shortcodes</strong>. You are using a newer version of X that may not be compatible. After updating, please ensure that you have cleared out your browser cache and any caching plugins you may be using. This message will self destruct upon updating X &ndash; Shortcodes.</p>
      </div>

    <?php }
  }

}

add_action( 'admin_notices', 'x_pairing_notice' );



// Version Migration Notice
// =============================================================================

//
// 1. Output notice.
// 2. Dismiss notice.
//

function x_version_migration_notice() { // 1

  if ( get_option( 'x_version_migration_notice' ) == true ) { ?>

    <div class="updated x-notice dismissible">
      <a href="<?php echo add_query_arg( array( 'x-dismiss-notice' => true ) ); ?>" class="dismiss"><span class="dashicons dashicons-no"></span></a>
      <p>Congratulations, you've successfully updated X! Be sure to <a href="//theme.co/x/changelog/" target="_blank">check out the release notes and changelog</a> for this latest version to see all that has changed, especially if you're utilizing any additional plugins or have made modifications to your website via a child theme.</p>
    </div>

  <?php }

}

add_action( 'admin_notices', 'x_version_migration_notice' );


function x_version_migration_notice_dismiss() { // 2

  if ( isset( $_GET['x-dismiss-notice'] ) ) {
    update_option( 'x_version_migration_notice', false );
  }

}

add_action( 'admin_init', 'x_version_migration_notice_dismiss' );



// Theme Migration
// =============================================================================

function x_theme_migration( $new_name, $new_theme ) {

  if ( $new_theme == 'X' || $new_theme->get( 'Template' ) == 'x' ) {
    return false;
  }

  include_once( ABSPATH . '/wp-admin/includes/plugin.php' );

  $plugins   = get_plugins();
  $x_plugins = array();

  foreach ( (array) $plugins as $plugin => $headers ) {
    if ( ! empty( $headers['X Plugin'] ) && $headers['X Plugin'] != 'x-shortcodes' ) {
      $x_plugins[] = $plugin;
    }
  }

  deactivate_plugins( $x_plugins );

}

add_action( 'switch_theme', 'x_theme_migration', 10, 2 );