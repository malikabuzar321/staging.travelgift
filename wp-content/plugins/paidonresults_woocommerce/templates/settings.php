<div class="wrap">
    <h2>Paid On Results - WooCommerce</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('paidonresults_woocommerce-group'); ?>

        <?php do_settings_sections('paidonresults_woocommerce'); ?>

        <?php @submit_button(); ?>
    </form>
</div>