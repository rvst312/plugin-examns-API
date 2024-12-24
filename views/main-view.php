<?php
function mostrar_examenes() {
    $data = get_exams_data();

    if (isset($data['error'])) {
        return '<p>Error fetching data: ' . esc_html($data['error']) . '</p>';
    } else {
        $output = '';
        foreach ($data as $item) {
            $output .= '<div>';
            $output .= '<h3>' . esc_html($item['descripcio']) . '</h3>';
            $output .= '<img src="' . esc_url($item['url_miniatura']) . '" alt="Thumbnail">';
            $output .= '<a href="' . esc_url($item['url_pregunta']) . '">View Question</a>';
            $output .= '</div>';
        }
        return $output;
    }
}
add_shortcode('examenes', 'mostrar_examenes');