//const gctcf_ajax.ajax_url = gctcf_ajax.ajax_url;
//const siteurl = gctcf_ajax.siteurl;
(function ($) {
  ("use strict");
})(jQuery);

function hotel_transter_by_departure() {
  hotel_name = jQuery("#hotel_search_transfer").val();
  hotel_name_char = jQuery("#hotel_search_transfer").val().length;
  jQuery("#hotel_result_transfer").css("display", "block");

  if (hotel_name_char < 3) {
    return false;
  }
  jQuery.ajax({
    type: "post",
    url: gctcf_ajax.ajax_url,
    data: {
      hotel_name_list: hotel_name,
      action: "gctcf_hotel_search_transfer",
    },
    success: function (data) {
      jQuery("#hotel_result_transfer").html(data.data);
    },
  });
}

function hotel_transter_by_arrival() {
  hotel_name = jQuery("#hotel_search_arrival").val();
  hotel_name_char = jQuery("#hotel_search_arrival").val().length;
  jQuery("#hotel_result_arrival").css("display", "block");

  if (hotel_name_char < 3) {
    return false;
  }
  jQuery.ajax({
    type: "post",
    url: gctcf_ajax.ajax_url,
    data: {
      hotel_name_list: hotel_name,
      action: "gctcf_hotel_search_arrival",
    },
    success: function (data) {
      jQuery("#hotel_result_arrival").html(data.data);
    },
  });
}

jQuery('#attraction-search').on('keyup', function(){
  if(jQuery('#attraction-search').val().length>2)
  {
    jQuery.ajax({
      type: "post",
      url: gctcf_ajax.ajax_url,
      data: {
        title: jQuery('#attraction-search').val(),
        action: "gctcf_attractions_search",
      },
      success: function (data) {
        jQuery(".gctcf-attractions-resp").html(data);
      },
    });
  }
});

jQuery('.gctcf-attraction-attr').on('change', function(){

  var tags = [];
  var destinations = [];
  var Product = jQuery("#searchedProduct").val();
  var Tag = jQuery("#searchedTag").val();
  var Dest = jQuery("#searchedDest").val();

  
  jQuery(".gctcf-attraction-attr").each(function(){
    if(jQuery(this).is(":checked"))
    {
      var type = jQuery(this).data('type');
      if(type == 'destination')
      {
        destinations.push(jQuery(this).val());
      }
      else
      {
        tags.push(jQuery(this).val());
      }
    }
    
  });

  if(Tag != '')
  {
    tags.push(Tag);
  }
  if(Dest != '')
  {
    destinations.push(Dest);
  }
  jQuery('.attraction-loader').fadeIn(500);
  
  jQuery.ajax({
      type: "post",
      url: gctcf_ajax.ajax_url,
      data: {
        dest: destinations,
        tags: tags,
        Product: Product,
        action: "gctcf_attractions_search_by_destination",
      },
      success: function (data) {
        jQuery(".view-content").html(data);
        jQuery('.attraction-loader').fadeOut(500);
      },
  });
}); 


jQuery('#datepicker').on('change', function(){
  var date = jQuery(this).val();
  var datefrom = jQuery("#datefrom").val();
  var dateto = jQuery("#dateto").val();
  var product_id = jQuery("#productId").val();
  var time = jQuery(this).closest('form').find('.selectedTime');
  
  if(time.length == 0){
    jQuery(".gctcf-loader").fadeIn("slow");
    var data = {
          date: date,
          datefrom: datefrom,
          dateto: dateto,
          product_id: product_id,
          action: "gctcf_attractions_dateid",
        }
    attraction_dateid(data)
  } else{
    jQuery(".gctcf-loader").fadeIn("slow");
    var data = {
          date: date,
          datefrom: datefrom,
          dateto: dateto,
          product_id: product_id,
          action: "gctcf_attractions_avilabletime",
        }
    attraction_avilabledtes(data);
  }
}); 

jQuery('#selectedTime').on('change', function(){
  var timeid = jQuery(this).val();
  var date = jQuery('#datepicker').val();
  var datefrom = jQuery("#datefrom").val();
  var dateto = jQuery("#dateto").val();
  var product_id = jQuery("#productId").val();
  var time = jQuery(this).closest('form').find('.selectedTime');
  var data = {
          time: timeid,
          date: date,
          datefrom: datefrom,
          dateto: dateto,
          product_id: product_id,
          action: "gctcf_attractions_dateid",
        }
  if(time.length == 1){
    jQuery(".gctcf-loader").fadeIn("slow");
    attraction_dateid(data);
  }
}); 

jQuery('.show-all-list').on('click', function(){
  jQuery(".gctcf-loader").fadeIn("slow");
  let offset = parseInt(jQuery("input[name='gctcf_offset']").val());
  jQuery.ajax({
        type: "post",
        url: gctcf_ajax.ajax_url,
        data: jQuery("#gctcf-show-all-hotels").serialize(),
        dataType: 'json',
        success: function (data) {
          offset = parseInt(data.offset);
          jQuery("input[name='gctcf_offset']").val(offset);
          if(data.html)
          {
            jQuery(".alt_content").append(data.html)
          }else{
            jQuery('.show-all-list').hide();
          }
          jQuery(".gctcf-loader").fadeOut("slow");
        },
    });
});
function attraction_dateid(data){
  jQuery.ajax({
        type: "post",
        url: gctcf_ajax.ajax_url,
        data: data,
        success: function (data) {
          if(data.success){
            jQuery("#dateId").val(data.data);
          }else{
            jQuery(".gctcf-select-time").append('<p>'+data.data+'</p>');
          }
          jQuery(".gctcf-loader").fadeOut("slow");
        },
    });
}

function attraction_avilabledtes(data){
  jQuery.ajax({
        type: "post",
        url: gctcf_ajax.ajax_url,
        data: data,
        success: function (data) {
          if(data.success){
            jQuery("#selectedTime").html(data.data.html);
          }else{
            jQuery(".gctcf-select-time").append('<p>Timings are not avilable for this date, Please contact for details.</p>');
          }
          jQuery(".gctcf-loader").fadeOut("slow");
        },
    });
}