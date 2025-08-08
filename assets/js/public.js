/**
 * Public JavaScript for Vietnix CSV Import Plugin
 */
(function($) {
    'use strict';
    
    // Global variables
    let vietnixTables = {};
    
    // Initialize when document ready
    $(document).ready(function() {
        initPriceTables(); 
    });
 
    // Initialize all price tables on the page
    function initPriceTables() {
        $('.vietnix-price-table-wrapper').each(function() {
            const $wrapper = $(this);
            const configScript = $wrapper.find('.vietnix-table-config');
            
            if (configScript.length) {
                try {
                    const config = JSON.parse(configScript.text());
                    vietnixTables[config.tableId] = config;
                    initTable($wrapper, config);
                } catch (e) {
                    console.error('Failed to parse table config:', e);
                }
            }
        });
    }
    window.VietnixConfig = {
        getConfig: function(tableId) {
            return vietnixTables[tableId] || null;
        },
        
        getAllConfigs: function() {
            return vietnixTables;
        },
        
        getPluginUrl: function(tableId) {
            // Nếu không có tableId, lấy config đầu tiên có sẵn
            if (!tableId) {
                const firstTableId = Object.keys(vietnixTables)[0];
                if (firstTableId) {
                    return vietnixTables[firstTableId].VIETNIX_CSV_URL || '';
                }
                return '';
            }
            
            const config = this.getConfig(tableId);
            return config ? config.VIETNIX_CSV_URL : '';
        },
        
        // Thêm function helper để lấy plugin URL dễ dàng hơn
        getAssetUrl: function(path, tableId) {
            const pluginUrl = this.getPluginUrl(tableId);
            return pluginUrl ? pluginUrl + path : '';
        }
    };
    // Initialize individual table
    function initTable($wrapper, config) {
        const tableId = config.tableId;
        
        // Initialize tabs
        initTabs($wrapper, config);
        
        // Store current state
        vietnixTables[tableId].filters = {
            server: config.defaultServer || '',
            usage: config.defaultUsage || ''
        };
        
        // Load initial data
        loadTableData($wrapper, config);
    }
    
    // Tab functionality
    function initTabs($wrapper, config) {
        // Server tabs
        $wrapper.on('click', '.vietnix-server-tab', function() {
            const $tab = $(this);
            const server = $tab.data('server');
            
            // Update active server tab
            $wrapper.find('.vietnix-server-tab').removeClass('active');
            $tab.addClass('active');
            
            // Show corresponding usage tabs
            $wrapper.find('.vietnix-usage-tabs').removeClass('active');
            $wrapper.find(`.vietnix-usage-tabs[data-server-content="${server}"]`).addClass('active');
            
            // Reset to first usage time for this server
            const $firstUsageTab = $wrapper.find(`.vietnix-usage-tabs[data-server-content="${server}"] .vietnix-usage-tab:first`);
            $wrapper.find('.vietnix-usage-tab').removeClass('active');
            $firstUsageTab.addClass('active');
            
            // Update filters and reload data
            vietnixTables[config.tableId].filters.server = server;
            vietnixTables[config.tableId].filters.usage = $firstUsageTab.data('usage') || '';
            
            loadTableData($wrapper, config);
        });
        
        // Usage time tabs
        $wrapper.on('click', '.vietnix-usage-tab', function() {
            const $tab = $(this);
            const server = $tab.data('server');
            const usage = $tab.data('usage');
            
            // Update active usage tab within the server
            $tab.siblings('.vietnix-usage-tab').removeClass('active');
            $tab.addClass('active');
            
            // Update filters and reload data
            vietnixTables[config.tableId].filters.server = server;
            vietnixTables[config.tableId].filters.usage = usage;
            
            loadTableData($wrapper, config);
        });
    }
    
    // Load table data via AJAX
    function loadTableData($wrapper, config) {
        const tableState = vietnixTables[config.tableId];
        
        // Show loading
        showLoading($wrapper);
        
        const data = {
            action: 'vietnix_get_table_data',
            server_name: tableState.filters.server || '',
            usage_time: tableState.filters.usage || '',
            nonce: config.nonce
        };
        
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    updateTable($wrapper, config, response.data);
                } else {
                    console.error('Load data error:', response.data);
                    showError($wrapper, response.data.message || 'Có lỗi xảy ra khi tải dữ liệu');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                showError($wrapper, 'Không thể kết nối đến server');
            },
            complete: function() {
                hideLoading($wrapper);
            }
        });
    }
    
    // Update products grid with new data
    function updateTable($wrapper, config, data) {
        const $productsGrid = $wrapper.find(`#${config.tableId}-products`);
        let html = '';
        
        if (data.items.length === 0) {
            html = '<div class="no-products"><p>Không tìm thấy sản phẩm nào.</p></div>';
        } else {
            data.items.forEach(function(item) {
                html += buildProductCard(item, config);
            });
        }
        
        $productsGrid.html(html);
    }
    
    // Build product card HTML với custom labels
    function buildProductCard(item, config) {
        let html = '<div class="vietnix-product-card">'; 
        // Product Header
        html += '<div class="product-header">';
        html += '<h3 class="product-name">' + escapeHtml(item.product_name) + '</h3>';
        
        html += '<div class="main-price">';
        
        // Kiểm tra có price sale hay không
        if (item.price_sale_raw && parseFloat(item.price_sale_raw) > 0 && parseFloat(item.price_sale_raw) < parseFloat(item.price_raw)) {
            html += '<span class="original-price">' + item.price + '</span>';
            html += '<span class="sale-price"><span class="current-price-value">' + item.price_sale + '</span><span>/th</span></span>';
        } else {
            html += '<span class="current-price"><span class="current-price-value">' + item.price + '</span><span>/th</span></span>';
        }
        html +=  '<div class="img-sale"><img src="' + VietnixConfig.getAssetUrl('assets/image/icon_sale.png', config.tableId) + '"/></div>';
        html += '</div>';
         // Addon pricing
            if (item.price_addon_sale) {
                html += '<div class="addon-pricing">';
                html += '<span class="addon-price"><span>Giảm thêm</span>' + escapeHtml(item.price_addon_sale) + '</span>';
                if (item.price_addon_code) {
                    html += '<code class="addon-code">' + escapeHtml(item.price_addon_code) + '</code>';
                    html += '<span class="addon-copy" data-copy="' + escapeHtml(item.price_addon_code) + '" title="Sao chép"><img src="' + VietnixConfig.getAssetUrl('assets/image/icon_copy.png', config.tableId) + '"/></span>';
                }
                html += '</div>';
            }
        html += '</div>'; 
              // Pricing Section với custom labels
        html += '<div class="pricing-section">';
       
        // Product Content
        if (item.product_content) {
            // Parse product_content into list items
            const features = item.product_content.split(',').map(f => f.trim()).filter(f => f);
            html += '<ul class="product-content">';
            // console.log(features);
            features.forEach(feature => {
                // Split by "|"
                const parts = feature.split('|').map(p => p.trim());
                parts.forEach((part, index) => {
                    parts[index] = part.replace(/[\{\}]/g, '').trim();
                });
                // console.log(parts);
                let imgHtml = '', textHtml = '';
                if (parts.length === 2) {
                    // If first part is a URL, use <img>
                    if (/^https?:\/\/.+\.(png|jpg|jpeg|gif|svg)$/i.test(parts[0])) {
                        imgHtml = `<img src="${escapeHtml(parts[0])}" alt="" loading="lazy" />`;
                        textHtml = `<span>${escapeHtml(parts[1])}</span>`;
                    } else {
                        textHtml = `<span>${escapeHtml(parts[0])} ${escapeHtml(parts[1])}</span>`;
                    }
                } else {
                    textHtml = `<span>${escapeHtml(feature)}</span>`;
                }
                html += `<li>${imgHtml}${textHtml}</li>`;
            });
            html += '</ul>';
        }
        html += '</div>';
        
        // Additional Info
        if (item.product_sku || item.description) {
            html += '<div class="product-details">';
            if (item.product_sku) {
                html += '<div class="sku-info">SKU: <code>' + escapeHtml(item.product_sku) + '</code></div>';
            }
            if (item.description) {
                const truncated = item.description.length > 150 ? 
                    item.description.substring(0, 150) + '...' : 
                    item.description;
                html += '<div class="description">' + escapeHtml(truncated) + '</div>';
            }
            html += '</div>';
        }
        
        // Server Sale Info
        if (item.server_sale) {
            html += '<div class="server-sale">' + escapeHtml(item.server_sale) + '</div>';
        }
        
        // CTA Button
        if (item.product_cta_link) {
            html += '<div class="product-actions">';
            html += '<a href="' + escapeHtml(item.product_cta_link) + '" class="cta-button" target="_blank">Xem chi tiết</a>';
            html += '</div>';
        }
        
        html += '</div>';
        return html;
    }
    
    // Show loading state
    function showLoading($wrapper) {
        $wrapper.find('.vietnix-loading').show();
        $wrapper.find('.vietnix-products-grid').css('opacity', '0.5');
    }
    
    // Hide loading state
    function hideLoading($wrapper) {
        $wrapper.find('.vietnix-loading').hide();
        $wrapper.find('.vietnix-products-grid').css('opacity', '1');
    }
    
    // Show error message
    function showError($wrapper, message) {
        const $productsGrid = $wrapper.find('.vietnix-products-grid');
        $productsGrid.html(`<div class="error-message" style="text-align: center; color: #d63638; padding: 40px;">Error: ${escapeHtml(message)}</div>`);
    }
    
 
    // Utility function to escape HTML
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return unsafe;
        
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
})(jQuery);
