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

  var Tawk_API = Tawk_API || {},
    Tawk_LoadStart = new Date();
  (function () {
    var s1 = document.createElement("script"),
      s0 = document.getElementsByTagName("script")[0];
    s1.async = true;
    s1.src = "https://embed.tawk.to/5d665d6e77aa790be33127f7/default";
    s1.charset = "UTF-8";
    s1.setAttribute("crossorigin", "*");
    s0.parentNode.insertBefore(s1, s0);
  })();

  $("#search-btn").click(function () {
    $("#search-field").toggle();
  });

  jQuery(".gctcf_testimonial-slider").owlCarousel({
    loop: true,
    margin: 32,
    nav: true,
    dot: false,
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      768: {
        items: 1,
      },
      1024: {
        items: 2,
      },
      1100: {
        items: 2,
      },
    },
  });

  jQuery(".gc-tour-destinations-row").owlCarousel({
    loop: true,
    margin: 32,
    nav: true,
    dot: false,
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      767: {
        items: 2,
      },
      768: {
        items: 2,
      },

      1024: {
        items: 2,
      },
      1100: {
        items: 3,
      },
    },
  });

  jQuery(".gctcf-top-hotels").owlCarousel({
    loop: true,
    margin: 32,
    nav: false,
    dot: true,
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: true,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
      },
      768: {
        items: 1,
      },
      1100: {
        items: 3,
      },
    },
  });
  jQuery("#myCarousel").owlCarousel({
    items: 1,
    loop: false,
    center: true,
    margin: 10,
    URLhashListener: true,
    autoplayHoverPause: true,
    startPosition: "URLHash",
  });
  jQuery(document).on("click", ".gctcf-tab", function () {
    jQuery(".gctcf-tab").removeClass("active");
    jQuery(this).addClass("active");
    var tab = jQuery(this).find("a").attr("tab");
    var content = jQuery(".gctcf-tabs").find(".tab-content");
    content.find(".tab-pane").removeClass("active");
    content.find(tab).addClass("active");
  });

  var room_count = 1;
  jQuery("body #btn_room_add_another").on('click',()=> {
    room_count = room_count + 1;
    jQuery(".room_options").append(
      '<div class="form-row mb-3 room_add_on'+
        room_count +
        '"><input type="hidden" name="no_of_room[' +
        room_count +
        ']"  value="' +
        room_count +
        '" /><div class="col-md-2"><label>Room' +
        room_count +
        '</label></div><div class="col-md-4"><select class="selectpicker form-control" name="hotel_adults[' +
        room_count +
        ']"  data-style="btn-white" style="width:100%;"><option value="1">1 Adults</option><option value="2">2 Adults</option><option value="3">3 Adults</option><option value="4">4 Adults</option><option value="5">5 Adults</option><option value="6">6 Adults</option><option value="7">7 Adults</option><option value="8">8 Adults</option><option value="9">9 Adults</option><option value="10">10 Adults</option></select></div><div class="col-md-4"><select class="selectpicker form-control" name="hotel_children[' +
        room_count +
        ']" data-style="btn-white" style="width:100%;"><option value="0">0 Children</option> <option value="1">1 Children</option> <option value="2">2 Children</option> <option value="3">3 Children</option> </select></div><div class="col-md-2 btn_adds"><input type="button" id="btn_room_add_another" class="btn btn-sm btn-outline-danger text-danger ' +
        room_count +
        " room_add_on" +
        room_count +
        '" onclick="delroom(' +
        room_count +
        ')" style="color:#fff;" value="X"></div></div>'
    );
  });

  jQuery("#hotel_night_for_all_device").change(function () {
    var hotel_night_val = jQuery(this).val();
    jQuery("#hotel_night_mobile").val(hotel_night_val);
  });

  jQuery(".gctcf-datepicker").datepicker({
    minDate: new Date(),
    dateFormat: "yy-mm-dd",
  });

  jQuery('#byconsole_giftcard_schedule_send').datepicker({
    minDate: new Date(),
    dateFormat: "yy-mm-dd",
  });

  jQuery("#hotel_result").on("click", "li.region_id_id", function () {
    var li = jQuery(this);
    var li_id_hotel = jQuery(this).attr("id");
    var select_search_by_val = jQuery(this).data("value");
    jQuery("#hotel-name").val(select_search_by_val);
    var select_search_by_region = jQuery(this).val();
    console.log(select_search_by_region); console.log("Hello World!");  
    jQuery("#hotel__region__id").val(jQuery(this).attr("data-region-ids"));
    jQuery("#hotel__country__code").val(jQuery(this).attr("data-country-code"));
    var hotel_country_by_region = jQuery("." + li_id_hotel + "-country").val();
    jQuery("#hotel_country_by_region").val(hotel_country_by_region);
    var lateroom_hotel_region = jQuery(
      "." + li_id_hotel + "-region_name"
    ).val();
    jQuery("#hotel__region__name").val(lateroom_hotel_region);
    jQuery("#hotel_result").css("display", "none").slideUp();
    li.siblings().remove();
  });

  //Tours enquiry
  jQuery(document).on("click", ".gctcf-tour-enquiry", function () {
    var tour_id = jQuery(this).data("id");
    if (tour_id <= 0 || tour_id == undefined) {
      return false;
    }
    jQuery(".gctcf-loader").fadeIn("slow");
    jQuery("#ToursModal .form-error").addClass("hidden");
    jQuery("#ToursModal .alert").addClass("hidden");
    jQuery.ajax({
      type: "post",
      url: gctcf.ajax,
      dataType: "json",
      data: {
        tour_id: tour_id,
        action: "gctc_fetch_tour",
      },
      success: function (data) {
        let html = "";
        html +=
          '<input type="hidden" class="tour-id-js" value="' +
          data.tour_id +
          '">';
        html += '<div class="gc-md-12">';
        html += "<h3>" + data.post_title + "</h3>";
        html += '<p class="text-muted">' + data.sub_heading + "</p>";
        html += '<img src="' + data.images + '" class="d-block">';
        html += "<article>";
        html += data.post_content;
        html += "</article>";
        html += "</div>";
        jQuery("#ToursModal .tour-content-js").html(html);
        jQuery("#ToursModal").fadeIn();
        setTimeout(function () {
          let options = {
            dateFormat: "dd-mm-yy",
          };
          jQuery("#ToursModal .tour-booking-date-js").datepicker(options);
          jQuery("#ToursModal .tour-booking-date-alt-js").datepicker(options);
        }, 100);
        jQuery(".gctcf-loader").fadeOut("slow");
      },
    });
  });
  jQuery(document).on("click", "button.close", function () {
    jQuery("div.modal").fadeOut();
  });

  jQuery(document).on(
    "click",
    "#ToursModal .submit-tour-enquiry-js",
    function (event) {
      jQuery("#ToursModal .form-error").addClass("hidden");
      jQuery("#ToursModal .form-error").html("");
      jQuery(".gctcf-loader").fadeIn("slow");
      let data = {
        action: "gc4t_submit_tour_enquiry",
        tour_id: jQuery("#ToursModal .tour-id-js").val(),
        tour_name: jQuery("#ToursModal .tour-name-js").val(),
        tour_email: jQuery("#ToursModal .tour-email-js").val(),
        tour_contact_number: jQuery(
          "#ToursModal .tour-contact-number-js"
        ).val(),
        tour_booking_date: jQuery("#ToursModal .tour-booking-date-js").val(),
        tour_booking_date_alt: jQuery(
          "#ToursModal .tour-booking-date-alt-js"
        ).val(),
        tour_adults: jQuery("#ToursModal .tour-adults-js").val(),
        tour_children: jQuery("#ToursModal .tour-children-js").val(),
        tour_message: jQuery("#ToursModal .tour-message-js").val(),
      };

      jQuery.ajax({
        type: "post",
        url: gctcf.ajax,
        data: data,
        dataType: "json",
        success: function (response) {
          if (response.res == "success") {
            jQuery("#ToursModal .alert").removeClass("hidden");
            setTimeout(function () {
              jQuery("#ToursModal").fadeOut("slow");
            }, 3000);
          } else {
            if (response.data) {
              for (let formError in response.data) {
                jQuery("#ToursModal .form-error[for=" + formError + "]").html(
                  response.data[formError]
                );
                jQuery(
                  "#ToursModal .form-error[for=" + formError + "]"
                ).removeClass("hidden");
              }
            }
          }
          jQuery(".gctcf-loader").fadeOut("slow");
        },
      });
    }
  );

  //slider js
  var minx = jQuery("#hotel_min_price_option").val();
  var maxx = jQuery("#hotel_max_price_option").val();
  if (maxx == 0) {
    maxx = 300;
  }
  jQuery("#slider-range").slider({
    range: true,
    min: 0,
    max: 500,
    values: [minx, maxx],
    slide: function (event, ui) {
      jQuery("#amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
      jQuery("#hotel_min_price_option").val(ui.values[0]);
      jQuery("#hotel_max_price_option").val(ui.values[1]);
    },
  });
  jQuery("#amount").val(
    "£" +
      jQuery("#slider-range").slider("values", 0) +
      " - £" +
      jQuery("#slider-range").slider("values", 1)
  );

  if (jQuery(".alt_page_navigation").length > 0) {
    jQuery("#paging_container3").pajinate({
      items_per_page: 15,
      item_container_id: ".alt_content",
      nav_panel_id: ".alt_page_navigation",
    });
  }

  jQuery("#datepicker_arriving").datepicker({
    minDate: new Date(),
    dateFormat: "dd/mm/yy",
    onSelect: function (date, obj) {
      return check_return_date_options(date, obj);
    },
  });

  jQuery(document).on("click", ".hotel-thumb", function () {
    jQuery(".hotel-full-img img").attr(
      "src",
      jQuery(this).find("img").attr("src")
    );
  });

  jQuery(document).on("change", "#gctcf_prices", function () {
    jQuery("input[name='hotel_sort_prices']").val(jQuery(this).val());
  });

  jQuery(document).on("change", "#gctcf_type", function () {
      jQuery("input[name='hotel_sort_type']").val(jQuery(this).val());
  });

  jQuery(document).on("change", "#gctcf_ratings", function () {
    jQuery("input[name='hotel_sort_ratings']").val(jQuery(this).val());
  });

  jQuery(document).on("change", ".gctcf-hotel-rating", function () {
    jQuery(".hotel_star_rating_option").val(jQuery(this).val());
    //jQuery('#roomxml_hotel_search').trigger('click');
  });

  jQuery(document).on("click", "#price_range_apply", function () {
    var hotel_min_price = jQuery("#slider-range").slider("values", 0);
    var hotel_max_price = jQuery("#slider-range").slider("values", 1);

    jQuery("#hotel_min_price_option").val(hotel_min_price);
    jQuery("#hotel_max_price_option").val(hotel_max_price);
    jQuery("#roomxml_hotel_search").trigger("click");
  });

  $("#datepicker_return").datepicker({
    minDate: new Date(),
    dateFormat: "dd/mm/yy",
  });

  if ($("#toggle-on").is(":checked")) {
    $("input[name='vehicle_return_date']").prop("disabled", true);
  }
  jQuery("#hotel_result_transfer").on("click", "li.hotel_id_id", function () {
    var li_id = jQuery(this).attr("id");
    var select_search_by_val = jQuery(this).data("value");
    jQuery("#hotel_search_transfer").val(select_search_by_val);
    jQuery("#hotel_result_transfer").css("display", "none").slideUp();

    var hotel_location_by_code = jQuery("." + li_id + "_iata").val();
    jQuery("#hotel__code__name").val(hotel_location_by_code);

    var hotel_location_by_latitude_dep = jQuery(
      "." + li_id + "_latitude"
    ).val();
    jQuery("#hotel__location__by__latitude_dep").val(
      hotel_location_by_latitude_dep
    );

    var hotel_location_by_longitude_dep = jQuery(
      "." + li_id + "_longitude"
    ).val();
    jQuery("#hotel__location__by__longitude_dep").val(
      hotel_location_by_longitude_dep
    );

    var hotel_location_by_type_dep = jQuery("." + li_id + "_ltype").val();
    jQuery("#location__type__code_dep").val(hotel_location_by_type_dep);
  });
  jQuery("#hotel_result_arrival").on(
    "click",
    "li.hotel_id_by_name",
    function () {
      var li_id = jQuery(this).attr("id");

      var select_search_by_val = jQuery(this).data("value");
      jQuery("#hotel_search_arrival").val(select_search_by_val);
      jQuery("#hotel_result_arrival").css("display", "none").slideUp();

      var hotel_location_by_code = jQuery("." + li_id + "_iata").val();
      jQuery("#hotel__code__name_arrival").val(hotel_location_by_code);

      var hotel_location_by_latitude_arrival = jQuery(
        "." + li_id + "_latitude"
      ).val();
      jQuery("#hotel__location__by__latitude_arrival").val(
        hotel_location_by_latitude_arrival
      );

      var hotel_location_by_longitude_arrival = jQuery(
        "." + li_id + "_longitude"
      ).val();
      jQuery("#hotel__location__by__longitude_arrival").val(
        hotel_location_by_longitude_arrival
      );

      var hotel_location_by_type_arrival = jQuery("." + li_id + "_ltype").val();
      jQuery("#location__type__code_arrival").val(
        hotel_location_by_type_arrival
      );
    }
  );

  jQuery("#country_code_result").on("click", "li.country_id_id", function () {
    var select_search_by_val = jQuery(this).data("value");
    jQuery("#country-name").val(select_search_by_val);
    jQuery("#country_code_result").css("display", "none").slideUp();
    var country_code = jQuery("#country-chort-name").val();
    jQuery("#cars__country__code").val(country_code);
  });

  jQuery(".toggle").click(function (e) {
    e.preventDefault();

    var $this = jQuery(this);

    if ($this.next().hasClass("show")) {
      $this.next().removeClass("show");
      $this.next().slideUp(350);
    } else {
      $this.parent().find("li .inner").removeClass("show");
      $this.parent().find("li .inner").slideUp(350);
      $this.next().toggleClass("show");
      $this.next().slideToggle(350);
    }
  });

  jQuery("#owl-hoel-full-image").owlCarousel({
    loop: true,
    margin: 32,
    items: 1,
    nav: true,
    dot: false,
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: true,
    responsiveClass: true,
  });

  var disabledDates = jQuery("#avilableDates").attr("data-date");
  var date1 = jQuery("#avilableDates").attr("data-avilable_date");
  var month = jQuery("#avilableDates").attr("data-avilable_month");
  var year = jQuery("#avilableDates").attr("data-avilable_year");
  var avilableD = jQuery("#avilableDates").attr("data-avilable");
  jQuery("#datepicker").datepicker({
    minDate: new Date(year, month - 1, date1),
    beforeShowDay: function (date) {
      var string = jQuery.datepicker.formatDate("yy-mm-dd", date);
      return [disabledDates.indexOf(string) != -1];
    },
    dateFormat: "MM dd, yy",
  });
  jQuery("#datepicker").datepicker("setDate", avilableD);

  var avilableDeparture = jQuery("#avilableDeparture").attr("data-avilable");
  jQuery("#datepickerBooking").datepicker({
    minDate: 0,
    beforeShowDay: function (date) {
      var string = jQuery.datepicker.formatDate("yy-mm-dd", date);
      return [avilableDeparture.indexOf(string) != -1];
    },
    dateFormat: "yy-mm-dd",
  });

  jQuery(document).on("click", "div.qtyminus, div.qtyplus", function () {
    var qty = jQuery(this).closest(".qty-button-wrap").find(".qty");
    var ticket_price = jQuery(this)
      .closest(".ticket-wrap")
      .find(".ticket-price-pure");
    var total_price = 0;
    var val = parseFloat(qty.val());
    var max = parseFloat(qty.attr("max"));
    var min = parseFloat(qty.attr("min"));
    var step = parseFloat(qty.attr("step"));
    if (jQuery(this).is(".qtyplus")) {
      if (max && max <= val) {
        qty.val(max);
        qty.attr("data-value", max);
      } else {
        qty.val(val + step);
        qty.attr("data-value", val + step);
      }
    } else {
      if (min && min >= val) {
        qty.val(min);
        qty.attr("data-value", min);
      } else if (val >= 1) {
        qty.val(val - step);
        qty.attr("data-value", val - step);
      }
    }

    jQuery("input.qty").each(function (index, value) {
      var quantity = jQuery(this).attr("data-value");
      var price = jQuery(this).attr("data-price");
      total_price += parseInt(quantity) * parseInt(price);
    });
    jQuery(".gc-tour-total-price").html("<span>£" + total_price + "</span>");
    jQuery(".ticket-total-price").val(total_price);
  });
})(jQuery);

