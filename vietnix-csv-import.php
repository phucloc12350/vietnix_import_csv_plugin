<?php
/**
 * Plugin Name: Vietnix Import CSV Table Price
 * Plugin URI: https://vietnix.vn
 * Description: Plugin import file CSV vào custom table và hiển thị bằng shortcode.
 * Version: 1.0.0
 * Author: Vietnix, locdp@vietnix.com.vn
 * Author URI: https://vietnix.vn
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vietnix-csv-import
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Ngăn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa các constants
define('VIETNIX_CSV_VERSION', '1.0.0');
define('VIETNIX_CSV_URL', plugin_dir_url(__FILE__));
define('VIETNIX_CSV_PATH', plugin_dir_path(__FILE__));
define('VIETNIX_CSV_BASENAME', plugin_basename(__FILE__));

/**
 * Class chính của plugin
 */
class VietnixCSVImport {
    
    /**
     * Stats reference for image import
     */
    private $current_stats;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Khởi tạo các hooks
     */
    private function init_hooks() {
        // Hook khi plugin được activate
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Hook khi plugin được deactivate
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Hook khi plugin được uninstall
        register_uninstall_hook(__FILE__, array('VietnixCSVImport', 'uninstall'));
        
        // Khởi tạo plugin
        add_action('init', array($this, 'init'));
        
        // Load textdomain cho translation
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Enqueue scripts và styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Thêm menu admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Shortcode
        add_shortcode('vietnix_price_table', array($this, 'price_table_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_vietnix_import_csv', array($this, 'handle_csv_import'));
        add_action('wp_ajax_vietnix_delete_data', array($this, 'handle_delete_data'));
    }
    
    /**
     * Khi plugin được activate
     */
    public function activate() {
        // Tạo bảng database
        $this->create_tables();
        
        // Thêm options mặc định
        add_option('vietnix_csv_version', VIETNIX_CSV_VERSION);
        add_option('vietnix_csv_table_settings', array(
            'show_pagination' => true,
            'items_per_page' => 10,
            'enable_search' => true,
            'table_style' => 'default'
        ));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Khi plugin được deactivate
     */
    public function deactivate() {
        // Cleanup khi deactivate
        flush_rewrite_rules();
    }
    
    /**
     * Khi plugin được uninstall
     */
    public static function uninstall() {
        // Xóa options
        delete_option('vietnix_csv_version');
        delete_option('vietnix_csv_table_settings');
        
        // Xóa bảng database
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vietnix_price_data");
    }
    
    /**
     * Khởi tạo plugin
     */
    public function init() {
        // Load includes
        $this->includes();
        
        // Khởi tạo các class con
        if (is_admin()) {
            new VietnixCSVAdmin();
        }
        
        new VietnixCSVPublic();
    }
    
    /**
     * Load textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'vietnix-csv-import',
            false,
            dirname(VIETNIX_CSV_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Enqueue scripts cho frontend
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'vietnix-csv-public-js',
            VIETNIX_CSV_URL . 'assets/js/public.js',
            array('jquery'),
            VIETNIX_CSV_VERSION,
            true
        );
        
        wp_enqueue_style(
            'vietnix-csv-base-css',
            VIETNIX_CSV_URL . 'assets/css/base.css',
            array(),
            VIETNIX_CSV_VERSION
        );
        wp_enqueue_style(
            'vietnix-csv-public-css',
            VIETNIX_CSV_URL . 'assets/css/public.css',
            array(),
            VIETNIX_CSV_VERSION
        );
        
        
        // Localize script
        wp_localize_script('vietnix-csv-public-js', 'vietnixCSV', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vietnix_csv_nonce'),
            'loading_text' => __('Loading...', 'vietnix-csv-import')
        ));
    }
    
    /**
     * Enqueue scripts cho admin
     */
    public function admin_enqueue_scripts($hook) {
        // Chỉ load trên trang admin của plugin
        if (strpos($hook, 'vietnix-csv') === false) {
            return;
        }
        
        wp_enqueue_script(
            'vietnix-csv-admin-js',
            VIETNIX_CSV_URL . 'assets/js/admin.js',
            array('jquery'),
            VIETNIX_CSV_VERSION,
            true
        );
        
        wp_enqueue_style(
            'vietnix-csv-admin-css',
            VIETNIX_CSV_URL . 'assets/css/admin.css',
            array(),
            VIETNIX_CSV_VERSION
        );
        
        wp_enqueue_style(
            'vietnix-csv-admin-import-page-css',
            VIETNIX_CSV_URL . 'assets/css/import-page.css',
            array(),
            VIETNIX_CSV_VERSION
        );
         wp_enqueue_style(
            'vietnix-csv-admin-view-data-css',
            VIETNIX_CSV_URL . 'assets/css/admin-view-data.css',
            array(),
            VIETNIX_CSV_VERSION
        );
        // Localize script
        wp_localize_script('vietnix-csv-admin-js', 'vietnixCSVAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vietnix_csv_admin_nonce'),
            'confirm_delete' => __('Are you sure you want to delete all data?', 'vietnix-csv-import'),
            'import_success' => __('CSV imported successfully!', 'vietnix-csv-import'),
            'import_error' => __('Import failed. Please check your CSV file.', 'vietnix-csv-import')
        ));
    }
    
