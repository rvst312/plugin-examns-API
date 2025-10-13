<?php

/**
 * Plugin Name:       Exercises & Exams API
 * Plugin URI:        https://versatile-handbook-314758.framer.app/page
 * Description:       This plugin provides a simple way to integrate exercises and exams into your WordPress site.
 * Version:           2.0.0
 * Author:            Aarón RV
 * Author URI:        https://www.linkedin.com/in/aar%C3%B3n-franco-fern%C3%A1ndez/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       examens-api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include necessary files (Check existence before requiring)
$includes = [
    'includes/functions.php',
    'includes/class-api-handler.php',
    'views/main-view.php',
    'views/results.php',
    'views/filters.php',
    'views/pagination.php',
    'views/exercise_view.php',
    'views/listing_view.php',
    'views/button-seo.php',
    'views/search.php',
];

foreach ($includes as $file) {
    $filepath = plugin_dir_path(__FILE__) . $file;
    if (file_exists($filepath)) {
        require_once $filepath;
    }
}

// Enqueue assets
function enqueue_assets()
{
    wp_enqueue_style('examens-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('examens-script', plugin_dir_url(__FILE__) . 'assets/js/scripts.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_assets');

// Rewrite URLs for friendly links
function add_exercise_rewrite_rules()
{
    // Regla para la página "exercici" con un ID
    add_rewrite_rule('^exercicis-selectivitat/exercici/([a-zA-Z0-9-_]+)/?$', 'index.php?pagename=exercici&id=$matches[1]', 'top');

    // Regla para la página "solucio" con un ID
    add_rewrite_rule('^exercicis-selectivitat/solucio/([a-zA-Z0-9-_]+)/?$', 'index.php?pagename=solucio&id=$matches[1]', 'top');

    // Regla para la página "exercicis" y un año
    add_rewrite_rule('^exercicis-selectivitat/any/([0-9]{4})/?$', 'index.php?pagename=any&year=$matches[1]', 'top');

    // Regla para la página "exercicis" y un "subject"
    add_rewrite_rule('^exercicis-selectivitat/assignatura/([^/-][^/]*?)/?$', 'index.php?pagename=assignatura&subject=$matches[1]', 'top');

}
add_action('init', 'add_exercise_rewrite_rules');

function add_exam_rewrite_rules()
{
    // Regla para la página "examenes" con un ID
    add_rewrite_rule('^examenes/([a-zA-Z0-9-_]+)/?$', 'index.php?pagename=examenes&id=$matches[1]', 'top');

    // Regla para la página "solucio" con un ID
    add_rewrite_rule('^examenes/solucio/([a-zA-Z0-9-_]+)/?$', 'index.php?pagename=examenes&id=$matches[1]', 'top');

    // Regla para la página "examens-de-selectivitat" y un "subject"
    add_rewrite_rule('^examens-de-selectivitat/asignatura/([^/-][^/]*?)/?$', 'index.php?pagename=asignatura&subject=$matches[1]', 'top');
	
    // Regla para la página "examens-de-selectivitat" y un año
    add_rewrite_rule('^examens-de-selectivitat/year/([a-zA-Z0-9-_]+)/?$', 'index.php?pagename=year&year=$matches[1]', 'top');
}
add_action('init', 'add_exam_rewrite_rules');

// Register query variables
function register_exercise_query_vars($vars)
{
    $vars[] = 'id';
    $vars[] = 'year';
    $vars[] = 'subject';
    return $vars;
}
add_filter('query_vars', 'register_exercise_query_vars');

// Clean subject parameter before use
function clean_subject_parameter($value)
{
    if (get_query_var('pagename') === 'exercicis' && get_query_var('subject')) {
        return str_replace('-', ' ', urldecode($value));
    }
    else if (get_query_var('pagename') === 'examens' && get_query_var('subject')) {
        return str_replace('-', ' ', urldecode($value));
    }
    return $value;
}
add_filter('request', function ($vars) {
    if (isset($vars['subject'])) {
        $vars['subject'] = clean_subject_parameter($vars['subject']);
    }
    return $vars;
});

// Flush rewrite rules on activation/deactivation
function flush_rewrite_rules_on_activation()
{
    add_exercise_rewrite_rules();
    add_exam_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'flush_rewrite_rules_on_activation');

function flush_rewrite_rules_on_deactivation()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'flush_rewrite_rules_on_deactivation');