<?php

// Register settings, sections and fields
function examens_register_settings() {
    // Register options with sanitization
    register_setting('examens_settings', 'examens_config_json', [
        'type' => 'string',
        'sanitize_callback' => 'examens_sanitize_path',
        'default' => ''
    ]);

    register_setting('examens_settings', 'examens_config_base_dir', [
        'type' => 'string',
        'sanitize_callback' => 'examens_sanitize_path',
        'default' => ''
    ]);

    // Main section
    add_settings_section(
        'examens_main_section',
        __('Configuración de Exámenes & Ejercicios', 'examens-api'),
        function () {
            echo '<p>' . esc_html__('Configura la ruta del archivo JSON de asignaturas/temáticas. Estas opciones se usan si no has definido las constantes en wp-config.php.', 'examens-api') . '</p>';
        },
        'examens-settings'
    );

    // JSON file path field
    add_settings_field(
        'examens_config_json',
        __('Ruta del archivo JSON', 'examens-api'),
        'examens_render_json_path_field',
        'examens-settings',
        'examens_main_section'
    );

    // Base dir field
    add_settings_field(
        'examens_config_base_dir',
        __('Directorio base permitido', 'examens-api'),
        'examens_render_base_dir_field',
        'examens-settings',
        'examens_main_section'
    );
}
add_action('admin_init', 'examens_register_settings');

// Add menu page
function examens_add_admin_menu() {
    add_menu_page(
        __('Exàmens API', 'examens-api'),
        __('Exàmens API', 'examens-api'),
        'manage_options',
        'examens-settings',
        'examens_render_settings_page',
        'dashicons-welcome-learn-more',
        80
    );
}
add_action('admin_menu', 'examens_add_admin_menu');

// Render input fields
function examens_render_json_path_field() {
    $value = get_option('examens_config_json', '');
    echo '<input type="text" name="examens_config_json" value="' . esc_attr($value) . '" class="regular-text" placeholder="/ruta/absoluta/configuracio_assignatures.json" />';
    echo '<p class="description">' . esc_html__('Ruta absoluta del archivo JSON. Si está vacía, se usará la ruta por defecto en uploads.', 'examens-api') . '</p>';
}

function examens_render_base_dir_field() {
    $value = get_option('examens_config_base_dir', '');
    echo '<input type="text" name="examens_config_base_dir" value="' . esc_attr($value) . '" class="regular-text" placeholder="/ruta/absoluta/directorio" />';
    echo '<p class="description">' . esc_html__('Directorio base permitido. El JSON debe estar dentro de esta carpeta para ser válido.', 'examens-api') . '</p>';
}

// Sanitize callback
function examens_sanitize_path($path) {
    $path = trim((string) $path);
    // Evitar inyecciones básicas
    $path = str_replace(["\0", "\n", "\r"], '', $path);
    // No permitir rutas relativas con ..
    if (strpos($path, '..') !== false) {
        return '';
    }
    return $path;
}

// Render settings page
function examens_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Ajustes de Exàmens API', 'examens-api') . '</h1>';
    echo '<form action="' . esc_url(admin_url('options.php')) . '" method="post">';
    settings_fields('examens_settings');
    do_settings_sections('examens-settings');
    submit_button();
    echo '</form>';

    // Estado actual y validación informativa
    echo '<hr />';
    echo '<h2>' . esc_html__('Estado de configuración', 'examens-api') . '</h2>';
    $resolved = function_exists('get_examens_config_json_path') ? get_examens_config_json_path() : false;
    if ($resolved) {
        echo '<p>' . esc_html__('JSON resuelto:', 'examens-api') . ' <code>' . esc_html($resolved) . '</code></p>';
        echo '<p>' . esc_html__('Lectura válida y dentro del directorio permitido.', 'examens-api') . '</p>';
    } else {
        echo '<p style="color:#b32d2e">' . esc_html__('No se pudo resolver un JSON válido. Revisa las rutas o usa la ubicación por defecto en uploads.', 'examens-api') . '</p>';
    }
    echo '</div>';
}