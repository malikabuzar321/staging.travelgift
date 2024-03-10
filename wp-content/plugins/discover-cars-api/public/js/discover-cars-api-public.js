(function ($) {
  "use strict";

  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  var xhr;
  var start_date = null,
    end_date = null;
  var timestamp_start_date = null,
    timestamp_end_date = null;
  var $input_start_date = null,
    $input_end_date = null;

  $(document).ready(function () {
    var options_start_date = {
      showAnim: false,
      constrainInput: true,
      numberOfMonths: 2,
      showOtherMonths: true,
      minDate: new Date(),
      setDate: new Date(),
      dateFormat: "dd-mm-yy",
      beforeShowDay: function (date) {
        // 0: published
        // 1: class
        // 2: tooltip
        var timestamp_date = date.getTime();
        var result = getDateClass(
          timestamp_date,
          timestamp_start_date,
          timestamp_end_date
        );
        if (result != false) return result;

        return [true, "", ""];
        // return [ true, "chocolate", "Chocolate! " ];
      },
      onSelect: function (date_string, datepicker) {
        // this => input
		start_date = $input_start_date.datepicker("getDate");
        timestamp_start_date = start_date.getTime();
        $input_end_date.datepicker("option", "minDate", date_string);
      },
      onClose: function () {
        if (end_date != null) {
          if (timestamp_start_date >= timestamp_end_date || end_date == null) {
            $input_end_date.datepicker("setDate", null);
            end_date = null;
            timestamp_end_date = null;
            $input_end_date.datepicker("show");
            return;
          }
        }
        if (start_date != null && end_date == null)
          $input_end_date.datepicker("show");
      },
    };
    var options_end_date = {
      showAnim: false,
      constrainInput: true,
      numberOfMonths: 2,
      showOtherMonths: true,
      dateFormat: "dd-mm-yy",
      beforeShowDay: function (date) {
        var timestamp_date = date.getTime();
        var result = getDateClass(
          timestamp_date,
          timestamp_start_date,
          timestamp_end_date
        );
        if (result != false) return result;

        return [true, "", "Chocolate !"];
      },
      onSelect: function (date_string, datepicker) {
        // this => input
        end_date = $input_end_date.datepicker("getDate");
        timestamp_end_date = end_date.getTime();
      },
      onClose: function () {
        if (end_date == null) return;

        if (timestamp_end_date <= timestamp_start_date || start_date == null) {
          $input_start_date.datepicker("setDate", null);
          start_date = null;
          timestamp_start_date = null;
          $input_start_date.datepicker("show");
        }
      },
    };

    $input_start_date = jQuery("#dca-pickup-date");
    $input_end_date = jQuery("#dca-drop-date");
    $("#dca-pickup-date").datepicker("setDate", new Date());
    $input_start_date.datepicker(options_start_date);
    $input_end_date.datepicker(options_end_date);
    $(".dca-location").autocomplete({
      source: function (request, response) {
        //$(this).parent().find(".dca-search-placeholder").show();
        //console.log($(this));
        if (xhr) {
          xhr.abort();
        }
        xhr = $.ajax({
          type: "POST",
          url: dca.ajaxurl,
          dataType: "json",
          data: {
            action: "dca_search_locations",
            s: request.term,
          },
          success: function (data) {
            response(data);
          },
        });
      },
      search: function (e, u) {
        $(this)
          .parent()
          .find(".dca-search-placeholder")
          .text("we are currently searching...")
          .show();
        $(".dca-err").remove();
      },
      response: function (e, u) {
        //console.log(u)
        $(this).parent().find(".dca-search-placeholder").hide();
        if (!u.content.length) {
          $(this)
            .parent()
            .find(".dca-search-placeholder")
            .text("Unable to find location")
            .show();
        }
      },
      //minLength: 3,
      select: function (event, ui) {
        // Set selection
        var el = $(this);
        if (el.attr("id") == "dca-pickup-location") {
          $("#dca-pickup-location").val(ui.item.label);
          $("input[name='dca-pickup-id']").val(ui.item.value);
          $("input[name='dca-pickup-country']").val(ui.item.country);
          if ($("#is-drop-off").is(":checked")) {
            $("#dca-dropoff-location").val(ui.item.label);
            $("input[name='dca-dropoff-id']").val(ui.item.value);
            $("input[name='dca-dropoff-country']").val(ui.item.country);
          }
        } else if (el.attr("id") == "dca-dropoff-location") {
          $("#dca-dropoff-location").val(ui.item.label);
          $("input[name='dca-dropoff-id']").val(ui.item.value);
          $("input[name='dca-dropoff-country']").val(ui.item.country);
        }

        return false;
      },
      open: function () {
        $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
      },
      close: function () {
        $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
      },
    });

    $(document).on("change", "#is-drop-off", function () {
      if ($(this).is(":checked")) {
        $(".dca-dropoff-sec").hide();
        $("#dca-dropoff-location").val($("#dca-pickup-location").val());
        $("input[name='dca-dropoff-id']").val(
          $("input[name='dca-pickup-id']").val()
        );
        $("input[name='dca-dropoff-country']").val(
          $("input[name='dca-pickup-country']").val()
        );
      } else {
        $(".dca-dropoff-sec").show();
        $("#dca-dropoff-location").val("");
        $("input[name='dca-dropoff-id']").val("");
        $("input[name='dca-dropoff-country']").val("");
      }
    });

    $(document).on("click", ".dca-show-options", function () {
      $(this).parents("ul").find(".dca-more-options").fadeToggle();
      var count = $(this).data("count");

      $(this).text(function (i, text) {
        return text === "Hide " + count + " more"
          ? "Show " + count + " more"
          : "Hide " + count + " more";
      });
    });

    $(document).on("submit", "#gc-search-box", function (e) {
      e.preventDefault();
      $(".dca-err").remove();
      $(".dca-search-placeholder").hide();
      var pick = $("#dca-pickup-location").val();
      var pick_id = $("input[name='dca-pickup-id']").val();
      if (pick_id == "" || pick_id <= 0 || pick == "") {
        $("input[name='dca-pickup-id']")
          .parent()
          .append('<label class="dca-err">Please select a location</label>');
        return false;
      }
      var drop = $("#dca-dropoff-location").val();
      var drop_id = $("input[name='dca-dropoff-id']").val();
      if ((drop_id == "" || drop == "") && !$("#is-drop-off").is(":checked")) {
        $("input[name='dca-dropoff-id']")
          .parent()
          .append('<label class="dca-err">Please select a location</label>');
        return false;
      }
      $(".dca-loader").fadeIn();
      var form = $("#gc-search-box");
      $.ajax({
        type: "POST",
        url: dca.ajaxurl,
        //dataType: "json",
        data: form.serialize(),

        success: function (data) {
          $(".dca-loader").fadeOut();
          $("#ajax-response").html(data);
          $(".dca-loader").fadeOut();
          $("html, body").animate(
            {
              scrollTop: $("#ajax-response").offset().top,
            },
            2000
          );
        },
      });
    });
  });

  function getDateClass(date, start, end) {
    if (end != null && start != null) {
      if (date > start && date < end) return [true, "sejour", "Séjour"];
    }

    if (date == start) return [true, "start", "Début de votre séjour"];
    if (date == end) return [true, "end", "Fin de votre séjour"];

    return false;
  }
})(jQuery);
jQuery(document).ready(function () {});
