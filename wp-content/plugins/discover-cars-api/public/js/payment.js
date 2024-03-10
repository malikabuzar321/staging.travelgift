var Days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
jQuery(document).ready(function ($) {
  var option = '<option value="0">day</option>';
  var selectedDay = "day";
  for (var i = 1; i <= Days[0]; i++) {
    option += '<option value="' + i + '">' + i + "</option>";
  }
  $("#booking_day").append(option);
  $("#booking_day").val(0);

  var monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];
  var option = '<option value="0">month</option>';
  var selectedMon = "month";
  $.each(monthNames, function (index, value) {
    index++;
    option += '<option value="' + index + '">' + value + "</option>";
  });
  $("#booking_month").append(option);
  $("#booking_month").val(0);

  var d = new Date();
  var option = '<option value="0">year</option>';
  selectedYear = "year";
  for (var i = 1930; i <= d.getFullYear(); i++) {
    // years start i
    option += '<option value="' + i + '">' + i + "</option>";
  }
  $("#booking_year").append(option);
  $("#booking_year").val(0);

  $(document).on("click", ".book-now_btn", function (e) {
    e.preventDefault();
    if (form_error()) {
      return false;
    }
    var data = $("#rob-book-car").serialize();
    $(".booking-res").remove();
    var postdata = {
      action: "dca_paypal_pament",
      data: data,
    };
    $(".dca-search-heading").hide();
    $(".dca-loader").fadeIn();
    $.ajax({
      type: "POST",
      dataType: "json",
      url: payment.ajax,
      data: postdata,
      success: function (response) {
        if (response.res == "success") {
          $("#paypal-booking-id-cstm").val(response.booking_id);
          $("#paypal-form").submit();
        } else {
          $(".book-now_btn").after(
            '<p class="booking-res error">' + response.msg + "</p>"
          );
          $(".dca-search-heading").show();
          $(".dca-loader").fadeOut();
        }
      },
    });
  });

  // $(document).on("click", ".gc_btn_price_view", function (e) {
  //   var car_amount = $(this).siblings(".car_amount").val();
  //   var itemname = $(this).siblings(".itemName").val();
  //   var vehicleImage = $(this).siblings(".vehicleImage").val();
  //   var car_seats = $(this).siblings(".car_seats").val();
  //   var car_bags = $(this).siblings(".car_bags").val();
  //   var car_doors = $(this).siblings(".car_doors").val();
  //   var car_ac = $(this).siblings(".car_ac").val();
  //   var rentdays = $(this).siblings(".rentdays").val();
  //   var fuel_policy = $(this).siblings(".fuel_policy").val();
  //   var pickup_id = $(this).siblings(".pickup_id").val();
  //   var features = $(this)
  //     .parents(".gc_car_box_sec")
  //     .find(".gc-car-feature")
  //     .html();

  //   var postdata = {
  //     action: "dca_booking_form",
  //     amount: car_amount,
  //     name: itemname,
  //     image: vehicleImage,
  //     seats: car_seats,
  //     bags: car_bags,
  //     doors: car_doors,
  //     ac: car_ac,
  //     days: rentdays,
  //     fuel_policy: fuel_policy,
  //     pickup_id: pickup_id,
  //   };
  //   // console.log(postdata);
  //   // return false;
  //   $.ajax({
  //     type: "POST",
  //     dataType: "json",
  //     url: payment.ajax,
  //     data: postdata,
  //     success: function (response) {
  //       //window.open( response.link, '_blank');
  //       window.location.href = response.link;
  //     },
  //   });
  // });

  jQuery(document).on(
    "input keypress keyup change",
    ".validate-input",
    function () {
      form_error();
    }
  );
});