function delroom(pid) {
  input_class_to_remove1 = pid;
  if (confirm("Are you sure you want to delete this?")) {
    jQuery("div.room_add_on" + pid).remove();
    return true;
  } else {
    return false;
  }
}
function hotel_search() {
  hotel_region_name = jQuery("#hotel-name").val();
  hotel_name_char = jQuery("#hotel-name").val().length;
  jQuery("#hotel_result").css("display", "block");

  if (hotel_name_char < 3 || hotel_name_char == gctcf.prevSearch) {
    return false;
  }
  gctcf.prevSearch = hotel_name_char;
  clearTimeout(gctcf.searchTimeout);
  gctcf.searchTimeout = setTimeout(function() {
    do_hotel_search(hotel_region_name);
  }, 500);
}

function do_hotel_search(hotel_region_name) {

jQuery("#hotel-name").after('<img id="dot_spinner" src="https://staging.travelgift.uk/wp-content/uploads/2023/11/Spinner.gif" style="position: absolute; margin-left: -54px; margin-top: -6px; z-index: 999;">');

  jQuery.ajax({
    type: "post",
    url: gctcf.ajax,
    data: {
      hotel_region_list: hotel_region_name,
      action: "gctcf_hotel_search",
    },
    success: function (data) {
    	jQuery('#dot_spinner').hide();
      //clearTimeout(gctcf.searchTimeout);
      jQuery("#hotel_result").html(data);
    },
  });
}

