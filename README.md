# Vietnix CSV Import Plugin

> 🚀 **Modern WordPress Plugin** để import dữ liệu CSV với Vue.js và SCSS, hỗ trợ hiển thị bảng giá sản phẩm chuyên nghiệp

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2+-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 📝 Mô tả

Plugin WordPress chuyên nghiệp để import dữ liệu từ file CSV và hiển thị bảng giá sản phẩm với giao diện hiện đại. Plugin được xây dựng với **Vue.js 3** cho admin interface và **SCSS** cho styling, cung cấp trải nghiệm người dùng mượt mà và responsive.

## ✨ Tính năng chính

### 🔧 Admin Dashboard (Vue.js)
- **📁 Import CSV**: Drag & drop upload với validation và preview
- **📊 Quản lý dữ liệu**: Xem, tìm kiếm, phân trang với Vue reactivity
- **🖼️ Download hình ảnh**: Tự động tải xuống từ URL và lưu vào Media Library
- **📈 Thống kê**: Dashboard với biểu đồ và metrics
- **⚙️ Cài đặt**: Tùy chỉnh plugin với settings panel

### 🎨 Frontend Display
- **📑 Tab phân cấp**: Server name (tab cha) → Usage time (tab con)
- **💳 Product cards**: Hiển thị pricing với animations
- **📋 Copy functionality**: Sao chép mã addon với feedback UI
- **📱 Responsive design**: Mobile-first approach

### 🗄️ Database Schema
Bảng `wp_vietnix_price_data` với các trường:
```sql
product_name, product_sku, price, price_sale, currency,
price_addon_sale, price_addon_code, product_label_special,
product_label_featured, product_content, usage_time,
time_label_sale, time_label, server_name, server_sale,
product_cta_link, description, created_at, updated_at
```

## � Cài đặt và Sử dụng

### 1. Clone Repository
```bash
git clone https://github.com/phucloc12350/vietnix_import_csv_plugin.git
cd vietnix_import_csv_plugin
```

### 2. Cài đặt Dependencies
```bash
# Install Node.js dependencies
npm install

# Install Sass globally (nếu chưa có)
npm install -g sass
```

### 3. Build Assets

#### SCSS Development:
```bash
# Compile SCSS một lần
npm run scss:build

# Watch SCSS files for changes
npm run scss:watch

# Hoặc dùng shell scripts
./build-scss.sh
./watch-scss.sh
```

#### Vue.js Development:
```bash
# Development mode
npm run dev

# Build for production
npm run build

# Watch mode
npm run watch
```

### 4. Upload to WordPress
1. Upload toàn bộ thư mục plugin vào `/wp-content/plugins/`
2. Activate plugin trong WordPress Admin
3. Truy cập **Vietnix CSV Import** trong menu admin

## 📁 Cấu trúc Project

```
vietnix_import_csv_plugin/
├── 📄 README.md                           # Tài liệu chính
├── 📄 DEVELOPMENT.md                      # Hướng dẫn development
├── 📄 INSTALLATION.md                     # Hướng dẫn cài đặt
├── 📄 package.json                        # Node.js dependencies
├── 📄 webpack.config.js                   # Webpack configuration
├── 🔧 vietnix-csv-import.php             # Main plugin file
├── 🌐 languages/                          # i18n files
├── 📁 assets/                             # Frontend assets
│   ├── 🎨 admin/                         # Admin assets
│   │   ├── scss/                         # SCSS source files
│   │   │   ├── _variables.scss           # SCSS variables
│   │   │   ├── _mixins.scss              # SCSS mixins
│   │   │   ├── main.scss                 # Main admin styles
│   │   │   ├── import.scss               # Import page styles
│   │   │   ├── view-data.scss            # Data view styles
│   │   │   └── public.scss               # Frontend styles
│   │   └── js/                           # Vue.js source
│   │       ├── admin/                    # Admin modules
│   │       │   ├── admin.js              # Main admin (Vue.js)
│   │       │   ├── import.js             # Import functionality
│   │       │   └── view-data.js          # Data table (Vue.js)
│   │       └── admin.js                  # Entry point
│   ├── 🎯 css/                           # Compiled CSS (auto-generated)
│   │   ├── admin.css                     # ← từ main.scss
│   │   ├── import-page.css               # ← từ import.scss
│   │   ├── admin-view-data.css           # ← từ view-data.scss
│   │   └── public.css                    # ← từ public.scss
│   ├── 📜 js/                            # Compiled JS
│   │   ├── admin.js                      # Admin scripts
│   │   └── public.js                     # Frontend scripts
│   └── 🖼️ image/                         # Static images
│       ├── icon_copy.png                 # Copy icon
│       ├── icon_sale.png                 # Sale badge
│       ├── label-time.png                # Time label
│       └── recom.png                     # Recommendation badge
├── 🏗️ includes/                          # Core PHP classes
│   ├── functions.php                     # Helper functions
│   ├── admin/                            # Admin functionality
│   │   ├── class-admin.php               # Admin controller
│   │   └── views/                        # Admin templates
│   │       ├── import-page.php           # Import interface
│   │       ├── settings-page.php         # Settings panel
│   │       └── view-data.php             # Data management
│   └── public/                           # Frontend functionality
│       ├── class-public.php              # Public controller
│       └── views/                        # Frontend templates
│           └── price-table.php           # Price table template
└── 🗂️ core/                              # Core system files
    └── class-database.php                # Database operations
```

