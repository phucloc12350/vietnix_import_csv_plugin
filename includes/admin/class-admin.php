<?php
/**
 * Class quản lý phần admin của Vietnix CSV Import
 */

// Ngăn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class VietnixCSVAdmin {
    
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
        // Settings API
        add_action('admin_init', array($this, 'register_settings'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
        
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'vietnix_csv_settings_group',
            'vietnix_csv_table_settings',
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'vietnix_csv_display_section',
            __('Display Settings', 'vietnix-csv-import'),
            array($this, 'display_section_callback'),
            'vietnix_csv_settings'
        );
        
        add_settings_field(
            'show_pagination',
            __('Show Pagination', 'vietnix-csv-import'),
            array($this, 'checkbox_field_callback'),
            'vietnix_csv_settings',
            'vietnix_csv_display_section',
            array(
                'name' => 'show_pagination',
                'label' => __('Enable pagination in shortcode', 'vietnix-csv-import')
            )
        );
        
        add_settings_field(
            'items_per_page',
            __('Items Per Page', 'vietnix-csv-import'),
            array($this, 'number_field_callback'),
            'vietnix_csv_settings',
            'vietnix_csv_display_section',
            array(
                'name' => 'items_per_page',
                'min' => 5,
                'max' => 100,
                'description' => __('Number of items to show per page', 'vietnix-csv-import')
            )
        );
        
        add_settings_field(
            'enable_search',
            __('Enable Search', 'vietnix-csv-import'),
            array($this, 'checkbox_field_callback'),
            'vietnix_csv_settings',
            'vietnix_csv_display_section',
            array(
                'name' => 'enable_search',
                'label' => __('Enable search functionality', 'vietnix-csv-import')
            )
        );

    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['show_pagination'] = isset($input['show_pagination']) ? true : false;
        $sanitized['items_per_page'] = isset($input['items_per_page']) ? intval($input['items_per_page']) : 10;
        $sanitized['enable_search'] = isset($input['enable_search']) ? true : false;
        $sanitized['table_style'] = isset($input['table_style']) ? sanitize_text_field($input['table_style']) : 'default';
        
        // Validate items per page
        if ($sanitized['items_per_page'] < 5) {
            $sanitized['items_per_page'] = 5;
        } elseif ($sanitized['items_per_page'] > 100) {
            $sanitized['items_per_page'] = 100;
        }
        
        return $sanitized;
    }
    
    /**
     * Display section callback
     */
    public function display_section_callback() {
        echo '<p>' . __('Configure how the price table is displayed on frontend.', 'vietnix-csv-import') . '</p>';
    }
    
    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback($args) {
        $settings = get_option('vietnix_csv_table_settings', array());
        $value = isset($settings[$args['name']]) ? $settings[$args['name']] : false;
        
        echo '<input type="checkbox" id="' . $args['name'] . '" name="vietnix_csv_table_settings[' . $args['name'] . ']" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="' . $args['name'] . '">' . $args['label'] . '</label>';
    }
    
    /**
     * Number field callback
     */
    public function number_field_callback($args) {
        $settings = get_option('vietnix_csv_table_settings', array());
        $value = isset($settings[$args['name']]) ? $settings[$args['name']] : 10;
        
        echo '<input type="number" id="' . $args['name'] . '" name="vietnix_csv_table_settings[' . $args['name'] . ']" value="' . esc_attr($value) . '" min="' . $args['min'] . '" max="' . $args['max'] . '" class="small-text" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }
    
    /**
     * Select field callback
     */
    public function select_field_callback($args) {
        $settings = get_option('vietnix_csv_table_settings', array());
        $value = isset($settings[$args['name']]) ? $settings[$args['name']] : 'default';
        
        echo '<select id="' . $args['name'] . '" name="vietnix_csv_table_settings[' . $args['name'] . ']">';
        foreach ($args['options'] as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
        if (isset($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        // Check if table exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'vietnix_price_data';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            echo '<div class="notice notice-warning"><p>' . 
                 sprintf(__('Vietnix CSV Import: Database table is missing. Please <a href="%s">deactivate and reactivate</a> the plugin.', 'vietnix-csv-import'), 
                 admin_url('plugins.php')) . 
                 '</p></div>';
        }
    }
    
 

}
