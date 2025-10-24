/**
 * OnTap Admin JavaScript
 */

(function ($) {
  "use strict";

  var OlogyBrewing = {
    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      // Sync button
      $("#start-sync").on("click", this.handleSync);

      // Clear logs button
      $("#clear-logs").on("click", this.handleClearLogs);

      // Settings form
      $("#ontap-settings-form").on("submit", this.handleSaveSettings);
    },

    handleSync: function (e) {
      e.preventDefault();

      var $button = $(this);
      var $container = $button.closest(".ontap-card");

      $button.prop("disabled", true).text("Starting...");
      $container.addClass("ontap-loading");

      $.ajax({
        url: ologyBrewing.ajaxUrl,
        type: "POST",
        data: {
          action: "ontap_sync",
          nonce: ologyBrewing.nonce,
        },
        success: function (response) {
          if (response.success) {
            OlogyBrewing.showNotice(
              ologyBrewing.strings.syncStarted,
              "success"
            );
            // Refresh logs after a delay
            setTimeout(function () {
              location.reload();
            }, 2000);
          } else {
            OlogyBrewing.showNotice("Error: " + response.data.message, "error");
          }
        },
        error: function () {
          OlogyBrewing.showNotice(
            "An error occurred while starting sync.",
            "error"
          );
        },
        complete: function () {
          $button.prop("disabled", false).text("Start Manual Sync");
          $container.removeClass("ontap-loading");
        },
      });
    },

    handleClearLogs: function (e) {
      e.preventDefault();

      if (!confirm("Are you sure you want to clear all logs?")) {
        return;
      }

      var $button = $(this);
      $button.prop("disabled", true).text("Clearing...");

      $.ajax({
        url: ologyBrewing.ajaxUrl,
        type: "POST",
        data: {
          action: "ontap_clear_logs",
          nonce: ologyBrewing.nonce,
        },
        success: function (response) {
          if (response.success) {
            OlogyBrewing.showNotice(
              ologyBrewing.strings.logsCleared,
              "success"
            );
            setTimeout(function () {
              location.reload();
            }, 1000);
          } else {
            OlogyBrewing.showNotice("Error: " + response.data.message, "error");
          }
        },
        error: function () {
          OlogyBrewing.showNotice(
            "An error occurred while clearing logs.",
            "error"
          );
        },
        complete: function () {
          $button.prop("disabled", false).text("Clear Logs");
        },
      });
    },

    handleSaveSettings: function (e) {
      e.preventDefault();

      var $form = $(this);
      var $submit = $form.find('input[type="submit"]');
      var originalText = $submit.val();

      $submit.prop("disabled", true).val("Saving...");

      $.ajax({
        url: ologyBrewing.ajaxUrl,
        type: "POST",
        data: {
          action: "ontap_save_settings",
          nonce: ologyBrewing.nonce,
          ...$form.serialize(),
        },
        success: function (response) {
          if (response.success) {
            OlogyBrewing.showNotice(
              ologyBrewing.strings.settingsSaved,
              "success"
            );
          } else {
            OlogyBrewing.showNotice("Error: " + response.data.message, "error");
          }
        },
        error: function () {
          OlogyBrewing.showNotice(
            "An error occurred while saving settings.",
            "error"
          );
        },
        complete: function () {
          $submit.prop("disabled", false).val(originalText);
        },
      });
    },

    showNotice: function (message, type) {
      type = type || "info";

      var $notice = $(
        '<div class="ontap-notice ' + type + '">' + message + "</div>"
      );

      // Remove existing notices
      $(".ontap-notice").remove();

      // Add new notice
      $(".wrap h1").after($notice);

      // Auto-hide after 5 seconds
      setTimeout(function () {
        $notice.fadeOut(function () {
          $(this).remove();
        });
      }, 5000);
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    OlogyBrewing.init();
  });
})(jQuery);
