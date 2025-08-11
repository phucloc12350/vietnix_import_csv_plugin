/**
 * Vanilla JS Import functionality for Vietnix CSV Import Plugin
 * Converted from jQuery to pure JavaScript
 */
const VietnixImport = {
  // Import state
  state: {
    currentPage: 1,
    totalPages: 1,
    isLoading: false,
    uploadedFile: null,
    xhr: null,
  },

  // Initialize import functionality
  init() {
    this.initFileUpload();
    this.initImportForm();
    this.initImportPageForm();
  },

  // File Upload Handler
  initFileUpload() {
    const uploadArea = document.querySelector(".vietnix-upload-area");
    const fileInput = document.querySelector(".vietnix-file-input");
    const fileLabel = document.querySelector(".vietnix-file-label");

    if (!uploadArea || !fileInput || !fileLabel) return;

    // Drag and drop events
    uploadArea.addEventListener("dragover", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.add("dragover");
    });

    uploadArea.addEventListener("dragenter", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.add("dragover");
    });

    uploadArea.addEventListener("dragleave", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.remove("dragover");
    });

    uploadArea.addEventListener("dragend", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.remove("dragover");
    });

    uploadArea.addEventListener("drop", (e) => {
      e.preventDefault();
      e.stopPropagation();
      uploadArea.classList.remove("dragover");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        this.handleFileSelect(files[0]);
      }
    });

    // File input change
    fileInput.addEventListener("change", (e) => {
      if (e.target.files.length > 0) {
        this.handleFileSelect(e.target.files[0]);
      }
    });

    // Click to select file
    fileLabel.addEventListener("click", () => {
      fileInput.click();
    });
  },

  // Handle file selection
  handleFileSelect(file) {
    const allowedTypes = ["text/csv", "application/vnd.ms-excel"];
    const maxSize = 10 * 1024 * 1024; // 10MB

    // Validate file type
    if (
      !allowedTypes.includes(file.type) &&
      !file.name.toLowerCase().endsWith(".csv")
    ) {
      if (window.VietnixUtils) {
        window.VietnixUtils.showMessage(
          "error",
          "Please select a valid CSV file."
        );
      }
      return;
    }

    // Validate file size
    if (file.size > maxSize) {
      if (window.VietnixUtils) {
        window.VietnixUtils.showMessage(
          "error",
          "File size must be less than 10MB."
        );
      }
      return;
    }

    this.state.uploadedFile = file;

    // Remove existing file info
    const existingSelected = document.querySelector(".vietnix-file-selected");
    if (existingSelected) {
      existingSelected.remove();
    }

    // Show file info
    const uploadArea = document.querySelector(".vietnix-upload-area");
    if (uploadArea) {
      const fileInfoDiv = document.createElement("div");
      fileInfoDiv.className = "vietnix-file-selected";
      fileInfoDiv.innerHTML =
        '<div class="file-info">' +
        file.name +
        "</div>" +
        '<div class="file-meta">Size: ' +
        this.formatFileSize(file.size) +
        " | Type: " +
        file.type +
        "</div>";

      uploadArea.parentNode.insertBefore(fileInfoDiv, uploadArea.nextSibling);
    }

    // Enable import button
    const importBtn = document.querySelector(".vietnix-import-btn");
    if (importBtn) {
      importBtn.disabled = false;
    }

    // Preview CSV content
    this.previewCSV(file);
  },

  // Preview CSV content
  previewCSV(file) {
    const reader = new FileReader();

    reader.onload = (e) => {
      const csv = e.target.result;
      const lines = csv.split("\n").slice(0, 5); // First 5 lines
      const preview = lines.join("\n");

      // Remove existing preview
      const existingPreview = document.querySelector(".csv-preview");
      if (existingPreview) {
        existingPreview.remove();
      }

      // Add new preview
      const fileSelected = document.querySelector(".vietnix-file-selected");
      if (fileSelected) {
        const previewDiv = document.createElement("div");
        previewDiv.className = "csv-preview";
        previewDiv.innerHTML =
          "<h4>File Preview:</h4>" +
          "<pre>" +
          this.escapeHtml(preview) +
          "</pre>";

        fileSelected.parentNode.insertBefore(
          previewDiv,
          fileSelected.nextSibling
        );
      }
    };
    reader.readAsText(file.slice(0, 1024)); // Read first 1KB
  },

  // Import Form Handler
  initImportForm() {
    const importForm = document.querySelector(".vietnix-import-form");
    if (!importForm) return;

    importForm.addEventListener("submit", (e) => {
      e.preventDefault();

      if (!this.state.uploadedFile) {
        if (window.VietnixUtils) {
          window.VietnixUtils.showMessage(
            "error",
            "Please select a CSV file first."
          );
        }
        return;
      }

      if (this.state.isLoading) {
        return;
      }

      this.processImport();
    });

    // Clear data button
    const clearBtn = document.querySelector(".vietnix-clear-data");
    if (clearBtn) {
      clearBtn.addEventListener("click", (e) => {
        e.preventDefault();

        if (
          !confirm(
            "Are you sure you want to clear all imported data? This action cannot be undone."
          )
        ) {
          return;
        }

        this.clearData();
      });
    }
  },

  // Process CSV Import
  processImport() {
    const formData = new FormData();
    formData.append("action", "vietnix_csv_import");
    formData.append("csv_file", this.state.uploadedFile);

    const overwriteCheckbox = document.querySelector(".vietnix-overwrite");
    formData.append(
      "overwrite_data",
      overwriteCheckbox && overwriteCheckbox.checked ? "1" : "0"
    );
    formData.append("nonce", vietnix_ajax.nonce);

    // Get selected options
    const selectedOptions = [];
    const optionCheckboxes = document.querySelectorAll(
      '.vietnix-import-options input[type="checkbox"]:checked'
    );
    optionCheckboxes.forEach((checkbox) => {
      selectedOptions.push(checkbox.value);
    });
    formData.append("import_options", selectedOptions.join(","));

    this.state.isLoading = true;

    // Update UI
    const importBtn = document.querySelector(".vietnix-import-btn");
    if (importBtn) {
      importBtn.disabled = true;
      importBtn.textContent = "Importing...";
    }

    const progressWrapper = document.querySelector(".vietnix-progress-wrapper");
    if (progressWrapper) {
      progressWrapper.style.display = "block";
    }

    const progressFill = document.querySelector(".vietnix-progress-fill");
    const progressText = document.querySelector(".vietnix-progress-text");

    if (progressFill) progressFill.style.width = "0%";
    if (progressText) progressText.textContent = "Preparing import...";

    // Start import
    const xhr = new XMLHttpRequest();
    this.state.xhr = xhr;

    // Progress tracking
    xhr.upload.addEventListener("progress", (e) => {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        if (progressFill) progressFill.style.width = percent + "%";
        if (progressText)
          progressText.textContent = "Uploading: " + percent + "%";
      }
    });

    xhr.onload = () => {
      try {
        const response = JSON.parse(xhr.responseText);
        if (response.success) {
          if (progressFill) progressFill.style.width = "100%";
          if (progressText)
            progressText.textContent = "Import completed successfully!";

          if (window.VietnixUtils) {
            window.VietnixUtils.showMessage("success", response.data.message);
          }

          // Update statistics
          this.updateStatistics(response.data.stats);

          // Reset form
          this.resetImportForm();

          // Reload data if on data tab
          const dataTab = document.querySelector("#data-tab");
          if (dataTab && dataTab.style.display !== "none") {
            if (window.VietnixViewData) {
              window.VietnixViewData.loadTableData();
            }
          }
        } else {
          if (window.VietnixUtils) {
            window.VietnixUtils.showMessage(
              "error",
              response.data || "Import failed. Please try again."
            );
          }
        }
      } catch (error) {
        if (window.VietnixUtils) {
          window.VietnixUtils.showMessage(
            "error",
            "Invalid response from server."
          );
        }
      }
    };

    xhr.onerror = () => {
      if (window.VietnixUtils) {
        window.VietnixUtils.showMessage(
          "error",
          "Import failed: Network error"
        );
      }
    };

    xhr.onloadend = () => {
      this.state.isLoading = false;
      if (importBtn) {
        importBtn.disabled = false;
        importBtn.textContent = "Import CSV";
      }
      if (progressWrapper) {
        progressWrapper.style.display = "none";
      }
    };

    xhr.open("POST", vietnix_ajax.ajax_url);
    xhr.send(formData);
  },

  // Clear all data
  clearData() {
    const formData = new FormData();
    formData.append("action", "vietnix_csv_clear_data");
    formData.append("nonce", vietnix_ajax.nonce);

    fetch(vietnix_ajax.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (window.VietnixUtils) {
            window.VietnixUtils.showMessage(
              "success",
              "All data cleared successfully."
            );
          }
          this.updateStatistics({ total: 0, active: 0, inactive: 0 });
          if (window.VietnixViewData) {
            window.VietnixViewData.loadTableData();
          }
        } else {
          if (window.VietnixUtils) {
            window.VietnixUtils.showMessage(
              "error",
              data.data || "Failed to clear data."
            );
          }
        }
      })
      .catch((error) => {
        if (window.VietnixUtils) {
          window.VietnixUtils.showMessage(
            "error",
            "Failed to clear data. Please try again."
          );
        }
      });
  },

  // Import Page Form Handler (for backward compatibility)
  initImportPageForm() {
    const importPageForm = document.getElementById("vietnix-csv-import-form");
    if (!importPageForm) return;

    importPageForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const formData = new FormData(importPageForm);
      formData.append("action", "vietnix_import_csv");
      formData.append("nonce", vietnixCSVAdmin.nonce);

      const submitBtn = importPageForm.querySelector("#submit");
      const spinner = importPageForm.querySelector(".spinner");
      const fileInput = document.getElementById("csv_file");

      // Check if file is selected
      if (!fileInput || !fileInput.files.length) {
        alert(
          vietnixCSVAdmin.messages?.selectFile || "Please select a CSV file"
        );
        return;
      }

      // Confirm if clearing existing data
      const clearExisting = document.getElementById("clear_existing");
      if (clearExisting && clearExisting.checked) {
        if (
          !confirm(
            vietnixCSVAdmin.messages?.confirmDelete ||
              "Are you sure you want to delete all existing data before import?"
          )
        ) {
          return;
        }
      }

      if (submitBtn) submitBtn.disabled = true;
      if (spinner) spinner.classList.add("is-active");

      fetch(ajaxurl || vietnixCSVAdmin.ajaxUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert(data.data.message || "Import successful!");
            location.reload();
          } else {
            alert(data.data || "Import failed");
          }
        })
        .catch((error) => {
          alert(
            vietnixCSVAdmin.messages?.connectionError ||
              "Connection error. Please try again."
          );
        })
        .finally(() => {
          if (submitBtn) submitBtn.disabled = false;
          if (spinner) spinner.classList.remove("is-active");
        });
    });

    // Delete all data
    const deleteAllBtn = document.getElementById("delete-all-data");
    if (deleteAllBtn) {
      deleteAllBtn.addEventListener("click", () => {
        if (
          !confirm(
            vietnixCSVAdmin.messages?.confirmDeleteAll ||
              "Are you sure you want to delete ALL data? This action cannot be undone!"
          )
        ) {
          return;
        }

        const formData = new FormData();
        formData.append("action", "vietnix_delete_data");
        formData.append("nonce", vietnixCSVAdmin.nonce);

        fetch(ajaxurl || vietnixCSVAdmin.ajaxUrl, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(data.data || "Data deleted successfully!");
              location.reload();
            } else {
              alert(data.data || "Delete failed");
            }
          })
          .catch((error) => {
            alert("Connection error. Please try again.");
          });
      });
    }
  },

  // Utility Functions
  resetImportForm() {
    this.state.uploadedFile = null;

    const fileInput = document.querySelector(".vietnix-file-input");
    if (fileInput) fileInput.value = "";

    const fileSelected = document.querySelector(".vietnix-file-selected");
    if (fileSelected) fileSelected.remove();

    const csvPreview = document.querySelector(".csv-preview");
    if (csvPreview) csvPreview.remove();

    const importBtn = document.querySelector(".vietnix-import-btn");
    if (importBtn) importBtn.disabled = true;
  },

  updateStatistics(stats) {
    const totalEl = document.querySelector(".stat-total .vietnix-stat-number");
    const activeEl = document.querySelector(
      ".stat-active .vietnix-stat-number"
    );
    const inactiveEl = document.querySelector(
      ".stat-inactive .vietnix-stat-number"
    );

    if (totalEl) totalEl.textContent = stats.total || 0;
    if (activeEl) activeEl.textContent = stats.active || 0;
    if (inactiveEl) inactiveEl.textContent = stats.inactive || 0;
  },

  formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  },

  escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  },
};

// Make VietnixImport available globally
window.VietnixImport = VietnixImport;
