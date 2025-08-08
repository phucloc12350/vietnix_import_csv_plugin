<?php
/**
 * Class quản lý phần public của Vietnix CSV Import
 */

// Ngăn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class VietnixCSVPublic {
    
    protected $items;
    protected $columns;
    protected $time_label;

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
            wp_send_json_error(__('Bảo mật không hợp lệ', 'vietnix-csv-import'));
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
     * Format giá tiền
     */
    public function format_price($price, $currency = 'VND') {
        if (!$price || $price <= 0) {
            return '—';
        }
        
        return number_format($price, 0, ',', '.') ;
    }

    /**
     * Chuẩn bị dữ liệu cho price table
     * 
     * @param array $atts Shortcode attributes
     * @return array Dữ liệu đã được xử lý
     */
    public function prepare_table_data($atts) {
        // Parse columns
        $columns = isset($atts['columns']) && $atts['columns'] !== 'all' ? explode(',', $atts['columns']) : 'all';
        
        // Generate unique ID for this table
        $table_id = 'vietnix-price-table-' . uniqid();
        
        // Get data
        $show_pagination = isset($atts['pagination']) ? $atts['pagination'] : false;
        $limit = isset($atts['limit']) ? (int)$atts['limit'] : 20;
        
        $data_args = array(
            'limit' => $show_pagination ? $limit : 0,
            'offset' => 0,
            'order_by' => 'product_name',
            'order' => 'ASC'
        );
        
        $items = vietnix_csv_get_data($data_args);
        $total_items = vietnix_csv_count_data();
        
        // Available columns
        $all_columns = vietnix_csv_get_table_columns();
        $display_columns = $this->prepare_display_columns($columns, $all_columns);
        
        // Process các dữ liệu server và usage time
        $processed_data = $this->process_server_usage_data($items);
        
        return array(
            'table_id' => $table_id,
            'items' => $items,
            'total_items' => $total_items,
            'display_columns' => $display_columns,
            'server_sales' => $processed_data['server_sales'],
            'time_label_sales' => $processed_data['time_label_sales'],
            'time_label' => $processed_data['time_label'],
            'server_names' => $processed_data['server_names'],
            'server_usage_data' => $processed_data['server_usage_data']
        );
    }
    
    /**
     * Chuẩn bị display columns
     */
    protected function prepare_display_columns($columns, $all_columns) {
        if ($columns === 'all') {
            $display_columns = $all_columns;
        } else {
            $display_columns = array();
            foreach ($columns as $col) {
                $col = trim($col);
                if (isset($all_columns[$col])) {
                    $display_columns[$col] = $all_columns[$col];
                }
            }
        }
        
        // Remove ID column by default unless specifically requested
        if ($columns === 'all' && !in_array('id', (array) $columns)) {
            unset($display_columns['id']);
        }
        
        return $display_columns;
    }
    
    /**
     * Xử lý dữ liệu server và usage time
     */
    protected function process_server_usage_data($items) {
        // Lấy dữ liệu server_sale không bị trùng
        $server_sales = array();
        foreach ($items as $item) {
            if (!empty($item->server_name) && !empty($item->server_sale)) {
                if (!isset($server_sales[$item->server_name])) {
                    $server_sales[$item->server_name] = array();
                }
                if (!in_array($item->server_sale, $server_sales[$item->server_name])) {
                    $server_sales[$item->server_name][] = $item->server_sale;
                }
            }
        }
        
        // Lấy dữ liệu time_label_sale theo usage_time, không bị trùng
        $time_label_sales = array();
        foreach ($items as $item) {
            if (!empty($item->usage_time) && !empty($item->time_label_sale)) {
                if (!isset($time_label_sales[$item->usage_time])) {
                    $time_label_sales[$item->usage_time] = array();
                }
                if (!in_array($item->time_label_sale, $time_label_sales[$item->usage_time])) {
                    $time_label_sales[$item->usage_time][0] = $item->time_label_sale;
                }
            }
        }
        
        // Lấy dữ liệu time_label không bị trùng theo server_name và usage_time
        $time_label = array();
        foreach ($items as $item) {
            if (!empty($item->server_name) && !empty($item->usage_time) && !empty($item->time_label)) {
                if (!isset($time_label[$item->server_name])) {
                    $time_label[$item->server_name] = array();
                }
                if (!isset($time_label[$item->server_name][$item->usage_time])) {
                    $time_label[$item->server_name][$item->usage_time] = array();
                }
                if (!in_array($item->time_label, $time_label[$item->server_name][$item->usage_time])) {
                    $time_label[$item->server_name][$item->usage_time][] = $item->time_label;
                }
            }
        }
        
        // Lấy dữ liệu server_name không bị trùng tên
        $server_names = array_unique(array_column($items, 'server_name'));
        
        // Lấy dữ liệu usage_time không bị trùng, theo server_name
        $server_usage_data = array();
        foreach ($items as $item) {
            $server = $item->server_name;
            $usage_time = $item->usage_time;
            
            if (!empty($server) && !empty($usage_time)) {
                if (!isset($server_usage_data[$server])) {
                    $server_usage_data[$server] = array();
                }
                
                // Chỉ thêm usage_time nếu chưa tồn tại trong server này
                if (!in_array($usage_time, $server_usage_data[$server])) {
                    $server_usage_data[$server][] = $usage_time;
                }
            }
        }
        
        return array(
            'server_sales' => $server_sales,
            'time_label_sales' => $time_label_sales,
            'time_label' => $time_label,
            'server_names' => $server_names,
            'server_usage_data' => $server_usage_data
        );
    }
    
    /**
     * Get unique server sales
     */
    public function get_server_sales($items) {
        $server_sales = array();
        foreach ($items as $item) {
            if (!empty($item->server_name) && !empty($item->server_sale)) {
                if (!isset($server_sales[$item->server_name])) {
                    $server_sales[$item->server_name] = array();
                }
                if (!in_array($item->server_sale, $server_sales[$item->server_name])) {
                    $server_sales[$item->server_name][] = $item->server_sale;
                }
            }
        }
        return $server_sales;
    }
    
    /**
     * Get unique time label sales
     */
    public function get_time_label_sales($items) {
        $time_label_sales = array();
        foreach ($items as $item) {
            if (!empty($item->usage_time) && !empty($item->time_label_sale)) {
                if (!isset($time_label_sales[$item->usage_time])) {
                    $time_label_sales[$item->usage_time] = array();
                }
                if (!in_array($item->time_label_sale, $time_label_sales[$item->usage_time])) {
                    $time_label_sales[$item->usage_time][] = $item->time_label_sale;
                }
            }
        }
        return $time_label_sales;
    }
    
    /**
     * Get server usage data
     */
    public function get_server_usage_data($items) {
        $server_usage_data = array();
        foreach ($items as $item) {
            $server = $item->server_name;
            $usage_time = $item->usage_time;
            
            if (!empty($server) && !empty($usage_time)) {
                if (!isset($server_usage_data[$server])) {
                    $server_usage_data[$server] = array();
                }
                
                if (!in_array($usage_time, $server_usage_data[$server])) {
                    $server_usage_data[$server][] = $usage_time;
                }
            }
        }
        return $server_usage_data;
    }


}
