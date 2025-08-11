# Hướng dẫn cài đặt môi trường Development

## 🔧 Cài đặt Prerequisites

### Option 1: Cài đặt Homebrew (Recommended)
```bash
# Cài đặt Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Sau đó cài Node.js
brew install node

# Cài Sass
npm install -g sass
```

### Option 2: Cài đặt Node.js trực tiếp
1. Tải Node.js từ: https://nodejs.org/en/download/
2. Chọn phiên bản LTS cho macOS
3. Cài đặt file .pkg đã tải
4. Restart terminal
5. Kiểm tra: `node --version` và `npm --version`
6. Cài Sass: `npm install -g sass`

### Option 3: Sử dụng Ruby Sass (Nếu có Ruby)
```bash
# Kiểm tra Ruby
ruby --version

# Cài Sass qua gem
gem install sass
```

## 📋 Sau khi cài đặt xong

1. **Kiểm tra installation:**
   ```bash
   node --version
   npm --version  
   sass --version
   ```

2. **Clone/navigate to plugin directory:**
   ```bash
   cd /path/to/vietnix_import_csv_plugin
   ```

3. **Install dependencies:**
   ```bash
   npm install
   ```

4. **Start development:**
   ```bash
   # Compile SCSS once
   npm run scss:build
   
   # Watch SCSS files
   npm run scss:watch
   ```

## 🎯 Nếu không muốn cài Node.js

Bạn có thể sử dụng SCSS compiler online hoặc VS Code extensions:

### VS Code Extensions:
1. **Live Sass Compiler** - auto compile SCSS to CSS
2. **Sass** - syntax highlighting
3. **SCSS IntelliSense** - autocomplete

### Online SCSS Compilers:
1. https://www.sassmeister.com/
2. https://codepen.io/ (có SCSS processor)
3. https://jsonformatter.org/scss-to-css

## 🚀 Quick Test

Sau khi cài đặt, test compile một file:
```bash
# Test compile import.scss
sass assets/admin/scss/import.scss assets/css/import-page.css

# Check if file was created
ls -la assets/css/import-page.css
```
