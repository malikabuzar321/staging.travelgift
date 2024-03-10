const ajaxUrl                      = hubwooi18n.ajaxUrl;
const hubwooSecurity               = hubwooi18n.hubwooSecurity;

jQuery( document ).ready(function($){
	
	jQuery(document).on(
		'click',
		'.hubwoo-hide-rev-notice',
		async function() {
			const response = await jQuery.ajax(
				{
					type : 'POST',
					url  : ajaxUrl,
					data : {
						action : 'hubwoo_hide_rev_notice',
						hubwooSecurity,
					},
					dataType : 'json',
				}
			);
			
			if( true == response.status ) {
				jQuery('.hubwoo-review-notice-wrapper').hide();
			}
		}
	);  
});	