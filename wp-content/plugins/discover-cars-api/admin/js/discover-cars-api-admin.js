(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

	 jQuery(document).on("click", ".dca-password-toggle", function(){

    	var $this = $(this);
    	if($this.parent().find('input').hasClass('hide'))
    	{
    		$this.parent().find('input').attr('type', 'text');
    		$this.parent().find('input').addClass('show');
    		$this.parent().find('input').removeClass('hide');
    		$this.removeClass('dashicons-visibility');
    		$this.addClass('dashicons-hidden');
    	} else if($this.parent().find('input').hasClass('show'))
    	{
    		$this.parent().find('input').attr('type', 'password');
    		$this.parent().find('input').addClass('hide');
    		$this.parent().find('input').removeClass('show');
    		$this.removeClass('dashicons-hidden');
    		$this.addClass('dashicons-visibility');
    	}

    });

})( jQuery );
