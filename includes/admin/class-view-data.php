<?php
/**
 * View Data page logic using VietnixCSVDataTable class
 */

// Ngăn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

//tạo class để sử dụng cho view-data 
class VietnixCSVDataTable
{
    protected $wpdb;
    protected $table_name;
    protected $per_page;
    protected $current_page;
    protected $search;
    protected $offset;
    protected $items;
    protected $total_items;
    protected $total_pages;
    protected $last_import;
    public function __construct($per_page = 20)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'vietnix_price_data';
        $this->per_page = $per_page;
        $this->current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $this->search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $this->offset = ($this->current_page - 1) * $this->per_page;
        $this->fetch_data();
    }

    protected function build_where_clause(&$query_params)
    {
        $where_conditions = array();

        if (!empty($this->search)) {
            $where_conditions[] = "(product_name LIKE %s OR server_name LIKE %s OR product_sku LIKE %s OR usage_time LIKE %s OR price_addon_code LIKE %s)";
            $search_term = '%' . $this->wpdb->esc_like($this->search) . '%';
            $query_params = array_merge($query_params, array($search_term, $search_term, $search_term, $search_term, $search_term));
        }

        if (!empty($where_conditions)) {
            return 'WHERE ' . implode(' AND ', $where_conditions);
        }
        return '';
    }

    protected function fetch_data()
    {
        $query_params = array();
        $where_clause = $this->build_where_clause($query_params);


        // Get total count
        $total_query = "SELECT COUNT(*) FROM {$this->table_name} {$where_clause}";
        if (!empty($query_params)) {
            $total_query = $this->wpdb->prepare($total_query, $query_params);
        }
        $this->total_items = $this->wpdb->get_var($total_query);
        // get total item
        $this->last_import = $this->wpdb->get_var("SELECT MAX(created_at) FROM {$this->table_name}");

        // Get data
        $data_query = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $final_params = array_merge($query_params, array($this->per_page, $this->offset));
        $data_query = $this->wpdb->prepare($data_query, $final_params);
        $this->items = $this->wpdb->get_results($data_query);

        $this->total_pages = ceil($this->total_items / $this->per_page);
    }

    public function get_items()
    {
        return $this->items;
    }

    public function get_total_items()
    {
        return $this->total_items;
    }

    public function get_total_pages()
    {
        return $this->total_pages;
    }

    public function get_current_page()
    {
        return $this->current_page;
    }
    public function search_handle()
    {
        return $this->search;
    }
    public function last_import()
    {
        return $this->last_import;
    }
}