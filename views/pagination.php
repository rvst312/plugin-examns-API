<?php

/**
 * Generates pagination links for a list of items.
 * Shows only the first 3 pages and adds ellipsis if there are more pages.
 *
 * @param int $total_items Total number of items to paginate.
 * @param int $items_per_page Number of items to show per page.
 * @param array $current_params Current filter parameters to maintain in pagination links.
 * @return string HTML formatted pagination links.
 */
function view_pagination($total_items, $items_per_page, $current_params = []){
    $total_pages = ceil($total_items / $items_per_page); // Calculate total pages
    
    // Don't show pagination if there's only one page
    if ($total_pages <= 1) {
        return ''; 
    }

    // Get current page and validate its range
    $current_page = isset($_GET['pagina']) ? max(1, min($total_pages, intval($_GET['pagina']))) : 1;
    
    // Build query string from current parameters
    $query_params = $current_params;
    $query_string = '';
    if (!empty($query_params)) {
        $query_string = http_build_query($query_params);
    }

    $pagination_links = '<div class="pagination">';

    // Previous button - show if not on first page
    if ($current_page > 1) {
        $prev_url = '?' . ($query_string ? $query_string . '&' : '') . 'pagina=' . ($current_page - 1);
        $pagination_links .= '<a href="' . esc_url($prev_url) . '" class="prev-page">&laquo; Previous</a> ';
    }

    // Show only first 3 pages
    for ($i = 1; $i <= min(3, $total_pages); $i++) {
        $url = '?' . ($query_string ? $query_string . '&' : '') . 'pagina=' . $i;
        $active_class = ($i === $current_page) ? ' active' : '';
        $pagination_links .= '<a href="' . esc_url($url) . '" class="page-number' . $active_class . '">' . $i . '</a> ';
    }

    // Add ellipsis if there are more pages after page 3
    if ($total_pages > 3) {
        $pagination_links .= '<span class="pagination-dots">...</span>';
    }

    // Next button - show if not on last page and within first 3 pages
    if ($current_page < min(3, $total_pages)) {
        $next_url = '?' . ($query_string ? $query_string . '&' : '') . 'pagina=' . ($current_page + 1);
        $pagination_links .= '<a href="' . esc_url($next_url) . '" class="next-page">Next &raquo;</a>';
    }

    $pagination_links .= '</div>';
    
    return $pagination_links;
}