# 🚀 Quick Setup Guide

> Hướng dẫn cài đặt nhanh sau khi clone repository

## 📋 Prerequisites

Đảm bảo bạn đã cài đặt:

- **Node.js** 16+ ([Download](https://nodejs.org/))
- **Sass** ([Install guide](https://sass-lang.com/install))
- **WordPress** 5.0+ environment

## ⚡ Quick Start (3 minutes)

### 1. Clone và Install
```bash
# Clone repository
git clone <your-repo-url>
cd vietnix_import_csv_plugin

# Install dependencies
npm install

# Install Sass globally (nếu chưa có)
npm install -g sass
```

### 2. Build Assets
```bash
# Build SCSS files
npm run scss:build

# Build Vue.js (optional, for development)
npm run build
```

### 3. Upload to WordPress
```bash
# Copy plugin to WordPress
cp -r . /path/to/wordpress/wp-content/plugins/vietnix_import_csv_plugin/

# Or zip and upload via WordPress admin
zip -r vietnix_import_csv_plugin.zip . -x "node_modules/*" "*.git/*" "*.DS_Store"
```

### 4. Activate Plugin
1. Go to WordPress Admin → **Plugins**
2. Find **Vietnix CSV Import Plugin**
3. Click **Activate**
4. Access **Vietnix CSV Import** in admin menu

## 🛠️ Development Mode

### SCSS Development:
```bash
# Watch SCSS files (auto-compile on change)
npm run scss:watch

# Edit files in: assets/admin/scss/
# Output CSS in: assets/css/
```

### Vue.js Development:
```bash
# Development mode with hot reload
npm run dev

# Build for production
npm run build
```

## 🗂️ Important Files

| File | Purpose |
|------|---------|
| `assets/admin/scss/` | **SCSS source files** (edit these) |
| `assets/css/` | **Generated CSS** (don't edit directly) |
| `assets/admin/js/` | **Vue.js components** |
| `package.json` | **Dependencies and scripts** |

## 🔧 Available Commands

```bash
npm run dev          # Start Vite dev server
npm run build        # Build Vue.js for production
npm run scss:build   # Compile SCSS once
npm run scss:watch   # Watch SCSS files
npm run watch        # Watch mode for development
```

## ❗ Troubleshooting

### SCSS not compiling?
```bash
# Check Sass installation
sass --version

# Reinstall if needed
npm uninstall -g sass
npm install -g sass
```

### Node.js issues?
```bash
# Check Node version
node --version

# Should be 16+ for best compatibility
```

### Permission errors?
```bash
# Make scripts executable
chmod +x *.sh
```

## 📞 Need Help?

- 📚 **Full Documentation**: [DEVELOPMENT.md](./DEVELOPMENT.md)
- 🐛 **Issues**: Create GitHub issue
- 💬 **Support**: support@vietnix.vn

---

**🎉 Happy coding!**