function arrivingType() {
  jQuery('input[name="arrving_type"]').parent().removeClass("active");
  if (jQuery("#toggle-on").is(":checked")) {
    jQuery("input[name='vehicle_return_date']").prop("disabled", true);
  } else if (jQuery("#toggle-off").is(":checked")) {
    jQuery("input[name='vehicle_return_date']").prop("disabled", false);
  }
  jQuery('input[name="arrving_type"]:checked').parent().addClass("active");
}
function hotel_facility_ajax(clickedID) {
  var hotel_id_by_ajax = jQuery("#" + clickedID).val();
  var feed = jQuery("#" + clickedID).attr("data-feed");
  jQuery(".gctcf-loader").fadeIn("slow");
  jQuery.ajax({
    type: "post",
    url: gctcf.ajax,
    dataType: "json",
    data: {
      hotel__id: hotel_id_by_ajax,
      feed: feed,
      action: "gctc_hotel_quick_view",
    },
    success: function (data) {
      if (data.res == "success") {
        jQuery("#hotel_facility_result").html(data.html);
        jQuery("#hotel_facility_result")
          .parents(".product_view")
          .fadeIn("slow");
      }
      jQuery(".gctcf-loader").fadeOut("slow");
    },
  });
}

function check_return_date_options(date, obj) {
  var todays_date = new Date();
  var todays_date_month = todays_date.getMonth() + 1;
  var todays_date_date = todays_date.getDate();
  if (todays_date_month < 10) {
    todays_date_month = "0" + todays_date_month;
  } else {
    todays_date_month = todays_date_month;
  }

  if (todays_date_date < 10) {
    todays_date_date = "0" + todays_date_date;
  } else {
    todays_date_date = todays_date_date;
  }

  var todays_date_format =
    todays_date_month +
    "/" +
    todays_date_date +
    "/" +
    "/" +
    todays_date.getFullYear();
  var byc_oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
  var byc_todays_date_val = new Date();
  var byc_cal_pick_date_val = new Date(date);
  var bycspd_diffDays = Math.round(
    Math.abs(
      (byc_todays_date_val.getTime() - byc_cal_pick_date_val.getTime()) /
        byc_oneDay
    )
  );
}

