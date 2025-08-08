<?php
// Initialize class và prepare data
$dataViewPublic = new VietnixCSVPublic();
$table_data = $dataViewPublic->prepare_table_data($atts);

// Extract data
$table_id = $table_data['table_id'];
$items = $table_data['items'];
$total_items = $table_data['total_items'];
$display_columns = $table_data['display_columns'];
$server_sales = $table_data['server_sales'];
$time_label_sales = $table_data['time_label_sales'];
$time_label = $table_data['time_label'];
$server_names = $table_data['server_names'];
$server_usage_data = $table_data['server_usage_data'];

?>

<div class="vietnix-price-table-wrapper vietnix-style-<?php echo esc_attr($table_style); ?>"
    id="<?php echo esc_attr($table_id); ?>">

    <!-- Server Tabs (Parent) -->
    <div class="vietnix-server-tabs">
        <div class="vietnix-server-tabs-nav">

            <?php
            $first_server = true;
            foreach ($server_usage_data as $server_name => $usage_times): ?>
                <button class="vietnix-server-tab <?php echo $first_server ? 'active' : ''; ?>"
                    data-server="<?php echo esc_attr($server_name); ?>">
                    <?php
                    if (!empty($server_sales[$server_name])) {
                        foreach ($server_sales[$server_name] as $sale) {
                            echo '<span class="vietnix-server-sale">' . esc_html($sale) . '</span>';
                        }
                    }
                    ?>
                    <?php echo esc_html($server_name); ?>
                </button>
                <?php
                $first_server = false;
            endforeach; ?>

        </div>
        <div class="vietnix-usage-tabs-wrap">
            <!-- Usage Time Tabs (Children) -->
            <?php foreach ($server_usage_data as $server_name => $usage_times): ?>
                <div class="vietnix-usage-tabs <?php echo $first_server ? '' : 'active'; ?>"
                    data-server-content="<?php echo esc_attr($server_name); ?>">
                    <div class="vietnix-usage-tabs-nav">
                        <?php
                        $first_usage = true;
                        foreach ($usage_times as $usage_time): ?>

                            <button class="vietnix-usage-tab btn <?php echo $first_usage ? 'active' : ''; ?>"
                                data-server="<?php echo esc_attr($server_name); ?>"
                                data-usage="<?php echo esc_attr($usage_time); ?>">
                                <?php
                                // show time label
                                if (isset($time_label[$server_name][$usage_time]) && !empty($time_label[$server_name][$usage_time])) {
                                    foreach ($time_label[$server_name][$usage_time] as $label) {
                                        echo '<span class="vietnix-usage-time-label"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>' . esc_html($label) . '</span>';
                                    }
                                }
                                ?>
                                <?php echo esc_html($usage_time); ?>
                                <!-- time label sale -->
                                <?php
                                if (isset($time_label_sales[$usage_time]) && !empty($time_label_sales[$usage_time])) {
                                    foreach ($time_label_sales[$usage_time] as $sale) {
                                        echo '<span class="vietnix-usage-time-label-sale">' . esc_html($sale) . '</span>';
                                    }
                                }
                                ?>

                            </button>
                            <?php
                            $first_usage = false;
                        endforeach; ?>
                    </div>
                </div>
                <?php
                $first_server = true;
            endforeach; ?>
        </div>
    </div>
    <!-- Table Container -->
    <div class="vietnix-table-container">
        <div class="vietnix-products-grid" id="<?php echo esc_attr($table_id); ?>-products">
            <!-- Products will be loaded via AJAX based on selected server and usage time -->
            <div class="vietnix-loading">
                <div class="loading-spinner"></div>
                <p><?php _e('Loading products...', 'vietnix-csv-import'); ?></p>
            </div>
        </div>
    </div>
    <!-- Hidden data for JavaScript -->
    <script type="application/json" class="vietnix-table-config">
    {
        "tableId": "<?php echo esc_js($table_id); ?>",
        "columns": <?php echo wp_json_encode(array_keys($display_columns)); ?>,
        "ajaxUrl": "<?php echo admin_url('admin-ajax.php'); ?>",
        "nonce": "<?php echo wp_create_nonce('vietnix_csv_nonce'); ?>",
        "serverUsageData": <?php echo wp_json_encode($server_usage_data); ?>,
        "defaultServer": "<?php echo esc_js(key($server_usage_data)); ?>",
        "defaultUsage": "<?php echo esc_js(reset($server_usage_data)[0] ?? ''); ?>",
        "VIETNIX_CSV_URL": "<?php echo esc_js(VIETNIX_CSV_URL); ?>"
    }
    </script>
</div>