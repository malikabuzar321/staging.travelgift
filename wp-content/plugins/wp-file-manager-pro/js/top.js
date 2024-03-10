var $ = jQuery;
$(document).on( 'paste', '.elfinder-cwd-filename textarea',function(e) {
    setTimeout(function()
    { 
        var data= $( ".elfinder-cwd-filename textarea" ).val() ;
        var dataFull = data.replace(/[\/\\]/g, '');
        jQuery(".elfinder-cwd-filename textarea").val(jQuery.trim(dataFull));
    });
});
$(document).on('keypress', '.elfinder-cwd-filename textarea', function(e) {
    if (e.keyCode === 191 || e.keyCode === 47 || e.keyCode === 220 || e.keyCode === 92) {
       e.preventDefault();
       return false;
    }
});

function fm_get_network_url(){
    var urlhash = window.location.hash;
    var href = '';
    if(urlhash){
      var arr = urlhash.split('_');
      var lastItem = arr.pop();
      var txt = decodeURIComponent(escape(window.atob(lastItem)));
  
      if(fmp_params.is_multisite == '1')
      {
        if(txt == '/')
        {
          href = fmp_params.network_url;
        }
      }
    }
    return href;
  }
  jQuery(document).on('click', ".notice-dismiss", function(){
    jQuery(this).closest('.updated').hide();
    var current_url = window.location.href;
    if(current_url!=''){
      var selected_tab = current_url.split('&tb')[1];
      if(selected_tab!=undefined){
        var admin_page_url = 'admin.php?page=wp_file_manager_settings&tb'+selected_tab+'';
        window.history.replaceState({}, document.title, admin_page_url);
      }
    }
  });