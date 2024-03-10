jQuery(document).ready(function ($) {

  jQuery.validator.setDefaults({ 
        ignore: [] 
  });

  

  jQuery("#attraction-booking").validate({
    rules: {
      order_email: {
        required: true,
        email: true,
      },
      lead_pass_name: "required",
      depature_date: "required",
      full_name: "required",
      address1: "required",
      city_address: "required",
      pin_code: "required",
    },
    messages: {
      order_email: {
        required: "Please enter a email",
        minlength: "Please enter a valid email address",
      },
      lead_pass_name: "Please fill lead passenger name",
      depature_date: "Enter a departure date",
      full_name: "Please fill full name",
      address1: "Please fill address",
      city_address: "Please fill city",
      pin_code: "Please fill pin code",
      terms: "Please accept our terms",
      privacy: "Please accept our privacy",
    },
    errorPlacement: function(error, element) {
      
      var placement = $(element).data('error');
      var id = $(element).attr('id');
      
      if ((id == "formTerms") || (id == "formPrivacy")) {
        // console.log(error[0]);
        $('.gctcf-error').html(error);
      } else {
        error.insertAfter(element);
      }
    }
  });
  jQuery(".passenger_data").each(function() {
    var attr_name = jQuery(this).attr("data-attr");
    console.log(attr_name);
    jQuery(this).rules("add", {
      required: true,
      messages: {
        required: "Please fill " + attr_name,
      },
    });
  });
 
});
