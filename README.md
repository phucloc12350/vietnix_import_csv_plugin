# Vietnix CSV Import Plugin

## 📝 Mô tả
Plugin WordPress chuyên nghiệp để import dữ liệu từ file CSV và hiển thị bảng giá sản phẩm với giao diện tab phân cấp. Plugin hỗ trợ tải xuống và quản lý hình ảnh từ URL, cung cấp shortcode linh hoạt để hiển thị dữ liệu ở frontend.

## ✨ Tính năng chính

### 🔧 Admin Dashboard
- **Import CSV**: Upload và xử lý file CSV với validation
- **Quản lý dữ liệu**: Xem, tìm kiếm, phân trang dữ liệu đã import
- **Download hình ảnh**: Tự động tải xuống hình ảnh từ URL và lưu vào Media Library
- **Thống kê**: Hiển thị số liệu tổng quan về dữ liệu

### 🎨 Frontend Display
- **Tab phân cấp**: Server name làm tab cha, usage time làm tab con
- **Product cards**: Hiển thị thông tin sản phẩm đầy đủ với pricing
- **Copy functionality**: Sao chép mã addon với feedback UI
- **Responsive design**: Tương thích mọi thiết bị

### 🗄️ Database Schema
Bảng `wp_vietnix_price_data` với 19 columns:
- `product_name`, `product_sku`, `price`, `price_sale`, `currency`
- `price_addon_sale`, `price_addon_code`, `product_label_special`
- `product_label_featured`, `product_content`, `usage_time`
- `time_label_sale`, `time_label`, `server_name`, `server_sale`
- `product_cta_link`, `description`, `created_at`, `updated_at`

## 📁 Cấu trúc thư mục

```
vietnix_import_csv_table_price/
├── README.md                              # Tài liệu plugin
├── vietnix-csv-import.php                 # File chính của plugin
├── languages/                             # Thư mục ngôn ngữ (i18n)
├── assets/                                # Tài nguyên static
│   ├── css/                              # Stylesheets
│   │   ├── admin.css                     # CSS cho admin dashboard
│   │   ├── admin-view-data.css           # CSS cho trang xem dữ liệu
│   │   ├── base.css                      # CSS cơ bản chung
│   │   ├── import-page.css               # CSS cho trang import
│   │   └── public.css                    # CSS cho frontend (768 lines)
│   ├── js/                               # JavaScript files
│   │   ├── admin.js                      # JS cho admin (523 lines)
│   │   └── public.js                     # JS cho frontend (372 lines)
│   └── image/                            # Hình ảnh assets
│       ├── icon_copy.png                 # Icon copy cho addon codes
│       ├── icon_sale.png                 # Icon sale cho pricing
│       ├── label-time.png                # Label time indicator
│       └── recom.png                     # Recommendation badge
└── includes/                             # Core PHP files
    ├── functions.php                     # Helper functions
    ├── admin/                            # Admin functionality
    │   ├── class-admin.php               # Admin class chính
    │   └── views/                        # Admin view templates
    │       ├── import-page.php           # Trang import CSV
    │       ├── settings-page.php         # Trang cài đặt
    │       └── view-data.php             # Trang xem dữ liệu
    └── public/                           # Frontend functionality
        ├── class-public.php              # Public class chính
        └── views/                        # Frontend templates
            └── price-table.php           # Template hiển thị bảng giá
```
