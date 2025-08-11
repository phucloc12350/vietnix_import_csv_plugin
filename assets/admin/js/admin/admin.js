/**
 * Vue.js Admin for Vietnix CSV Import Plugin
 * Fallback to vanilla JS if Vue is not available
 */

// Check if Vue is available, fallback to vanilla JS if not
function initVietnixAdmin() {

    // Vue.js version
    const { createApp } = Vue;
    
    const VietnixAdminApp = createApp({
      data() {
        return {
          activeTab: "import",
        };
      },

      methods: {
        // Tab Navigation
        setActiveTab(event, tab) {
          event.preventDefault();
          this.activeTab = tab;

          // Load data if viewing data tab
          if (tab === "data" && window.VietnixViewData) {
            this.$nextTick(() => {
              window.VietnixViewData.loadTableData();
            });
          }
        },

        // Show message utility
        showMessage(type, message) {
          const messageHtml =
            '<div class="notice notice-' +
            type +
            ' is-dismissible"><p>' +
            message +
            "</p></div>";
          const messagesEl = document.querySelector(".vietnix-messages");
          if (messagesEl) {
            messagesEl.innerHTML = messageHtml;

            // Auto dismiss after 5 seconds
            setTimeout(() => {
              const notice = messagesEl.querySelector(".notice");
              if (notice) {
                notice.style.display = "none";
              }
            }, 5000);

            // Scroll to message
            messagesEl.scrollIntoView({ behavior: "smooth" });
          }
        },

        // Format file size utility
        formatFileSize(bytes) {
          if (bytes === 0) return "0 Bytes";
          const k = 1024;
          const sizes = ["Bytes", "KB", "MB", "GB"];
          const i = Math.floor(Math.log(bytes) / Math.log(k));
          return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
        },

        // Format price utility
        formatPrice(price, currency) {
          const formatter = new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: currency || "USD",
          });
          return formatter.format(price);
        },

        // Format date utility
        formatDate(dateString) {
          const date = new Date(dateString);
          return date.toLocaleDateString();
        },

        // Escape HTML utility
        escapeHtml(unsafe) {
          return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
        },

        // Debounce utility
        debounce(func, wait) {
          let timeout;
          return function executedFunction(...args) {
            const later = () => {
              clearTimeout(timeout);
              func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
          };
        },
      },

      mounted() {
        // Initialize modules compatibility
        if (window.VietnixImport) {
          window.VietnixImport.init();
        }
        if (window.VietnixViewData) {
          window.VietnixViewData.init();
        }

        // Make utilities available globally for compatibility
        window.VietnixUtils = {
          showMessage: this.showMessage,
          formatFileSize: this.formatFileSize,
          formatPrice: this.formatPrice,
          formatDate: this.formatDate,
          escapeHtml: this.escapeHtml,
          debounce: this.debounce,
        };

        // Backward compatibility
        window.VietnixAdmin = {
          init: () => {},
          showMessage: this.showMessage,
          setActiveTab: this.setActiveTab,
        };
      },
    });

    // Mount when DOM ready
    document.addEventListener("DOMContentLoaded", () => {
      const adminEl = document.getElementById("vietnix-admin-app");
      if (adminEl) {
        VietnixAdminApp.mount("#vietnix-admin-app");
      }
    });
    
  } 
// Initialize when script loads
initVietnixAdmin();