    /**
     * Thêm menu admin
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Vietnix CSV Import', 'vietnix-csv-import'),
            __('CSV Import', 'vietnix-csv-import'),
            'manage_options',
            'vietnix-csv-import',
            array($this, 'admin_page'),
            'dashicons-upload',
            30
        );
        
        add_submenu_page(
            'vietnix-csv-import',
            __('Import Data', 'vietnix-csv-import'),
            __('Import Data', 'vietnix-csv-import'),
            'manage_options',
            'vietnix-csv-import',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'vietnix-csv-import',
            __('View Data', 'vietnix-csv-import'),
            __('View Data', 'vietnix-csv-import'),
            'manage_options',
            'vietnix-csv-view',
            array($this, 'view_data_page')
        );
        
       
    }
    
    /**
     * Trang admin chính
     */
    public function admin_page() {
        include VIETNIX_CSV_PATH . 'includes/admin/views/import-page.php';
    }
    
    /**
     * Trang xem data
     */
    public function view_data_page() {
        include VIETNIX_CSV_PATH . 'includes/admin/views/view-data.php';
    }
     
    
    /**
     * Shortcode hiển thị bảng giá
     */
    public function price_table_shortcode($atts) {
        $atts = shortcode_atts(array( 
            'style' => 'default',
            'columns' => 'all'
        ), $atts);
        
        ob_start();
        include VIETNIX_CSV_PATH . 'includes/public/views/price-table.php';
        return ob_get_clean();
    }
    
