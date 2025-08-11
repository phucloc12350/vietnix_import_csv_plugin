/**
 * Data view functionality for Vietnix CSV Import Plugin
 */
(function () {
  "use strict";

  // Vue data view component
  const VietnixDataView = {
    data() {
      return {
        state: {
          currentPage: 1,
          totalPages: 1,
        },
        tableData: {
          items: [],
          pagination: {},
          total: 0,
          showing: 0,
        },
        searchTerm: "",
        statusFilter: "",
        categoryFilter: "",
        isLoading: false,
        orderBy: "product_name",
        order: "asc",
      };
    },

    mounted() {
      this.initSettings();
      this.loadTableData();
    },

    methods: {
      // Debounce function for search
      debounce(func, delay) {
        let timeoutId;
        return function (...args) {
          clearTimeout(timeoutId);
          timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
      },

      // Search handler with debounce
      onSearchInput() {
        this.debouncedSearch();
      },

      // Filter change handler
      onFilterChange() {
        this.state.currentPage = 1;
        this.loadTableData();
      },

      // Column sort handler
      onColumnSort(column) {
        const currentOrder = this.orderBy === column ? this.order : "asc";
        this.order = currentOrder === "asc" ? "desc" : "asc";
        this.orderBy = column;
        this.state.currentPage = 1;
        this.loadTableData();
      },

      // Pagination handler
      onPageClick(page) {
        if (page && page !== this.state.currentPage) {
          this.state.currentPage = page;
          this.loadTableData();
        }
      },

      // Load table data
      loadTableData() {
        this.isLoading = true;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", vietnix_ajax.ajax_url, true);
        xhr.setRequestHeader(
          "Content-Type",
          "application/x-www-form-urlencoded"
        );

        const formData = new URLSearchParams({
          action: "vietnix_csv_get_data",
          page: this.state.currentPage,
          search: this.searchTerm,
          status: this.statusFilter,
          category: this.categoryFilter,
          order_by: this.orderBy,
          order: this.order,
          nonce: vietnix_ajax.nonce,
        });

        xhr.onload = () => {
          this.isLoading = false;
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              this.updateDataTable(response.data);
            } else {
              window.VietnixUtils.showMessage(
                "error",
                response.data || "Failed to load data."
              );
            }
          } catch (e) {
            window.VietnixUtils.showMessage(
              "error",
              "Failed to parse response."
            );
          }
        };

        xhr.onerror = () => {
          this.isLoading = false;
          window.VietnixUtils.showMessage(
            "error",
            "Failed to load data. Please try again."
          );
        };

        xhr.send(formData);
      },

      // Update data table
      updateDataTable(data) {
        this.tableData = {
          items: data.items || [],
          pagination: data.pagination || {},
          total: data.total || 0,
          showing: data.showing || 0,
        };

        if (data.pagination) {
          this.state.totalPages = data.pagination.total_pages;
          this.state.currentPage = data.pagination.current_page;
        }
      },

      // Format price display
      formatPrice(price, currency = "VND") {
        return window.VietnixUtils
          ? window.VietnixUtils.formatPrice(price, currency)
          : price;
      },

      // Format date display
      formatDate(date) {
        return window.VietnixUtils
          ? window.VietnixUtils.formatDate(date)
          : date;
      },

      // Escape HTML
      escapeHtml(text) {
        return window.VietnixUtils
          ? window.VietnixUtils.escapeHtml(text)
          : text;
      },

      // Get sort class for column headers
      getSortClass(column) {
        if (this.orderBy !== column) return "";
        return this.order === "asc" ? "sorted-asc" : "sorted-desc";
      },

      // Get pagination pages array
      getPaginationPages() {
        const pages = [];
        const pagination = this.tableData.pagination;

        if (!pagination.start_page || !pagination.end_page) return pages;

        for (let i = pagination.start_page; i <= pagination.end_page; i++) {
          pages.push(i);
        }
        return pages;
      },

      // Settings form handler
      onSettingsSubmit(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        formData.append("action", "vietnix_csv_save_settings");
        formData.append("nonce", vietnix_ajax.nonce);

        const xhr = new XMLHttpRequest();
        xhr.open("POST", vietnix_ajax.ajax_url, true);

        xhr.onload = () => {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              window.VietnixUtils.showMessage(
                "success",
                "Settings saved successfully."
              );
            } else {
              window.VietnixUtils.showMessage(
                "error",
                response.data || "Failed to save settings."
              );
            }
          } catch (e) {
            window.VietnixUtils.showMessage(
              "error",
              "Failed to parse response."
            );
          }
        };

        xhr.onerror = () => {
          window.VietnixUtils.showMessage(
            "error",
            "Failed to save settings. Please try again."
          );
        };

        xhr.send(formData);
      },

      // Reset settings handler
      onResetSettings(event) {
        event.preventDefault();

        if (
          !confirm(
            "Are you sure you want to reset all settings to default values?"
          )
        ) {
          return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", vietnix_ajax.ajax_url, true);
        xhr.setRequestHeader(
          "Content-Type",
          "application/x-www-form-urlencoded"
        );

        const formData = new URLSearchParams({
          action: "vietnix_csv_reset_settings",
          nonce: vietnix_ajax.nonce,
        });

        xhr.onload = () => {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              window.VietnixUtils.showMessage(
                "success",
                "Settings reset successfully."
              );
              location.reload();
            } else {
              window.VietnixUtils.showMessage(
                "error",
                response.data || "Failed to reset settings."
              );
            }
          } catch (e) {
            window.VietnixUtils.showMessage(
              "error",
              "Failed to parse response."
            );
          }
        };

        xhr.onerror = () => {
          window.VietnixUtils.showMessage(
            "error",
            "Failed to reset settings. Please try again."
          );
        };

        xhr.send(formData);
      },

      // Settings initialization
      initSettings() {
        // Settings form submission
        const settingsForm = document.querySelector(".vietnix-settings-form");
        if (settingsForm) {
          settingsForm.addEventListener(
            "submit",
            this.onSettingsSubmit.bind(this)
          );
        }

        // Reset settings button
        const resetButton = document.querySelector(".vietnix-reset-settings");
        if (resetButton) {
          resetButton.addEventListener(
            "click",
            this.onResetSettings.bind(this)
          );
        }
      },
    },

    created() {
      // Create debounced search function
      this.debouncedSearch = this.debounce(() => {
        this.state.currentPage = 1;
        this.loadTableData();
      }, 500);
    },
  };

  // Create and mount Vue app for data view
  if (
    typeof Vue !== "undefined" &&
    document.querySelector("#vietnix-data-view")
  ) {
    const app = Vue.createApp(VietnixDataView);
    app.mount("#vietnix-data-view");
  }

  // Also create global object for backward compatibility
  window.VietnixViewData = {
    // View state
    state: {
      currentPage: 1,
      totalPages: 1,
    },

    // Initialize view data functionality
    init: function () {
      this.initDataTable();
      this.initSettings();
    },

    // Data Table Handler
    initDataTable: function () {
      const self = this;

      // Search
      const searchInput = document.querySelector(".vietnix-search-input");
      if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener("keyup", function () {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            self.state.currentPage = 1;
            self.loadTableData();
          }, 500);
        });
      }

      // Filters
      const filterSelects = document.querySelectorAll(".vietnix-filter-select");
      filterSelects.forEach((select) => {
        select.addEventListener("change", function () {
          self.state.currentPage = 1;
          self.loadTableData();
        });
      });

      // Sorting
      document.addEventListener("click", function (e) {
        if (e.target.matches('.vietnix-data-table th[data-sortable="true"]')) {
          const column = e.target.dataset.column;
          const currentOrder = e.target.dataset.order || "asc";
          const newOrder = currentOrder === "asc" ? "desc" : "asc";

          // Update UI
          document.querySelectorAll(".vietnix-data-table th").forEach((th) => {
            th.classList.remove("sorted-asc", "sorted-desc");
            delete th.dataset.order;
          });
          e.target.classList.add("sorted-" + newOrder);
          e.target.dataset.order = newOrder;

          // Load data
          self.state.currentPage = 1;
          self.loadTableData(column, newOrder);
        }
      });

      // Pagination
      document.addEventListener("click", function (e) {
        if (e.target.matches(".vietnix-pagination .page-numbers")) {
          e.preventDefault();
          const page = parseInt(e.target.dataset.page);
          if (page && page !== self.state.currentPage) {
            self.state.currentPage = page;
            self.loadTableData();
          }
        }
      });
    },

    // Load table data
    loadTableData: function (orderBy = "product_name", order = "asc") {
      const searchInput = document.querySelector(".vietnix-search-input");
      const statusFilter = document.querySelector(".vietnix-status-filter");
      const categoryFilter = document.querySelector(".vietnix-category-filter");

      const searchTerm = searchInput ? searchInput.value : "";
      const statusValue = statusFilter ? statusFilter.value : "";
      const categoryValue = categoryFilter ? categoryFilter.value : "";

      const loadingEl = document.querySelector(".vietnix-data-loading");
      const tableBody = document.querySelector(".vietnix-data-table tbody");

      if (loadingEl) loadingEl.style.display = "block";
      if (tableBody)
        tableBody.innerHTML =
          '<tr><td colspan="100%" style="text-align: center; padding: 40px;">Loading...</td></tr>';

      const xhr = new XMLHttpRequest();
      xhr.open("POST", vietnix_ajax.ajax_url, true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      const formData = new URLSearchParams({
        action: "vietnix_csv_get_data",
        page: this.state.currentPage,
        search: searchTerm,
        status: statusValue,
        category: categoryValue,
        order_by: orderBy,
        order: order,
        nonce: vietnix_ajax.nonce,
      });

      xhr.onload = () => {
        if (loadingEl) loadingEl.style.display = "none";
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            this.updateDataTable(response.data);
          } else {
            window.VietnixUtils.showMessage(
              "error",
              response.data || "Failed to load data."
            );
          }
        } catch (e) {
          window.VietnixUtils.showMessage("error", "Failed to parse response.");
        }
      };

      xhr.onerror = () => {
        if (loadingEl) loadingEl.style.display = "none";
        window.VietnixUtils.showMessage(
          "error",
          "Failed to load data. Please try again."
        );
      };

      xhr.send(formData);
    },

    // Update data table
    updateDataTable: function (data) {
      let html = "";

      if (data.items.length === 0) {
        html =
          '<tr class="no-data"><td colspan="100%" style="text-align: center; padding: 40px; color: #666;">No data found</td></tr>';
      } else {
        data.items.forEach(function (item) {
          html += "<tr>";
          html += "<td>" + item.id + "</td>";
          html +=
            "<td><strong>" +
            window.VietnixUtils.escapeHtml(item.product_name) +
            "</strong></td>";
          html +=
            '<td class="price">' +
            window.VietnixUtils.formatPrice(item.price, item.currency) +
            "</td>";
          html +=
            "<td>" +
            (item.category
              ? '<span class="category-tag">' +
                window.VietnixUtils.escapeHtml(item.category) +
                "</span>"
              : "—") +
            "</td>";
          html +=
            "<td>" +
            (item.description
              ? window.VietnixUtils.escapeHtml(
                  item.description.substring(0, 100)
                ) + (item.description.length > 100 ? "..." : "")
              : "—") +
            "</td>";
          html +=
            "<td>" +
            (item.sku
              ? "<code>" + window.VietnixUtils.escapeHtml(item.sku) + "</code>"
              : "—") +
            "</td>";

          if (item.stock_quantity > 0) {
            html +=
              '<td><span class="stock-available">' +
              item.stock_quantity +
              "</span></td>";
          } else {
            html += '<td><span class="stock-out">Out of stock</span></td>';
          }

          html +=
            '<td><span class="status-' +
            item.status +
            '">' +
            (item.status === "active" ? "Active" : "Inactive") +
            "</span></td>";
          html +=
            "<td>" + window.VietnixUtils.formatDate(item.created_at) + "</td>";
          html += "</tr>";
        });
      }

      const tableBody = document.querySelector(".vietnix-data-table tbody");
      if (tableBody) tableBody.innerHTML = html;

      // Update pagination
      if (data.pagination) {
        this.updatePagination(data.pagination);
      }

      // Update results info
      const resultsInfo = document.querySelector(".vietnix-results-info");
      if (resultsInfo) {
        resultsInfo.textContent =
          "Showing " + data.showing + " of " + data.total + " items";
      }
    },

    // Update pagination
    updatePagination: function (pagination) {
      this.state.totalPages = pagination.total_pages;
      this.state.currentPage = pagination.current_page;

      let html = "";

      // Previous button
      if (pagination.current_page > 1) {
        html +=
          '<a href="#" class="page-numbers" data-page="' +
          (pagination.current_page - 1) +
          '">« Previous</a>';
      }

      // Page numbers
      for (let i = pagination.start_page; i <= pagination.end_page; i++) {
        if (i === pagination.current_page) {
          html += '<span class="page-numbers current">' + i + "</span>";
        } else {
          html +=
            '<a href="#" class="page-numbers" data-page="' +
            i +
            '">' +
            i +
            "</a>";
        }
      }

      // Next button
      if (pagination.current_page < pagination.total_pages) {
        html +=
          '<a href="#" class="page-numbers" data-page="' +
          (pagination.current_page + 1) +
          '">Next »</a>';
      }

      const pagination_el = document.querySelector(".vietnix-pagination");
      if (pagination_el) pagination_el.innerHTML = html;
    },

    // Settings Handler
    initSettings: function () {
      const settingsForm = document.querySelector(".vietnix-settings-form");
      if (settingsForm) {
        settingsForm.addEventListener("submit", function (e) {
          e.preventDefault();

          const formData = new FormData(this);
          formData.append("action", "vietnix_csv_save_settings");
          formData.append("nonce", vietnix_ajax.nonce);

          const xhr = new XMLHttpRequest();
          xhr.open("POST", vietnix_ajax.ajax_url, true);

          xhr.onload = function () {
            try {
              const response = JSON.parse(xhr.responseText);
              if (response.success) {
                window.VietnixUtils.showMessage(
                  "success",
                  "Settings saved successfully."
                );
              } else {
                window.VietnixUtils.showMessage(
                  "error",
                  response.data || "Failed to save settings."
                );
              }
            } catch (e) {
              window.VietnixUtils.showMessage(
                "error",
                "Failed to parse response."
              );
            }
          };

          xhr.onerror = function () {
            window.VietnixUtils.showMessage(
              "error",
              "Failed to save settings. Please try again."
            );
          };

          xhr.send(formData);
        });
      }

      // Reset settings
      const resetButton = document.querySelector(".vietnix-reset-settings");
      if (resetButton) {
        resetButton.addEventListener("click", function (e) {
          e.preventDefault();

          if (
            !confirm(
              "Are you sure you want to reset all settings to default values?"
            )
          ) {
            return;
          }

          const xhr = new XMLHttpRequest();
          xhr.open("POST", vietnix_ajax.ajax_url, true);
          xhr.setRequestHeader(
            "Content-Type",
            "application/x-www-form-urlencoded"
          );

          const formData = new URLSearchParams({
            action: "vietnix_csv_reset_settings",
            nonce: vietnix_ajax.nonce,
          });

          xhr.onload = function () {
            try {
              const response = JSON.parse(xhr.responseText);
              if (response.success) {
                window.VietnixUtils.showMessage(
                  "success",
                  "Settings reset successfully."
                );
                location.reload();
              } else {
                window.VietnixUtils.showMessage(
                  "error",
                  response.data || "Failed to reset settings."
                );
              }
            } catch (e) {
              window.VietnixUtils.showMessage(
                "error",
                "Failed to parse response."
              );
            }
          };

          xhr.onerror = function () {
            window.VietnixUtils.showMessage(
              "error",
              "Failed to reset settings. Please try again."
            );
          };

          xhr.send(formData);
        });
      }
    },
  };
})();
