<?php
// Get data for display
global $wpdb;
$table_name = $wpdb->prefix . 'vietnix_price_data';

$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
// $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

$offset = ($current_page - 1) * $per_page;

// Build where clause
$where_conditions = array();
$query_params = array();

if (!empty($search)) {
    $where_conditions[] = "(product_name LIKE %s OR server_name LIKE %s OR product_sku LIKE %s OR usage_time LIKE %s OR price_addon_code LIKE %s)";
    $search_term = '%' . $wpdb->esc_like($search) . '%';
    $query_params = array_merge($query_params, array($search_term, $search_term, $search_term, $search_term, $search_term));
}


$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
$total_query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
if (!empty($query_params)) {
    $total_query = $wpdb->prepare($total_query, $query_params);
}
$total_items = $wpdb->get_var($total_query);

// Get data
$data_query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
$final_params = array_merge($query_params, array($per_page, $offset));
$data_query = $wpdb->prepare($data_query, $final_params);
$items = $wpdb->get_results($data_query);

// Get categories for filter
// $categories = $wpdb->get_col("SELECT DISTINCT category FROM {$table_name} WHERE category != '' ORDER BY category");


$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Search and Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="vietnix-csv-view" />

                <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                    placeholder="<?php _e('Search products...', 'vietnix-csv-import'); ?>" />


                <!-- <select name="status">
                    <option value=""><?php _e('All Status', 'vietnix-csv-import'); ?></option>
                    <option value="active" <?php selected($status_filter, current: 'active'); ?>>
                        <?php _e('Active', 'vietnix-csv-import'); ?>
                    </option>
                    <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>
                        <?php _e('Inactive', 'vietnix-csv-import'); ?>
                    </option>
                </select> -->

                <?php submit_button(__('Filter', 'vietnix-csv-import'), 'secondary', 'submit', false); ?>

            </form>
        </div>

        <div class="alignright actions">
            <span class="displaying-num">
                <?php printf(_n('%s item', '%s items', $total_items, 'vietnix-csv-import'), number_format_i18n($total_items)); ?>
            </span>
        </div>
    </div>
    <!-- Data Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><?php _e('ID', 'vietnix-csv-import'); ?></th>
                <th scope="col"><?php _e('Tên sản phẩm', 'vietnix-csv-import'); ?></th>
                <th scope="col"><?php _e('Loại thời gian', 'vietnix-csv-import'); ?></th>
                <th scope="col"><?php _e('Thời gian [nhãn]', 'vietnix-csv-import'); ?></th>
                <th scope="col"><?php _e('Thời gian [nhãn Nổi bật]', 'vietnix-csv-import'); ?></th>
                <th scope="col"><?php _e('Danh mục', 'vietnix-csv-import'); ?></th>
                <th scope="col"><?php _e('Danh mục[nhãn]', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 120px;"><?php _e('Giá', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 100px;"><?php _e('Giá đã giảm', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 100px;"><?php _e('Mã giảm phẩm', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 80px;"><?php _e('Nội dung giảm giá', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 80px;"><?php _e('Code giảm giá', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 80px;"><?php _e('Sản phẩm [Nhãn]', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 80px;"><?php _e('Sản phẩm [Nhãn nổi bật]', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 80px;"><?php _e('Link', 'vietnix-csv-import'); ?></th>
                <th scope="col" style="width: 120px;"><?php _e('Ngày tạo', 'vietnix-csv-import'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <?php if ($search): ?>
                            <?php _e('No items found matching your criteria.', 'vietnix-csv-import'); ?>
                        <?php else: ?>
                            <?php _e('No data found. Please import a CSV file first.', 'vietnix-csv-import'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo intval($item->id); ?></td>
                        <td>
                            <strong><?php echo esc_html($item->product_name); ?></strong>
                            <?php if ($item->description): ?>
                                <div class="row-actions">
                                    <span><?php echo esc_html(wp_trim_words($item->description, 10)); ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($item->usage_time); ?></td>
                        <td><?php echo esc_html($item->time_label_sale); ?></td>
                        <td><?php echo esc_html($item->time_label); ?></td>
                        <td><?php echo esc_html($item->server_name); ?></td>
                        <td><?php echo esc_html($item->server_sale); ?></td>
                        <td>
                            <strong><?php echo vietnix_csv_format_price($item->price, $item->currency); ?></strong>
                        </td>
                        <td><?php echo vietnix_csv_format_price($item->price_sale, $item->currency); ?></td>
                        <td><?php echo esc_html($item->product_sku); ?></td> 
                        
                        <td style="max-width: 200px; white-space: normal; overflow-wrap: break-word;">
                            <?php echo esc_html(wp_trim_words($item->product_content, 10, '...')); ?>
                        </td>
                        <td><?php echo esc_html($item->price_addon_code); ?></td>
                        <td><?php echo esc_html($item->product_label_special); ?></td>
                        <td><?php echo esc_html($item->product_label_featured); ?></td>
                        <td><?php echo esc_url($item->product_cta_link); ?></td>
                        <td>
                            <?php echo date_i18n(get_option('date_format'), strtotime($item->created_at)); ?>
                            <br>
                            <small><?php echo date_i18n(get_option('time_format'), strtotime($item->created_at)); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $pagination_args = array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo; Previous'),
                    'next_text' => __('Next &raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page,
                    'type' => 'plain',
                    'add_args' => array_filter(array(
                        's' => $search,
                        // 'category' => $category_filter,
                        // 'status' => $status_filter
                    ))
                );

                echo paginate_links($pagination_args);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <?php if (!empty($items)): ?>
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="title-handle"><?php _e('Summary Statistics', 'vietnix-csv-import'); ?></h2>
            <div class="inside">
                <?php
                $total_value = 0;
                $active_count = 0;
                $total_stock = 0;

                foreach ($items as $item) {
                    $total_value += $item->price;
                    if ($item->status === 'active') {
                        $active_count++;
                    }
                    $total_stock += $item->stock_quantity;
                }
                ?>

                <div class="vietnix-stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($total_items); ?></div>
                        <div class="stat-label"><?php _e('Total Items', 'vietnix-csv-import'); ?></div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($active_count); ?></div>
                        <div class="stat-label"><?php _e('Active Items', 'vietnix-csv-import'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>