function form_error() {
  var error = false;
  jQuery(".dca-booking-err").text("").removeClass("show");
  jQuery(".validate-input").removeClass("error");
  filter = /^\d*(?:\.\d{1,2})?$/;
  jQuery(".validate-input").each(function () {
    var $this = jQuery(this);
    if ($this.val() == "" || $this.val() == 0 || $this.val() == undefined) {
      $this.addClass("error");
      error = true;
    } else if ($this.attr("name") == "driver_email") {
      if ($this.val() != "" && !IsEmail($this.val())) {
        $this.addClass("error");
        error = true;
      }
    } else if ($this.attr("name") == "driver-phone") {
      if (!filter.test($this.val())) {
        $this.addClass("error");
        error = true;
      }
    } else if ($this.attr("name") == "country_code") {
      if (!filter.test($this.val()) || $this.val().length > 3) {
        $this.addClass("error");
        error = true;
      }
    }
  });
  var el = jQuery("#rob-book-car").find(".error").first();
  if (el.length > 0) {
    if (el.val() == "" || el.val() == 0 || el.val() == undefined) {
      jQuery(".dca-booking-err").text(el.attr("data-valid"));
    } else {
      jQuery(".dca-booking-err").text(el.attr("data-valid2"));
    }

    jQuery(".dca-booking-err").addClass("show");
  }
  console.log(error);
  return error;
}
function IsEmail(email) {
  var regex =
    /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  if (!regex.test(email)) {
    return false;
  } else {
    return true;
  }
}

function isLeapYear(year) {
  year = parseInt(year);
  if (year % 4 != 0) {
    return false;
  } else if (year % 400 == 0) {
    return true;
  } else if (year % 100 == 0) {
    return false;
  } else {
    return true;
  }
}

function change_year(select) {
  if (isLeapYear(jQuery(select).val())) {
    Days[1] = 29;
  } else {
    Days[1] = 28;
  }
  if (jQuery("#month").val() == 2) {
    var day = jQuery("#day");
    var val = jQuery(day).val();
    jQuery(day).empty();
    var option = '<option value="day">day</option>';
    for (var i = 1; i <= Days[1]; i++) {
      //add option days
      option += '<option value="' + i + '">' + i + "</option>";
    }
    jQuery(day).append(option);
    if (val > Days[month]) {
      val = 1;
    }
    jQuery(day).val(val);
  }
}

function change_month(select) {
  var day = jQuery("#day");
  var val = jQuery(day).val();
  jQuery(day).empty();
  var option = '<option value="day">day</option>';
  var month = parseInt(jQuery(select).val()) - 1;
  for (var i = 1; i <= Days[month]; i++) {
    //add option days
    option += '<option value="' + i + '">' + i + "</option>";
  }
  jQuery(day).append(option);
  if (val > Days[month]) {
    val = 1;
  }
  jQuery(day).val(val);
}
function carcouponsearchCode() {
  var coupon_code = jQuery("#coupon_code_value").val();
  var total_booking_payment = jQuery("#car_amount").val();
  jQuery("#coupon_code_smg").html("");
  jQuery(".gctcf-loader").fadeIn("slow");
  var check_coupon = jQuery.ajax({
    type: "post",
    url: dca.ajaxurl,
    data: {
      action: "gctc_car_coupon_search",
      coupon_code_search: coupon_code,
      total_price: total_booking_payment,
    },
    success: function (data) {},
  });
  check_coupon.done(function (response) {
    //alert('ajax success.......');
    jQuery(".gctcf-loader").fadeOut("slow");
    jQuery("#coupon_code_smg").html(response.data.amount);
    if (response.data.amount > 0) {
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_valid">Valid coupon code.</div>'
      );
      jQuery("#discount_coupen_code").val(coupon_code);
      jQuery("#discount_coupen_value").val(response.data.amount);
      if (parseInt(total_booking_payment) > parseInt(response.data.amount)) {
        jQuery(".byc_coupon_amount").val(response.data.amount);
        jQuery("#byc_coupon_amount_total").html(response.data.amount);
        var neat_amount_to_pay = total_booking_payment - response.data.amount;
        jQuery("#car_book_amount_total").html(
          "£" + neat_amount_to_pay.toFixed(2)
        );
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
      } else {
        var amount = 1;
        jQuery(".byc_coupon_amount").val(amount);
        jQuery("#car_book_amount_total").html("£" + amount + ".00");
        jQuery("#byc_coupon_amount_total").html(response.data.amount);
        jQuery("#coupon_price_hidden").css("display", "block");
        jQuery(".coupon_price_hidden").css("display", "block");
      }
    } else {
      jQuery(".byc_coupon_amount").val(0);
      jQuery("#discount_coupen_code").val("");
      jQuery("#discount_coupen_value").val("");
      jQuery("#coupon_price_hidden").css("display", "none");
      jQuery(".coupon_price_hidden").css("display", "none");
      jQuery("#car_book_amount_total").html("£" + total_booking_payment);
      jQuery("#coupon_code_smg").html(
        '<div class="coupon_invalid">Invalid coupon code..!!</div>'
      );
    }
  });
}
