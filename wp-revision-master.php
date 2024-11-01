<?php
/**
 * Main plugin file
 *
 * @package     WPRevisionMasterPlugin
 * @author      Md. Hasan Shahriar <info@themeaxe.com>
 * @since       1.0.2
 */

/**
Plugin Name: WP Revision Master
Plugin URI: http://themeaxe.com
Description: Controls post revision in WordPress
Author: Md. Hasan Shahriar
Version: 1.0.2
Author URI: http://github.com/hsleonis
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}

// Used for tracking the version used.
defined( 'TMXRM_VERSION' ) or define( 'TMXRM_VERSION', '1.0.2' );
// Used for text domains.
defined( 'TMXRM_I18NDOMAIN' ) or define( 'TMXRM_I18NDOMAIN', 'wp-revision-master' );
// Used for general naming, e.g. nonces.
defined( 'TMXRM' ) or define( 'TMXRM', 'wp-revision-master' );
// Used for general naming.
defined( 'TMXRM_NAME' ) or define( 'TMXRM_NAME', 'WP Revision Master' );
// Used for file includes.
defined( 'TMXRM_PATH' ) or define( 'TMXRM_PATH', plugin_dir_path( __FILE__ ) );
// Used for file uri
defined('TMXRM_URI') or define( 'TMXRM_URI', trailingslashit( plugins_url('',__FILE__)) );
// Used for testing and checking plugin slug name.
defined( 'TMXRM_PLUGIN_BASENAME' ) or define( 'TMXRM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require the main plugin class
require_once( TMXRM_PATH . 'Themeaxe/class.WPRevisionMasterPlugin.php');

// Instance
\Themeaxe\WPRevisionMasterPlugin::instance();

// Register activation hook
register_activation_hook( __FILE__, array( 'Themeaxe\WPRevisionMasterPlugin', 'activate' ) );