<?php
/**
 * Example usage of VietnixCSVDataTable class
 */

// Khởi tạo class
$data_table = new VietnixCSVDataTable();

// 1. Lấy dữ liệu với pagination và search
$result = $data_table->get_data(array(
    'page' => 1,
    'per_page' => 20,
    'search' => 'VPS',
    'order_by' => 'product_name',
    'order' => 'ASC'
));

$items = $result['items'];
$pagination = $result['pagination'];

// 2. Lấy thống kê
$stats = $data_table->get_statistics();
echo "Total items: " . $stats['total_items'];
echo "Last import: " . $stats['last_import'];

// 3. Lấy options cho filter
$filters = $data_table->get_filter_options();
$servers = $filters['servers'];
$usage_times = $filters['usage_times'];

// 4. Format dữ liệu
foreach ($items as $item) {
    echo $data_table->format_price($item->price, $item->currency);
    echo $data_table->format_date($item->created_at);
}

// 5. Xóa items
$data_table->delete_item(123);
$data_table->delete_items(array(1, 2, 3));

// 6. Clear all data
$data_table->clear_all_data();

// 7. Cấu hình per_page
$data_table->set_per_page(50);
?>
