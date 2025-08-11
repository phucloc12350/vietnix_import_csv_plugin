<?php
/**
 * Helper functions cho Vietnix CSV Import plugin
 */

// Ngăn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lấy dữ liệu từ database
 */
function vietnix_csv_get_data($args = array())
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'vietnix_price_data';


    // Build final query
    $query = "SELECT * FROM {$table_name} ";

    if ($args['limit'] > 0) {
        $query .= " LIMIT %d";
        $query_params[] = $args['limit'];

        if ($args['offset'] > 0) {
            $query .= " OFFSET %d";
            $query_params[] = $args['offset'];
        }
    }

    return $wpdb->get_results($wpdb->prepare($query, $query_params));
}

/**
 * Đếm tổng số records
 */
function vietnix_csv_count_data($args = array())
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'vietnix_price_data';

    $defaults = array(
        'search' => '',
        'category' => '',
        'status' => 'active'
    );

    $args = wp_parse_args($args, $defaults);

    $where_conditions = array("status = %s");
    $query_params = array($args['status']);

    if (!empty($args['search'])) {
        $where_conditions[] = "(product_name LIKE %s OR description LIKE %s OR category LIKE %s OR sku LIKE %s)";
        $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
        $query_params = array_merge($query_params, array($search_term, $search_term, $search_term, $search_term));
    }

    if (!empty($args['category'])) {
        $where_conditions[] = "category = %s";
        $query_params[] = $args['category'];
    }

    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

    $query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";

    return $wpdb->get_var($wpdb->prepare($query, $query_params));
}

/**
 * Format giá tiền
 */
function vietnix_csv_format_price($price, $currency = 'VND')
{
    $price = floatval($price);

    switch (strtoupper($currency)) {
        case 'VND':
            return number_format($price, 0, ',', '.') . ' ₫';
        case 'USD':
            return '$' . number_format($price, 2);
        case 'EUR':
            return '€' . number_format($price, 2);
        case 'JPY':
            return '¥' . number_format($price, 0);
        case 'GBP':
            return '£' . number_format($price, 2);
        default:
            return number_format($price, 2) . ' ' . $currency;
    }
}

/**
 * Lấy settings của plugin
 */
function vietnix_csv_get_settings()
{
    $defaults = array(
        'show_pagination' => true,
        'items_per_page' => 10,
        'enable_search' => true,
        'table_style' => 'default'
    );

    $settings = get_option('vietnix_csv_table_settings', array());

    return wp_parse_args($settings, $defaults);
}

/**
 * Cập nhật settings
 */
function vietnix_csv_update_settings($settings)
{
    return update_option('vietnix_csv_table_settings', $settings);
}

/**
 * Validate CSV file
 */
function vietnix_csv_validate_file($file_path)
{
    if (!file_exists($file_path)) {
        return array('valid' => false, 'message' => __('File does not exist', 'vietnix-csv-import'));
    }

    $file_info = pathinfo($file_path);
    if (strtolower($file_info['extension']) !== 'csv') {
        return array('valid' => false, 'message' => __('File must be a CSV file', 'vietnix-csv-import'));
    }

    $file_size = filesize($file_path);
    $max_size = 10 * 1024 * 1024; // 10MB

    if ($file_size > $max_size) {
        return array('valid' => false, 'message' => __('File size too large (max 10MB)', 'vietnix-csv-import'));
    }

    // Test if file can be opened
    $handle = fopen($file_path, 'r');
    if (!$handle) {
        return array('valid' => false, 'message' => __('Cannot read file', 'vietnix-csv-import'));
    }

    // Check if file has content
    $first_line = fgets($handle);
    fclose($handle);

    if (empty($first_line)) {
        return array('valid' => false, 'message' => __('File is empty', 'vietnix-csv-import'));
    }

    return array('valid' => true, 'message' => __('File is valid', 'vietnix-csv-import'));
}

/**
 * Log error
 */
function vietnix_csv_log_error($message, $data = array())
{
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Vietnix CSV Import] ' . $message . (empty($data) ? '' : ' Data: ' . print_r($data, true)));
    }
}

/**
 * Get table columns info
 */
function vietnix_csv_get_table_columns()
{
    return array(
        'id' => array(
            'label' => __('ID', 'vietnix-csv-import'),
            'type' => 'number',
            'sortable' => true
        ),
        'product_name' => array(
            'label' => __('Product Name', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => true,
            'searchable' => true
        ),
        'product_sku' => [
            'label' => __('SKU', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => true,
            'searchable' => true
        ],
        'price' => [
            'label' => __('Price', 'vietnix-csv-import'),
            'type' => 'number',
            'sortable' => true
        ],
        'price_sale' => [
            'label' => __('Sale Price', 'vietnix-csv-import'),
            'type' => 'number',
            'sortable' => true
        ],
        'price_addon_sale' => [
            'label' => __('Addon Sale Price', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'price_addon_code' => [
            'label' => __('Addon Code', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'product_label_special' => [
            'label' => __('Special Label', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'product_label_featured' => [
            'label' => __('Featured Label', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'product_content' => [
            'label' => __('Content', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'usage_time' => [
            'label' => __('Usage Time', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'time_label_sale' => [
            'label' => __('Time Label Sale', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'time_label' => [
            'label' => __('Time Label', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'server_name' => [
            'label' => __('Server Name', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'server_sale' => [
            'label' => __('Server Sale', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'product_cta_link' => [
            'label' => __('CTA Link', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false
        ],
        'description' => [
            'label' => __('Description', 'vietnix-csv-import'),
            'type' => 'text',
            'sortable' => false,
            'searchable' => true
        ],
        'created_at' => array(
            'label' => __('Created', 'vietnix-csv-import'),
            'type' => 'datetime',
            'sortable' => true
        )
    );
}

/**
 * Sanitize shortcode attributes
 */
function vietnix_csv_sanitize_shortcode_atts($atts)
{
    $clean_atts = array();

    if (isset($atts['limit'])) {
        $clean_atts['limit'] = max(1, min(100, intval($atts['limit'])));
    }

    if (isset($atts['search'])) {
        $clean_atts['search'] = in_array(strtolower($atts['search']), array('true', '1', 'yes')) ? 'true' : 'false';
    }

    if (isset($atts['pagination'])) {
        $clean_atts['pagination'] = in_array(strtolower($atts['pagination']), array('true', '1', 'yes')) ? 'true' : 'false';
    }

    if (isset($atts['style'])) {
        $allowed_styles = array('default', 'striped', 'bordered', 'modern');
        $clean_atts['style'] = in_array($atts['style'], $allowed_styles) ? $atts['style'] : 'default';
    }

    if (isset($atts['columns'])) {
        if ($atts['columns'] !== 'all') {
            $columns = explode(',', $atts['columns']);
            $available_columns = array_keys(vietnix_csv_get_table_columns());
            $clean_atts['columns'] = array_intersect(array_map('trim', $columns), $available_columns);
        }
    }

    return $clean_atts;
}