function checkSpcialChar(event) {
  if (
    !(
      (event.keyCode >= 65 && event.keyCode <= 90) ||
      (event.keyCode >= 97 && event.keyCode <= 122) ||
      (event.keyCode >= 48 && event.keyCode <= 57)
    )
  ) {
    event.returnValue = false;
    return;
  }
  event.returnValue = true;
}

function couponCode() {
  var coupon_code = jQuery("#coupon_code_value").val();
  var total_booking_payment = jQuery('input[name="gctcf_amount"]').val();
  jQuery("#coupon_code_smg").html("");
  jQuery(".gctcf-loader").fadeIn("slow");
  var check_coupon = jQuery.ajax({
    type: "post",
    url: gctcf.ajax,
    data: {
      action: "gctc_coupon_search",
      coupon_code_search: coupon_code,
      quote_id: jQuery("#coupon_code_quote_id").val(),
      post_id: jQuery("#hotel_post_id").val(),
      total_price: total_booking_payment,
      feed_type: jQuery('input[name="gctcf_feed_type"]').val(),
    },
    success: function (data) {},
  });
  check_coupon.done(function (response) {
    //alert('ajax success.......');
    jQuery(".gctcf-loader").fadeOut("slow");
    jQuery("#coupon_code_smg").html(response);
    if (response > 0) {
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_valid">Valid coupon code.</div>'
      );
      jQuery(".gctcf_coupon_code").val(coupon_code);
      jQuery(".gctcf_coupon_amount").val(response);
      if (parseInt(total_booking_payment) > parseInt(response)) {
        jQuery(".byc_coupon_amount").val(response);
        jQuery("#byc_coupon_amount_total").html(response);
        var neat_amount_to_pay = total_booking_payment - response;
        jQuery("#hotel_amount_total").html("£" + neat_amount_to_pay.toFixed(2));
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
      } else {
        var amount = 1;
        jQuery(".byc_travel_amount").val(amount);
        jQuery("#hotel_amount_total").html("£" + amount + ".00");
        jQuery("#byc_coupon_amount_total").html(response);
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
      }
    } else {
      jQuery(".gctcf_coupon_code").val("");
      jQuery(".gctcf_coupon_amount").val("");
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_invalid">Invalid coupon code..!!</div>'
      );
      jQuery(".byc_coupon_amount").val(0);
      jQuery(".byc_travel_amount").val(total_booking_payment);
      jQuery("#coupon_price_hidden").css("display", "none");
      jQuery(".coupon_price_hidden").css("display", "none");
      jQuery("#hotel_amount_total").html("£" + total_booking_payment);
    }
  });
}