## 🔧 Development Commands

| Command | Description |
|---------|-------------|
| `npm run dev` | 🛠️ Start Vite dev server |
| `npm run build` | 🏗️ Build for production |
| `npm run watch` | 👁️ Watch mode for development |
| `npm run scss:build` | 🎨 Compile SCSS once |
| `npm run scss:watch` | 👀 Watch SCSS files |

## 🎯 Workflow Development

### Frontend Development:
```bash
# 1. Start SCSS watching
npm run scss:watch

# 2. Edit SCSS files in assets/admin/scss/
# 3. CSS files auto-generated in assets/css/
# 4. WordPress tự động load CSS
```

### Vue.js Development:
```bash
# 1. Start Vue development
npm run dev

# 2. Edit Vue components in assets/admin/js/
# 3. Build for production
npm run build
```

## 📦 Files Không Cần Commit

**Đã được exclude trong .gitignore:**

### 📁 Dependencies:
- `node_modules/` - Node.js packages
- `vendor/` - Composer packages
- `package-lock.json` - Lock file

### 🏗️ Build outputs:
- `dist/` - Vite build output
- `assets/css/*.css` - Compiled CSS from SCSS
- `*.css.map` - Source maps

### 🛠️ Development files:
- `.DS_Store` - macOS files
- `*.log` - Log files
- `.vscode/`, `.idea/` - IDE settings
- `*-old.*`, `*-backup.*` - Backup files

### ⚠️ Files to Delete Before Git:
```bash
# Remove development artifacts
rm -rf node_modules/
rm -rf .DS_Store
rm -rf assets/css/*.css
rm -rf *.log
rm -rf *-old.*
```

## 🔧 Tech Stack

- **Backend**: PHP 7.4+, WordPress 5.0+
- **Frontend**: Vue.js 3, Vanilla JavaScript
- **Styling**: SCSS, CSS3
- **Build Tools**: Webpack, Vite, Sass
- **Database**: MySQL (WordPress tables)

## 📋 Requirements

- **WordPress**: 5.0 hoặc cao hơn
- **PHP**: 7.4 hoặc cao hơn
- **Node.js**: 16+ (cho development)
- **Sass**: 1.32+ (cho SCSS compilation)

## 🤝 Đóng góp

1. Fork project
2. Tạo feature branch: `git checkout -b feature/AmazingFeature`
3. Commit changes: `git commit -m 'Add AmazingFeature'`
4. Push to branch: `git push origin feature/AmazingFeature`
5. Mở Pull Request

## 📄 License

Distributed under the GPL v2+ License. See `LICENSE` for more information.

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-username/vietnix_import_csv_plugin/issues)
- **Documentation**: [Wiki](https://github.com/your-username/vietnix_import_csv_plugin/wiki)
- **Email**: support@vietnix.vn

---

**⭐ Nếu project hữu ích, hãy give star để support nhé!**

