<?php

/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://formaciomiro.com
 * @since             1.0.0
 * @package           Examens API
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Plugin Boilerplate Tutorial
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Aarón RV
 * Author URI:        https://www.linkedin.com/in/aar%C3%B3n-franco-fern%C3%A1ndez/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       Examens API
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_NAME_PLUGIN_NAME', 'Exámenes y ejercícios' );
define( 'PLUGIN_NAME_VERSION', '1.0.0' );
define( 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_NAME_PATH', plugin_dir_path( __FILE__ ) );

require_once 'wp-load.php'; 
require_once 'includes/functions.php';
require_once 'includes/class-api-handler.php'; 

// Enqueue scripts and styles
function enqueue_assets() {
    wp_enqueue_style('examens-style', plugins_url('assets/css/style.css', __FILE__));
    wp_enqueue_script('examens-script', plugins_url('assets/js/script.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_examens_assets');

