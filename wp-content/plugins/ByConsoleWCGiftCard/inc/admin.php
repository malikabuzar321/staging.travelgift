<?php
// add mmenu
add_action('admin_menu','byconsole_giftcard_add_plugin_menu');

function byconsole_giftcard_add_plugin_menu(){

// $user = new WP_User( 2 );
// $user->add_cap( 'manage_options' );

add_menu_page( 'BYC Processing Fees', 'BYC Processing Fees', 'manage_options', 'byconsole_giftcard_general_settings', 'byconsole_giftcard_settings_form' );


}

function byconsole_giftcard_settings_form()
{
?>
<div class="wrap">
<h1>Giftcard Processing Fee</h1>
<form method="post" class="" action="options.php">
<?php
settings_fields("byconsolegiftcardsection");
do_settings_sections("byconsole_giftcard_plugin_options");      
submit_button(); 
?>          
</form>
</div>	
<?php
}

function byconsole_giftcard_processing_fees_fun()
{
	$byconsole_giftcard_processing_fees = get_option('byconsole_giftcard_processing_fees');
?>
<input type="text" name="byconsole_giftcard_processing_fees" id="byconsole_giftcard_processing_fees" value="<?php echo $byconsole_giftcard_processing_fees;?>" />
<?php	
}

add_action('admin_init', 'byconsole_giftcard_settings_fields');
function byconsole_giftcard_settings_fields()
{
	add_settings_section("byconsolegiftcardsection", "", null, "byconsole_giftcard_plugin_options");

	add_settings_field("byconsole_giftcard_processing_fees", "Processing Fees:", "byconsole_giftcard_processing_fees_fun", "byconsole_giftcard_plugin_options", "byconsolegiftcardsection");
	
	register_setting("byconsolegiftcardsection", "byconsole_giftcard_processing_fees");
}