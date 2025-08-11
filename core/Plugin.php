<?php
namespace VietnixImportCsv\Core;


class Plugin
{
    /**
     * Stats reference for image import
     */
    private $current_stats;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Khởi tạo các hooks
     */
    private function init_hooks()
    {
        // Hook khi plugin được activate (sẽ được đăng ký từ file chính)

        // Hook khi plugin được deactivate
        register_deactivation_hook(VIETNIX_IMPORT_CSV_PLUGIN_DIR . '/vietnix-csv-import.php', array($this, 'deactivate'));

        // Hook khi plugin được uninstall
        register_uninstall_hook(VIETNIX_IMPORT_CSV_PLUGIN_DIR . '/vietnix-csv-import.php', array('VietnixCSVImport', 'uninstall'));

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

        // AJAX handlers sẽ được đăng ký trong class VietnixImport
    }

    /**
     * Khi plugin được activate
     */
    public function activate()
    {
        // Load classes first
        $this->includes();

        // Tạo bảng database
        if (class_exists('VietnixImport')) {
            $import = new \VietnixImport();
            $import->create_tables();
        }

        // Thêm options mặc định
        add_option('VIETNIX_IMPORT_CSV_PLUGIN_VERSION', VIETNIX_IMPORT_CSV_PLUGIN_VERSION);
        add_option('vietnix_csv_table_settings', array(
            'show_pagination' => true,
            'items_per_page' => 10,
            'enable_search' => true,
            'table_style' => 'default'
        ));

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public static function get_instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    /**
     * Khi plugin được deactivate
     */
    public function deactivate()
    {
        // Cleanup khi deactivate
        flush_rewrite_rules();
    }

    /**
     * Khi plugin được uninstall
     */
    public static function uninstall()
    {
        // Xóa options
        delete_option('VIETNIX_IMPORT_CSV_PLUGIN_VERSION');
        delete_option('vietnix_csv_table_settings');

        // Xóa bảng database
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vietnix_price_data");
    }

    /**
     * Khởi tạo plugin
     */
    public function init()
    {
        // Load includes
        $this->includes();

        // Khởi tạo các class con
        if (is_admin()) {
            new \VietnixCSVAdmin();
            new \VietnixImport();
        }

        new \VietnixCSVPublic();
    }

    /**
     * Load includes
     */
    private function includes()
    {
        // Admin includes
        if (is_admin()) {
            require_once VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/admin/class-admin.php';
            require_once VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/admin/class-import.php';
            require_once VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/admin/class-view-data.php';
        }

        // Public includes
        require_once VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/public/class-public.php';

        // Helper functions
        require_once VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/functions.php';
    }

    /**
     * Load textdomain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'vietnix-csv-import',
            false,
            dirname(VIETNIX_IMPORT_CSV_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Enqueue scripts cho frontend
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'vietnix-csv-public-js',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/dist/js/public.js',
            array('jquery'),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION,
            true
        );

        wp_enqueue_style(
            'vietnix-csv-base-css',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/css/base.css',
            array(),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION
        );
        wp_enqueue_style(
            'vietnix-csv-public-css',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/css/public.css',
            array(),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION
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
    public function admin_enqueue_scripts($hook)
    {
        // Chỉ load trên trang admin của plugin
        if (strpos($hook, 'vietnix-csv') === false) {
            return;
        }

        wp_enqueue_script(
            'vietnix-csv-admin-js',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/dist/js/admin.js',
            array('jquery'),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION,
            true
        );

        wp_enqueue_style(
            'vietnix-csv-main-css',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/css/main.css',
            array(),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION
        );

        wp_enqueue_style(
            'vietnix-csv-admin-import-css',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/css/import.css',
            array(),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION
        );
        wp_enqueue_style(
            'vietnix-csv-admin-view-data-css',
            VIETNIX_IMPORT_CSV_PLUGIN_URL . 'assets/css/view-data.css',
            array(),
            VIETNIX_IMPORT_CSV_PLUGIN_VERSION
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
    public function add_admin_menu()
    {
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
    public function admin_page()
    {
        include VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/admin/views/import-page.php';
    }

    /**
     * Trang xem data
     */
    public function view_data_page()
    {
        include VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/admin/views/view-data.php';
    }

    /**
     * Shortcode hiển thị bảng giá
     */
    public function price_table_shortcode($atts)
    {
        // Đảm bảo CSS và JS được enqueue cho shortcode trong footer
        add_action('wp_footer', function () {
            $this->enqueue_scripts();
        });

        // Đảm bảo các file cần thiết được include
        $this->includes();

        $atts = shortcode_atts(array(
            'style' => 'default',
            'columns' => 'all'
        ), $atts);

        ob_start();
        include VIETNIX_IMPORT_CSV_PLUGIN_PATH . 'includes/public/views/price-table.php';
        return ob_get_clean();
    }


}