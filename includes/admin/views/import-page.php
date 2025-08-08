<?php
$data_table = new VietnixCSVDataTable();    
$total_items = $data_table->get_total_items();
$last_import = $data_table->last_import();
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="vietnix-csv-import-page">
        <div class="postbox">
            <h2 class="title-handle"><?php _e('Import CSV File', 'vietnix-csv-import'); ?></h2>
            <div class="inside">
                <form id="vietnix-csv-import-form" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('vietnix_csv_import_nonce', 'vietnix_csv_nonce'); ?>
                    
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="csv_file"><?php _e('CSV File', 'vietnix-csv-import'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required />
                                    <p class="description">
                                        <?php _e('Chọn file với định dạng CSV. Dung lượng tối đa: 10MB', 'vietnix-csv-import'); ?>
                                         
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="clear_existing"><?php _e('Xoá hết dữ liệu tồn tại', 'vietnix-csv-import'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="clear_existing" name="clear_existing" value="1" />
                                    <label for="clear_existing"><?php _e('Xoá hết dữ liệu tồn tại trước khi nhập', 'vietnix-csv-import'); ?></label>
                                    <p class="description"><?php _e('Cảnh báo: Hành động này sẽ xóa vĩnh viễn tất cả dữ liệu giá hiện tại.', 'vietnix-csv-import'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="download_images"><?php _e('Tải hình ảnh về', 'vietnix-csv-import'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="download_images" name="download_images" value="1" checked />
                                    <label for="download_images"><?php _e('Tự động tải hình ảnh từ URL về Media Library', 'vietnix-csv-import'); ?></label>
                                    <p class="description">
                                        <?php _e('Nếu bật, plugin sẽ tự động tải các hình ảnh từ URL trong cột product_content về Media Library và thay thế bằng URL mới.', 'vietnix-csv-import'); ?>
                                        <br>
                                        <strong><?php _e('Lưu ý:', 'vietnix-csv-import'); ?></strong> <?php _e('Quá trình này có thể mất thời gian nếu có nhiều hình ảnh.', 'vietnix-csv-import'); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Import CSV', 'vietnix-csv-import'); ?>" />
                        <span class="spinner"></span>
                    </p>
                </form>
            </div>
        </div> 
        
        <?php
        // Hiển thị statistics nếu có data 
        if ($total_items > 0):
        ?>
        <div class="postbox">
            <h2 class="title-handle"><?php _e('Current Data Statistics', 'vietnix-csv-import'); ?></h2>
            <div class="inside"> 
                
                <div class="vietnix-stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format( $total_items); ?></div>
                        <div class="stat-label"><?php _e('Total Items', 'vietnix-csv-import'); ?></div>
                    </div>   
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $last_import ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_import)) : __('N/A', 'vietnix-csv-import'); ?></div>
                        <div class="stat-label"><?php _e('Last Import', 'vietnix-csv-import'); ?></div>
                    </div>
                    <div class="danger-zone">
                    <h3><?php _e('Danger Zone', 'vietnix-csv-import'); ?></h3>
                    <p><?php _e('The following action cannot be undone.', 'vietnix-csv-import'); ?></p>
                    <button type="button" id="delete-all-data" class="button button-secondary"><?php _e('Delete All Data', 'vietnix-csv-import'); ?></button>
                </div>
                </div>
                
                
            </div>
        </div>
        <?php endif; ?>
        
        <div class="postbox">
            <h2 class="title-handle"><?php _e('Cách sử dụng', 'vietnix-csv-import'); ?></h2>
            <div class="inside">
                <h3><?php _e('Sau khi Import thành công.', 'vietnix-csv-import'); ?></h3>
                <p><?php _e('Bạn có thể hiển thị dữ liệu đó trên bất kỳ trang hoặc bài đăng nào bằng cách sử dụng mã ngắn:', 'vietnix-csv-import'); ?></p>
                <code>[vietnix_price_table]</code> 
            </div>
        </div>
    </div>
</div>
