<?php
/**
 * Plugin Name: Vietnix Import CSV Table Price
 * Plugin URI: https://vietnix.vn
 * Description: Plugin import file CSV vào custom table và hiển thị bằng shortcode.
 * Version: 1.0.3
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

// Load core files
require_once plugin_dir_path(__FILE__) . 'core/Constant.php';
require_once plugin_dir_path(__FILE__) . 'core/Plugin.php';

use VietnixImportCsv\Core\Constant;
use VietnixImportCsv\Core\Plugin;

/**
 * Class chính của plugin
 */

try {
  Constant::define_Constants(); // Sửa tên phương thức

  $plugin_instance = Plugin::get_instance();
  $plugin_instance->init();
  
  // Đăng ký activation hook
  register_activation_hook(__FILE__, array($plugin_instance, 'activate'));
  
} catch (\Exception $e) {
  error_log($e->getMessage());
}

/**
 * Legacy class for backward compatibility
 */
class VietnixCSVImport { 
    public function __construct() {
        // This class is kept for backward compatibility
        // All functionality is now handled by the Plugin class
    }
}

// Khởi tạo plugin legacy class (để tương thích với shortcode cũ)
new VietnixCSVImport();
