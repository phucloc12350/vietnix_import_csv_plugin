<?php
/**
 * Class quản lý phần public của Vietnix CSV Import
 */

// Ngăn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class VietnixCSVPublic {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Khởi tạo hooks
     */
    private function init_hooks() {
        // AJAX handlers cho public
        add_action('wp_ajax_vietnix_get_table_data', array($this, 'get_table_data'));
        add_action('wp_ajax_nopriv_vietnix_get_table_data', array($this, 'get_table_data'));
    }
    
    /**
     * Get table data for AJAX
     */
    public function get_table_data() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'vietnix_csv_nonce')) {
            wp_send_json_error(__('Security check failed', 'vietnix-csv-import'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vietnix_price_data';
        
        // Get parameters
        $server_name = isset($_POST['server_name']) ? sanitize_text_field($_POST['server_name']) : '';
        $usage_time = isset($_POST['usage_time']) ? sanitize_text_field($_POST['usage_time']) : '';
        
        // Build WHERE clause
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($server_name)) {
            $where_conditions[] = "server_name = %s";
            $where_values[] = $server_name;
        }
        
        if (!empty($usage_time)) {
            $where_conditions[] = "usage_time = %s";
            $where_values[] = $usage_time;
        }
        
        // Get data
        $data_query = "SELECT * FROM {$table_name}";
        if (!empty($where_conditions)) {
            $data_query .= " WHERE " . implode(' AND ', $where_conditions);
        }
        $data_query .= " ORDER BY product_name ASC";
        
        if (!empty($where_values)) {
            $items = $wpdb->get_results($wpdb->prepare($data_query, $where_values));
        } else {
            $items = $wpdb->get_results($data_query);
        }
        
        // Format data
        $formatted_items = array();
        foreach ($items as $item) {
            $formatted_items[] = array(
                'id' => $item->id,
                'product_name' => esc_html($item->product_name),
                'product_sku' => esc_html($item->product_sku ?: ''),
                'price' => $this->format_price($item->price),
                'price_raw' => $item->price,
                'price_sale' => $item->price_sale ? $this->format_price($item->price_sale) : '',
                'price_sale_raw' => $item->price_sale,
                'currency' => esc_html($item->currency ?: 'VND'),
                // 'currency' => '',
                'price_addon_sale' => esc_html($item->price_addon_sale ?: ''),
                'price_addon_code' => esc_html($item->price_addon_code ?: ''),
                'product_label_special' => esc_html($item->product_label_special ?: ''),
                'product_label_featured' => esc_html($item->product_label_featured ?: ''),
                'product_content' => esc_html($item->product_content ?: ''),
                'usage_time' => esc_html($item->usage_time ?: ''),
                'time_label_sale' => esc_html($item->time_label_sale ?: ''),
                'time_label' => esc_html($item->time_label ?: ''),
                'server_name' => esc_html($item->server_name ?: ''),
                'server_sale' => esc_html($item->server_sale ?: ''),
                'product_cta_link' => esc_url($item->product_cta_link ?: ''),
                'description' => esc_html($item->description ?: ''),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            );
        }
        
        $response = array(
            'items' => $formatted_items
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * Format price with currency
     */
    public function format_price($price, $currency = 'VND') {
        if (!$price || $price <= 0) {
            return '—';
        }
        
        return number_format($price, 0, ',', '.') ;
    }
}
