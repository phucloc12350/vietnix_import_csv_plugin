/**
 * Admin JavaScript for Vietnix CSV Import Plugin
 */
(function($) {
    'use strict';
    
    // Global variables
    let vietnixImport = {
        currentPage: 1,
        totalPages: 1,
        isLoading: false,
        uploadedFile: null,
        xhr: null
    };
    
    // Initialize when document ready
    $(document).ready(function() {
        initTabs();
        initFileUpload();
        initImportForm();
        initDataTable();
        initSettings();
    });
    
    // Tab Navigation
    function initTabs() {
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).attr('href');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show/hide content
            $('.tab-content').hide();
            $(targetTab).show();
            
            // Load data if viewing data tab
            if (targetTab === '#data-tab') {
                loadTableData();
            }
        });
        
        // Show first tab by default
        $('.nav-tab:first').click();
    }
    
    // File Upload Handler
    function initFileUpload() {
        const $uploadArea = $('.vietnix-upload-area');
        const $fileInput = $('.vietnix-file-input');
        const $fileLabel = $('.vietnix-file-label');
        
        // Drag and drop
        $uploadArea.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });
        
        $uploadArea.on('dragleave dragend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });
        
        $uploadArea.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });
        
        // File input change
        $fileInput.on('change', function() {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });
        
        // Click to select file
        $fileLabel.on('click', function() {
            $fileInput.click();
        });
    }
    
    // Handle file selection
    function handleFileSelect(file) {
        const allowedTypes = ['text/csv', 'application/vnd.ms-excel'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        // Validate file type
        if (!allowedTypes.includes(file.type) && !file.name.toLowerCase().endsWith('.csv')) {
            showMessage('error', 'Please select a valid CSV file.');
            return;
        }
        
        // Validate file size
        if (file.size > maxSize) {
            showMessage('error', 'File size must be less than 10MB.');
            return;
        }
        
        vietnixImport.uploadedFile = file;
        
        // Show file info
        $('.vietnix-file-selected').remove();
        $('.vietnix-upload-area').after(
            '<div class="vietnix-file-selected">' +
                '<div class="file-info">' + file.name + '</div>' +
                '<div class="file-meta">Size: ' + formatFileSize(file.size) + ' | Type: ' + file.type + '</div>' +
            '</div>'
        );
        
        // Enable import button
        $('.vietnix-import-btn').prop('disabled', false);
        
        // Preview CSV content
        previewCSV(file);
    }
    
    // Preview CSV content
    function previewCSV(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const csv = e.target.result;
            const lines = csv.split('\n').slice(0, 5); // First 5 lines
            const preview = lines.join('\n');
            
            $('.csv-preview').remove();
            $('.vietnix-file-selected').after(
                '<div class="csv-preview">' +
                    '<h4>File Preview:</h4>' +
                    '<pre>' + escapeHtml(preview) + '</pre>' +
                '</div>'
            );
        };
        reader.readAsText(file.slice(0, 1024)); // Read first 1KB
    }
    
    // Import Form Handler
    function initImportForm() {
        $('.vietnix-import-form').on('submit', function(e) {
            e.preventDefault();
            
            if (!vietnixImport.uploadedFile) {
                showMessage('error', 'Please select a CSV file first.');
                return;
            }
            
            if (vietnixImport.isLoading) {
                return;
            }
            
            processImport();
        });
        
        // Clear data button
        $('.vietnix-clear-data').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear all imported data? This action cannot be undone.')) {
                return;
            }
            
            clearData();
        });
    }
    
    // Process CSV Import
    function processImport() {
        const formData = new FormData();
        formData.append('action', 'vietnix_csv_import');
        formData.append('csv_file', vietnixImport.uploadedFile);
        formData.append('overwrite_data', $('.vietnix-overwrite').is(':checked') ? '1' : '0');
        formData.append('nonce', vietnix_ajax.nonce);
        
        // Get selected options
        const selectedOptions = [];
        $('.vietnix-import-options input[type="checkbox"]:checked').each(function() {
            selectedOptions.push($(this).val());
        });
        formData.append('import_options', selectedOptions.join(','));
        
        vietnixImport.isLoading = true;
        $('.vietnix-import-btn').prop('disabled', true).text('Importing...');
        $('.vietnix-progress-wrapper').show();
        $('.vietnix-progress-fill').css('width', '0%');
        $('.vietnix-progress-text').text('Preparing import...');
        
        // Start import
        vietnixImport.xhr = $.ajax({
            url: vietnix_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        $('.vietnix-progress-fill').css('width', percent + '%');
                        $('.vietnix-progress-text').text('Uploading: ' + percent + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    $('.vietnix-progress-fill').css('width', '100%');
                    $('.vietnix-progress-text').text('Import completed successfully!');
                    
                    showMessage('success', response.data.message);
                    
                    // Update statistics
                    updateStatistics(response.data.stats);
                    
                    // Reset form
                    resetImportForm();
                    
                    // Reload data if on data tab
                    if ($('#data-tab').is(':visible')) {
                        loadTableData();
                    }
                } else {
                    showMessage('error', response.data || 'Import failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    showMessage('error', 'Import failed: ' + error);
                }
            },
            complete: function() {
                vietnixImport.isLoading = false;
                $('.vietnix-import-btn').prop('disabled', false).text('Import CSV');
                $('.vietnix-progress-wrapper').hide();
            }
        });
    }
    
    // Clear all data
    function clearData() {
        $.ajax({
            url: vietnix_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vietnix_csv_clear_data',
                nonce: vietnix_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', 'All data cleared successfully.');
                    updateStatistics({ total: 0, active: 0, inactive: 0 });
                    loadTableData();
                } else {
                    showMessage('error', response.data || 'Failed to clear data.');
                }
            },
            error: function() {
                showMessage('error', 'Failed to clear data. Please try again.');
            }
        });
    }
    
    // Data Table Handler
    function initDataTable() {
        // Search
        $('.vietnix-search-input').on('keyup', debounce(function() {
            vietnixImport.currentPage = 1;
            loadTableData();
        }, 500));
        
        // Filters
        $('.vietnix-filter-select').on('change', function() {
            vietnixImport.currentPage = 1;
            loadTableData();
        });
        
        // Sorting
        $(document).on('click', '.vietnix-data-table th[data-sortable="true"]', function() {
            const column = $(this).data('column');
            const currentOrder = $(this).data('order') || 'asc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            
            // Update UI
            $('.vietnix-data-table th').removeClass('sorted-asc sorted-desc').removeData('order');
            $(this).addClass('sorted-' + newOrder).data('order', newOrder);
            
            // Load data
            vietnixImport.currentPage = 1;
            loadTableData(column, newOrder);
        });
        
        // Pagination
        $(document).on('click', '.vietnix-pagination .page-numbers', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (page && page !== vietnixImport.currentPage) {
                vietnixImport.currentPage = page;
                loadTableData();
            }
        });
    }
    
    // Load table data
    function loadTableData(orderBy = 'product_name', order = 'asc') {
        const searchTerm = $('.vietnix-search-input').val();
        const statusFilter = $('.vietnix-status-filter').val();
        const categoryFilter = $('.vietnix-category-filter').val();
        
        $('.vietnix-data-loading').show();
        $('.vietnix-data-table tbody').html('<tr><td colspan="100%" style="text-align: center; padding: 40px;">Loading...</td></tr>');
        
        $.ajax({
            url: vietnix_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'vietnix_csv_get_data',
                page: vietnixImport.currentPage,
                search: searchTerm,
                status: statusFilter,
                category: categoryFilter,
                order_by: orderBy,
                order: order,
                nonce: vietnix_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDataTable(response.data);
                } else {
                    showMessage('error', response.data || 'Failed to load data.');
                }
            },
            error: function() {
                showMessage('error', 'Failed to load data. Please try again.');
            },
            complete: function() {
                $('.vietnix-data-loading').hide();
            }
        });
    }
    
    // Update data table
    function updateDataTable(data) {
        let html = '';
        
        if (data.items.length === 0) {
            html = '<tr class="no-data"><td colspan="100%" style="text-align: center; padding: 40px; color: #666;">No data found</td></tr>';
        } else {
            data.items.forEach(function(item) {
                html += '<tr>';
                html += '<td>' + item.id + '</td>';
                html += '<td><strong>' + escapeHtml(item.product_name) + '</strong></td>';
                html += '<td class="price">' + formatPrice(item.price, item.currency) + '</td>';
                html += '<td>' + (item.category ? '<span class="category-tag">' + escapeHtml(item.category) + '</span>' : '—') + '</td>';
                html += '<td>' + (item.description ? escapeHtml(item.description.substring(0, 100)) + (item.description.length > 100 ? '...' : '') : '—') + '</td>';
                html += '<td>' + (item.sku ? '<code>' + escapeHtml(item.sku) + '</code>' : '—') + '</td>';
                
                if (item.stock_quantity > 0) {
                    html += '<td><span class="stock-available">' + item.stock_quantity + '</span></td>';
                } else {
                    html += '<td><span class="stock-out">Out of stock</span></td>';
                }
                
                html += '<td><span class="status-' + item.status + '">' + (item.status === 'active' ? 'Active' : 'Inactive') + '</span></td>';
                html += '<td>' + formatDate(item.created_at) + '</td>';
                html += '</tr>';
            });
        }
        
        $('.vietnix-data-table tbody').html(html);
        
        // Update pagination
        if (data.pagination) {
            updatePagination(data.pagination);
        }
        
        // Update results info
        $('.vietnix-results-info').text(
            'Showing ' + data.showing + ' of ' + data.total + ' items'
        );
    }
    
    // Update pagination
    function updatePagination(pagination) {
        vietnixImport.totalPages = pagination.total_pages;
        vietnixImport.currentPage = pagination.current_page;
        
        let html = '';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += '<a href="#" class="page-numbers" data-page="' + (pagination.current_page - 1) + '">« Previous</a>';
        }
        
        // Page numbers
        for (let i = pagination.start_page; i <= pagination.end_page; i++) {
            if (i === pagination.current_page) {
                html += '<span class="page-numbers current">' + i + '</span>';
            } else {
                html += '<a href="#" class="page-numbers" data-page="' + i + '">' + i + '</a>';
            }
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
            html += '<a href="#" class="page-numbers" data-page="' + (pagination.current_page + 1) + '">Next »</a>';
        }
        
        $('.vietnix-pagination').html(html);
    }
    
    // Settings Handler
    function initSettings() {
        $('.vietnix-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            
            $.ajax({
                url: vietnix_ajax.ajax_url,
                type: 'POST',
                data: formData + '&action=vietnix_csv_save_settings&nonce=' + vietnix_ajax.nonce,
                success: function(response) {
                    if (response.success) {
                        showMessage('success', 'Settings saved successfully.');
                    } else {
                        showMessage('error', response.data || 'Failed to save settings.');
                    }
                },
                error: function() {
                    showMessage('error', 'Failed to save settings. Please try again.');
                }
            });
        });
        
        // Reset settings
        $('.vietnix-reset-settings').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to reset all settings to default values?')) {
                return;
            }
            
            $.ajax({
                url: vietnix_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'vietnix_csv_reset_settings',
                    nonce: vietnix_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', 'Settings reset successfully.');
                        location.reload();
                    } else {
                        showMessage('error', response.data || 'Failed to reset settings.');
                    }
                },
                error: function() {
                    showMessage('error', 'Failed to reset settings. Please try again.');
                }
            });
        });
    }
    
    // Utility Functions
    function resetImportForm() {
        vietnixImport.uploadedFile = null;
        $('.vietnix-file-input').val('');
        $('.vietnix-file-selected').remove();
        $('.csv-preview').remove();
        $('.vietnix-import-btn').prop('disabled', true);
    }
    
    function updateStatistics(stats) {
        $('.stat-total .vietnix-stat-number').text(stats.total || 0);
        $('.stat-active .vietnix-stat-number').text(stats.active || 0);
        $('.stat-inactive .vietnix-stat-number').text(stats.inactive || 0);
    }
    
    function showMessage(type, message) {
        const messageHtml = '<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>';
        $('.vietnix-messages').html(messageHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.vietnix-messages .notice').fadeOut();
        }, 5000);
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $('.vietnix-messages').offset().top - 50
        }, 300);
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function formatPrice(price, currency) {
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'USD'
        });
        return formatter.format(price);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }
    
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Cancel import
    $(document).on('click', '.vietnix-cancel-import', function(e) {
        e.preventDefault();
        
        if (vietnixImport.xhr) {
            vietnixImport.xhr.abort();
            showMessage('info', 'Import cancelled.');
            resetImportForm();
        }
    });
    
})(jQuery);