function attractioncouponCode() {
  var coupon_code = jQuery("#coupon_code_value").val();
  var total_booking_payment = jQuery('input[name="gctcf_amount"]').val();
  jQuery("#coupon_code_smg").html("");
  jQuery(".gctcf-loader").fadeIn("slow");
  var check_coupon = jQuery.ajax({
    type: "post",
    url: gctcf.ajax,
    data: {
      action: "gctc_attractioncoupon_search",
      coupon_code_search: coupon_code,
      quote_id: jQuery("#coupon_code_quote_id").val(),
      total_price: total_booking_payment,
    },
    success: function (data) {},
  });
  check_coupon.done(function (response) {
    //alert('ajax success.......');
    jQuery(".gctcf-loader").fadeOut("slow");
    jQuery("#coupon_code_smg").html(response);
    if (response > 0) {
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_valid">Valid coupon code.</div>'
      );
      jQuery(".gctcf_coupon_code").val(coupon_code);
      jQuery(".gctcf_coupon_amount").val(response);
      if (parseInt(total_booking_payment) > parseInt(response)) {
        jQuery(".byc_coupon_amount").val(response);
        jQuery("#byc_coupon_amount_total").html(response);
        var neat_amount_to_pay = total_booking_payment - response;
        jQuery("#attraction_amount_total").html(
          "£" + neat_amount_to_pay.toFixed(2)
        );
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
        jQuery(".byc_attraction_amount").val(neat_amount_to_pay);
      } else {
        var amount = 1;
        jQuery(".byc_travel_amount").val(amount);
        jQuery("#attraction_amount_total").html("£" + amount + ".00");
        jQuery("#byc_coupon_amount_total").html(response);
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
        jQuery(".byc_attraction_amount").val(amount);
      }
    } else {
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_invalid">Invalid coupon code..!!</div>'
      );
      jQuery(".gctcf_coupon_code").val("");
      jQuery(".gctcf_coupon_amount").val("");
      jQuery(".byc_attraction_amount").val(total_booking_payment);
      jQuery("#coupon_price_hidden").css("display", "none");
      jQuery(".coupon_price_hidden").css("display", "none");
      jQuery("#attraction_amount_total").html(
        "£" + total_booking_payment + ".00"
      );
    }
  });
}
function gctcf_show_loader() {
  jQuery(".gctcf-loader, .loader-message").show();
}
function gctcf_loader() {
  jQuery(".gctcf-loader").show();
}
function country_search() {
  country_name = jQuery("#country-name").val();
  country_name_char = jQuery("#country-name").val().length;
  if (country_name_char < 3) {
    return false;
  }

  jQuery.ajax({
    type: "post",
    url: gctcf.ajax,
    data: { action: "gctcf_get_countries", cars_country_name: country_name },
    success: function (data) {
      jQuery("#country_code_result").html(data);
      jQuery("#country_code_result").slideDown();
    },
  });
}
function transfer_couponCode() {
  var coupon_code = jQuery("#coupon_code_value").val();
  var total_booking_payment = jQuery('input[name="gctcf_amount"]').val();
  jQuery("#coupon_code_smg").html("");
  jQuery(".gctcf-loader").fadeIn("slow");
  var tansfer_check_coupon = jQuery.ajax({
    type: "post",
    url: gctcf.ajax,
    data: {
      action: "gctc_transfer_coupon_search",
      coupon_code_search: coupon_code,
      total_price: total_booking_payment,
    },
    success: function (data) {},
  });
  tansfer_check_coupon.done(function (response) {
    //alert('ajax success.......');
    jQuery(".gctcf-loader").fadeOut("slow");
    jQuery("input[name='transfer_gctcf_coupon_amount']").val(0);
    jQuery("input[name='transfer_gctcf_coupon_code']").val("");
    jQuery(".byc_coupon_amount").val(response);
    jQuery("#coupon_code_smg").html("");
    if (response > 0) {
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_valid">Valid coupon code.</div>'
      );

      jQuery("input[name='transfer_gctcf_coupon_code']").val(coupon_code);

      if (total_booking_payment > response) {
        jQuery(".byc_coupon_amount").val(response);
        jQuery("#byc_coupon_amount_total").html(response);
        var neat_amount_to_pay = total_booking_payment - response;
        jQuery("#transfer_amount_total").html(
          "£" + neat_amount_to_pay.toFixed(2)
        );
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
        jQuery("input[name='transfer_gctcf_coupon_amount']").val(response);
      } else {
        var amount = 1;
        jQuery(".byc_travel_amount").val(amount);
        jQuery("#transfer_amount_total").html("£" + amount + ".00");
        jQuery("#byc_coupon_amount_total").html(response);
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
        jQuery("input[name='transfer_gctcf_coupon_amount']").val(1);
      }
    } else {
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_invalid">Invalid coupon code..!!</div>'
      );
    }
  });
}