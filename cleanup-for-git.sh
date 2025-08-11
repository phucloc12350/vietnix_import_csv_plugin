#!/bin/bash

# Script to clean up files before git commit
# Usage: ./cleanup-for-git.sh

echo "🧹 Cleaning up files for git commit..."

# Remove Node.js dependencies
if [ -d "node_modules" ]; then
    echo "📦 Removing node_modules..."
    rm -rf node_modules/
fi

# Remove package lock files
if [ -f "package-lock.json" ]; then
    echo "🔒 Removing package-lock.json..."
    rm -f package-lock.json
fi

if [ -f "yarn.lock" ]; then
    echo "🔒 Removing yarn.lock..."
    rm -f yarn.lock
fi

# Remove OS generated files
echo "💻 Removing OS files..."
find . -name ".DS_Store" -delete
find . -name ".DS_Store?" -delete
find . -name "._*" -delete
find . -name ".Spotlight-V100" -delete
find . -name ".Trashes" -delete
find . -name "ehthumbs.db" -delete
find . -name "Thumbs.db" -delete

# Remove log files
echo "📝 Removing log files..."
find . -name "*.log" -delete
find . -name "npm-debug.log*" -delete
find . -name "yarn-debug.log*" -delete
find . -name "yarn-error.log*" -delete

# Remove backup files
echo "💾 Removing backup files..."
find . -name "*-old.*" -delete
find . -name "*-backup.*" -delete
find . -name "*-bak.*" -delete

# Remove compiled CSS files (they should be built from SCSS)
echo "🎨 Removing compiled CSS files..."
rm -f assets/css/admin.css
rm -f assets/css/import-page.css
rm -f assets/css/admin-view-data.css
rm -f assets/css/public.css

# Remove source maps
echo "🗺️ Removing source maps..."
find . -name "*.css.map" -delete
find . -name "*.js.map" -delete

# Remove temporary files
echo "🗑️ Removing temporary files..."
find . -name "*.tmp" -delete
find . -name "*.temp" -delete
rm -rf .cache/

# Remove IDE files
echo "💡 Removing IDE files..."
rm -rf .vscode/
rm -rf .idea/
find . -name "*.swp" -delete
find . -name "*.swo" -delete
find . -name "*~" -delete

# Remove Sass cache
echo "🎨 Removing Sass cache..."
rm -rf .sass-cache/

# Remove dist/build directories
echo "🏗️ Removing build directories..."
rm -rf dist/
rm -rf build/
rm -rf assets/js/dist/
rm -rf assets/css/dist/

echo "✅ Cleanup completed!"
echo ""
echo "📋 Summary of removed items:"
echo "   - Node.js dependencies (node_modules)"
echo "   - Package lock files"
echo "   - OS generated files (.DS_Store, etc.)"
echo "   - Log files"
echo "   - Backup files (*-old.*, etc.)"
echo "   - Compiled CSS files (will be built from SCSS)"
echo "   - Source maps"
echo "   - IDE configuration files"
echo "   - Temporary and cache files"
echo ""
echo "🚀 Your project is ready for git commit!"
echo "💡 Remember to run 'npm install && npm run scss:build' after cloning"
