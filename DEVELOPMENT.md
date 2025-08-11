# Vietnix CSV Import Plugin - Development Guide

## 📋 Prerequisites

1. **Node.js & npm** (for SCSS compilation and Vue.js)
   ```bash
   # Install via Homebrew (macOS)
   brew install node
   
   # Or download from: https://nodejs.org/
   ```

2. **Sass** (for SCSS compilation)
   ```bash
   # Install globally
   npm install -g sass
   
   # Or via gem (if you have Ruby)
   gem install sass
   ```

## 🚀 Quick Start

### 1. Install Dependencies
```bash
npm install
```

### 2. SCSS Development

#### Compile SCSS once:
```bash
# Using npm script
npm run scss:build

# Or using shell script
./build-scss.sh
```

#### Watch SCSS files for changes:
```bash
# Using npm script  
npm run scss:watch

# Or using shell script
./watch-scss.sh
```

#### Manual compilation:
```bash
# Compile specific file
sass assets/admin/scss/import.scss assets/css/import-page.css --style=compressed

# Watch specific file
sass --watch assets/admin/scss/import.scss:assets/css/import-page.css
```

### 3. Vue.js Development

#### Development mode:
```bash
npm run dev
```

#### Build for production:
```bash
npm run build
```

#### Watch mode:
```bash
npm run watch
```

## 📁 Project Structure

```
assets/
├── admin/
│   ├── scss/
│   │   ├── _variables.scss    # SCSS variables
│   │   ├── _mixins.scss       # SCSS mixins  
│   │   ├── main.scss          # Main admin styles
│   │   ├── import.scss        # Import page styles
│   │   ├── view-data.scss     # View data page styles
│   │   └── public.scss        # Frontend styles
│   └── js/
│       └── main.js            # Vue.js entry point
├── css/                       # Compiled CSS output
│   ├── admin.css
│   ├── import-page.css
│   ├── admin-view-data.css
│   └── public.css
├── js/
│   ├── admin.js               # Admin JavaScript
│   └── public.js              # Public JavaScript
└── dist/                      # Vite build output
```

## 🎨 SCSS Usage

### Variables
```scss
// Use predefined variables
.my-component {
  color: $primary-color;
  padding: $spacing-md;
  background: $background-light;
}
```

### Mixins
```scss
// Use mixins for common patterns
.card {
  @include card-style;
}

.grid {
  @include grid-responsive(250px);
}

.center-content {
  @include flex-center;
}
```

### Responsive Design
```scss
.my-component {
  // Mobile first
  width: 100%;
  
  // Tablet and up
  @include respond-to('md') {
    width: 50%;
  }
  
  // Desktop and up
  @include respond-to('lg') {
    width: 33.333%;
  }
}
```

## 🔧 Available Scripts

| Script | Description |
|--------|-------------|
| `npm run dev` | Start Vite dev server |
| `npm run build` | Build for production |
| `npm run watch` | Watch mode for development |
| `npm run scss:build` | Compile SCSS once |
| `npm run scss:watch` | Watch SCSS files |
| `./build-scss.sh` | Shell script to compile SCSS |
| `./watch-scss.sh` | Shell script to watch SCSS |

## 📝 Development Workflow

1. **Start watching SCSS:**
   ```bash
   npm run scss:watch
   ```

2. **Edit SCSS files** in `assets/admin/scss/`

3. **CSS files are auto-generated** in `assets/css/`

4. **WordPress automatically loads** the CSS files

## 🎯 File Mapping

| SCSS Source | CSS Output | Usage |
|-------------|------------|-------|
| `assets/admin/scss/main.scss` | `assets/css/admin.css` | Admin dashboard |
| `assets/admin/scss/import.scss` | `assets/css/import-page.css` | Import page |
| `assets/admin/scss/view-data.scss` | `assets/css/admin-view-data.css` | View data page |
| `assets/admin/scss/public.scss` | `assets/css/public.css` | Frontend shortcode |

## 🚨 Important Notes

1. **Never edit CSS files directly** - they are generated from SCSS
2. **Always compile SCSS** before committing changes
3. **Use variables and mixins** for consistency
4. **Follow BEM methodology** for CSS class naming
5. **Test responsive design** on different screen sizes

## 🔍 Troubleshooting

### SCSS not compiling?
```bash
# Check if sass is installed
sass --version

# Install if missing
npm install -g sass
```

### Node.js issues?
```bash
# Check Node version (should be 16+)
node --version

# Update if needed
brew upgrade node
```

### File permission issues?
```bash
# Make scripts executable
chmod +x build-scss.sh
chmod +x watch-scss.sh
```