    /**
     * Handle CSV import AJAX
     */
    public function handle_csv_import() {
        // Kiểm tra nonce và quyền
        if (!wp_verify_nonce($_POST['nonce'], 'vietnix_csv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed', 'vietnix-csv-import'));
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('No file uploaded or upload error', 'vietnix-csv-import'));
        }
        
        $file_path = $_FILES['csv_file']['tmp_name'];
        $file_info = pathinfo($_FILES['csv_file']['name']);
        
        if (strtolower($file_info['extension']) !== 'csv') {
            wp_send_json_error(__('Please upload a CSV file', 'vietnix-csv-import'));
        }
        
        // Import CSV
        $result = $this->import_csv_data($file_path);
        
        if ($result['success']) {
            $message = sprintf(__('Successfully imported %d rows', 'vietnix-csv-import'), $result['count']);
            
            // Add image download info if enabled
            if (isset($_POST['download_images']) && $_POST['download_images'] === '1') {
                if ($result['images_downloaded'] > 0) {
                    $message .= sprintf(__(' • Downloaded %d images', 'vietnix-csv-import'), $result['images_downloaded']);
                }
                if ($result['images_failed'] > 0) {
                    $message .= sprintf(__(' • Failed to download %d images', 'vietnix-csv-import'), $result['images_failed']);
                }
            }
            
            wp_send_json_success(array(
                'message' => $message,
                'stats' => $result
            ));
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Handle delete data AJAX
     */
    public function handle_delete_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'vietnix_csv_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed', 'vietnix-csv-import'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vietnix_price_data';
        
        // Optional: Delete imported images from Media Library
        if (isset($_POST['delete_images']) && $_POST['delete_images'] === '1') {
            $this->cleanup_imported_images();
        }
        
        $result = $wpdb->query("DELETE FROM {$table_name}");
        
        if ($result !== false) {
            wp_send_json_success(__('All data deleted successfully', 'vietnix-csv-import'));
        } else {
            wp_send_json_error(__('Failed to delete data', 'vietnix-csv-import'));
        }
    }
    
    /**
     * Cleanup imported images from Media Library
     */
    private function cleanup_imported_images() {
        global $wpdb;
        
        // Find all images imported by this plugin
        $imported_images = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_imported_by' AND meta_value = %s",
            'vietnix_csv_import'
        ));
        
        foreach ($imported_images as $image_id) {
            wp_delete_attachment($image_id, true);
        }
    }
    
    /**
     * Load includes
     */
    private function includes() {
        // Admin includes
        if (is_admin()) {
            require_once VIETNIX_CSV_PATH . 'includes/admin/class-admin.php';
        }

        // Public includes
        require_once VIETNIX_CSV_PATH . 'includes/public/class-public.php';
        
        // Helper functions
        require_once VIETNIX_CSV_PATH . 'includes/functions.php';
    }
    
    /**
     * Tạo bảng database
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vietnix_price_data';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_name varchar(255) NOT NULL,
            product_sku varchar(100),
            price decimal(10,2) NOT NULL,
            price_sale decimal(10,2),
            currency varchar(10) DEFAULT 'VND',
            price_addon_sale varchar(100),
            price_addon_code varchar(100),
            product_label_special varchar(100),
            product_label_featured varchar(100),
            product_content text,
            usage_time varchar(100), 
            time_label_sale varchar(100),
            time_label varchar(100),
            server_name varchar(100),
            server_sale varchar(100),
            product_cta_link varchar(100),
            description text, 
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_product_name (product_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Download image from URL and add to Media Library
     */
    private function download_and_import_image($image_url, $post_title = '', &$stats = null) {
        if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if image already exists by URL
        global $wpdb;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_source_url' AND meta_value = %s",
            $image_url
        ));
        
        if ($existing) {
            return wp_get_attachment_url($existing);
        }
        
        // Include WordPress media functions
        if (!function_exists('media_handle_sideload')) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        // Download image
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            if ($stats !== null && isset($stats['images_failed'])) {
                $stats['images_failed']++;
            }
            return false;
        }
        
        // Get file info
        $file_array = array(
            'name' => basename(parse_url($image_url, PHP_URL_PATH)),
            'tmp_name' => $tmp
        );
        
        // If no file extension, try to get from content type
        if (!pathinfo($file_array['name'], PATHINFO_EXTENSION)) {
            $file_info = wp_check_filetype_and_ext($tmp, $file_array['name']);
            if ($file_info['ext']) {
                $file_array['name'] = $file_array['name'] . '.' . $file_info['ext'];
            }
        }
        
        // Handle sideload
        $id = media_handle_sideload($file_array, 0, $post_title);
        
        // Clean up temp file
        if (file_exists($tmp)) {
            @unlink($tmp);
        }
        
        if (is_wp_error($id)) {
            if ($stats !== null && isset($stats['images_failed'])) {
                $stats['images_failed']++;
            }
            return false;
        }
        
        // Store source URL for future reference
        update_post_meta($id, '_source_url', $image_url);
        update_post_meta($id, '_imported_by', 'vietnix_csv_import');
        
        if ($stats !== null && isset($stats['images_downloaded'])) {
            $stats['images_downloaded']++;
        }
        
        return wp_get_attachment_url($id);
    }
    
    /**
     * Process content and download images
     */
    private function process_content_images($content, &$stats = null) {
        if (empty($content)) {
            return $content;
        }
        
        // Store stats reference for callback
        $this->current_stats = &$stats;
        
        // Pattern to find image URLs (both direct URLs and img src)
        $patterns = array(
            // Direct image URLs
            '/(https?:\/\/[^\s<>"\']+\.(?:jpg|jpeg|png|gif|webp|svg))/i',
            // IMG tags src attribute
            '/(<img[^>]+src=["\'])([^"\']+\.(?:jpg|jpeg|png|gif|webp|svg))(["\'][^>]*>)/i'
        );
        
        foreach ($patterns as $pattern) {
            $content = preg_replace_callback($pattern, array($this, 'replace_image_callback'), $content);
        }
        
        return $content;
    }
    
    /**
     * Callback function for image replacement
     */
    private function replace_image_callback($matches) {
        if (count($matches) == 2) {
            // Direct URL match
            $original_url = $matches[1];
            $new_url = $this->download_and_import_image($original_url, '', $this->current_stats);
            return $new_url ? $new_url : $original_url;
        } elseif (count($matches) == 4) {
            // IMG tag match
            $before = $matches[1];
            $original_url = $matches[2];
            $after = $matches[3];
            $new_url = $this->download_and_import_image($original_url, '', $this->current_stats);
            return $before . ($new_url ? $new_url : $original_url) . $after;
        }
        
        return $matches[0];
    }
    
    /**
     * Import CSV data
     */
    private function import_csv_data($file_path) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vietnix_price_data';
        $count = 0;
        $images_downloaded = 0;
        $images_failed = 0;
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            // Đọc header row
            $headers = fgetcsv($handle, 1000, ',');
            
            if (!$headers) {
                return array('success' => false, 'message' => __('Invalid CSV file', 'vietnix-csv-import'));
            }
            
            // Mapping columns
            $column_mapping = array(
            'id' => array('id', 'ID'),
            'product_name' => array('product_name', 'Tên sản phẩm'),
            'product_sku' => array('product_sku', 'SKU'),
            'price' => array('price', 'Giá'),
            'price_sale' => array('price_sale', 'Giá khuyến mãi'),
            'currency' => array('currency', 'Đơn vị tiền tệ'),
            'price_addon_sale' => array('price_addon_sale', 'Giá addon khuyến mãi'),
            'price_addon_code' => array('price_addon_code', 'Mã addon'),
            'product_label_special' => array('product_label_special', 'Nhãn đặc biệt'),
            'product_label_featured' => array('product_label_featured', 'Nhãn nổi bật'),
            'product_content' => array('product_content', 'Nội dung sản phẩm'), 
            'usage_time' => array('usage_time', 'Thời gian sử dụng'),
            'time_label_sale' => array('time_label_sale', 'Nhãn thời gian khuyến mãi'),
            'time_label' => array('time_label', 'Nhãn thời gian'),
            'server_name' => array('server_name', 'Tên server'),
            'server_sale' => array('server_sale', 'Server khuyến mãi'),
            'product_cta_link' => array('product_cta_link', 'CTA Link'),
            'description' => array('description', 'Mô tả'),

            );
            
            // Tìm index của các columns
            $column_indexes = array();
            foreach ($column_mapping as $db_column => $possible_names) {
                foreach ($headers as $index => $header) {
                    $header_lower = strtolower(trim($header));
                    if (in_array($header_lower, array_map('strtolower', $possible_names))) {
                        $column_indexes[$db_column] = $index;
                        break;
                    }
                }
            }
            
            // Kiểm tra required columns
            if (!isset($column_indexes['product_name']) || !isset($column_indexes['price'])) {
                return array('success' => false, 'message' => __('Required columns (product_name, price) not found', 'vietnix-csv-import'));
            }
            
            // Clear existing data option
            if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === '1') {
                $wpdb->query("DELETE FROM {$table_name}");
            }
            
            // Import data
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if (empty($data[0])) continue; // Skip empty rows
                
                // Process product_content and download images if option is enabled
                $product_content = isset($column_indexes['product_content']) ? $data[$column_indexes['product_content']] : '';
                $processed_content = $product_content;
                
                // Only download images if option is enabled
                if (isset($_POST['download_images']) && $_POST['download_images'] === '1') {
                    $stats = array('images_downloaded' => &$images_downloaded, 'images_failed' => &$images_failed);
                    $processed_content = $this->process_content_images($product_content, $stats);
                }
                
                $insert_data = array(
                    'product_name' => isset($column_indexes['product_name']) ? sanitize_text_field($data[$column_indexes['product_name']]) : '',
                    'price' => isset($column_indexes['price']) ? floatval(str_replace(',', '', $data[$column_indexes['price']])) : 0,
                    'currency' => isset($column_indexes['currency']) ? sanitize_text_field($data[$column_indexes['currency']]) : 'VND',
                    'description' => isset($column_indexes['description']) ? sanitize_textarea_field($data[$column_indexes['description']]) : '',
                    'product_sku' => isset($column_indexes['product_sku']) ? sanitize_text_field($data[$column_indexes['product_sku']]) : '',
                    'price_sale' => isset($column_indexes['price_sale']) ? floatval(str_replace(',', '', $data[$column_indexes['price_sale']])) : null,
                    'price_addon_sale' => isset($column_indexes['price_addon_sale']) ? sanitize_text_field($data[$column_indexes['price_addon_sale']]) : '',
                    'price_addon_code' => isset($column_indexes['price_addon_code']) ? sanitize_text_field($data[$column_indexes['price_addon_code']]) : '',
                    'product_label_special' => isset($column_indexes['product_label_special']) ? sanitize_text_field($data[$column_indexes['product_label_special']]) : '',
                    'product_label_featured' => isset($column_indexes['product_label_featured']) ? sanitize_text_field($data[$column_indexes['product_label_featured']]) : '',
                    'product_content' => $processed_content,
                    'usage_time' => isset($column_indexes['usage_time']) ? sanitize_text_field($data[$column_indexes['usage_time']]) : '',
                    'time_label_sale' => isset($column_indexes['time_label_sale']) ? sanitize_text_field($data[$column_indexes['time_label_sale']]) : '',
                    'time_label' => isset($column_indexes['time_label']) ? sanitize_text_field($data[$column_indexes['time_label']]) : '',
                    'server_name' => isset($column_indexes['server_name']) ? sanitize_text_field($data[$column_indexes['server_name']]) : '',
                    'server_sale' => isset($column_indexes['server_sale']) ? sanitize_text_field($data[$column_indexes['server_sale']]) : '',
                    'product_cta_link' => isset($column_indexes['product_cta_link']) ? esc_url_raw($data[$column_indexes['product_cta_link']]) : '',
                );
                
                if (!empty($insert_data['product_name']) && $insert_data['price'] > 0) {
                    $result = $wpdb->insert($table_name, $insert_data);
                    if ($result) {
                        $count++;
                    }
                }
            }
            fclose($handle);
        }
        
        $result = array(
            'success' => true, 
            'count' => $count,
            'images_downloaded' => $images_downloaded,
            'images_failed' => $images_failed
        );
        
        return $result;
    }
}

// Khởi tạo plugin
new VietnixCSVImport();
