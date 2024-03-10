<div class="gc-container">
<?php
        $voucher_code = (isset($_POST['gct4_voucher_code'])) ? strtoupper(sanitize_text_field($_POST['gct4_voucher_code'])) : '';
 ?>
        <form class="bookform form-inline gc-row" method="POST">
            <div class="form-group gc-md-10 gc-sm-12 gc-xs-12">
                <input type="text" id="gct4_voucher_code" name="gct4_voucher_code" class="form-control" placeholder="Gift Card Code..." required="required" autocomplete="off" value="<?php echo $voucher_code; ?>">
            </div>
            <div class="form-group gc-md-2 gc-sm-6 gc-xs-12">
                <input type="submit" name="gc4t_get_vouchers" class="btn btn-primary btn-block" style="padding-top:10px !important;" value="SUBMIT">
            </div>
        </form>
<?php
        if (isset($_POST['gc4t_get_vouchers']) && $voucher_code):
            $vouchers = get_posts(array(
                            'post_type' => 'gc4t_store_voucher',
                            'post_status' => 'private',
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => 'gc4t_voucher_status',
                                    'value' => array('active', 'used'),
                                    'compare' => 'IN',
                                ),
                                array(
                                    'key' => 'gc4t_voucher_code',
                                    'value' => $voucher_code,
                                ),
                            )
                        ));
            if ($vouchers):
?>
                <div class="gc-row">
                    <div class="gc-md-12">
                        <div class="gc-row">
                            <p class="gc-md-3"><strong>Amount</strong></p>
                            <p class="gc-md-3"><strong>Remaining</strong></p>
                            <p class="gc-md-3"><strong>Status</strong></p>
                            <p class="gc-md-3"><strong>Code</strong></p>
                        </div>
                        <?php foreach ($vouchers as $voucher): ?>
                        <div class="gc-row" <?php echo $voucher->ID; ?>>
                            <p class="gc-md-3">Currency: GBP</p>
                            <p class="gc-md-3">&pound;<?php echo sprintf('%01.2f', get_post_meta($voucher->ID, 'gc4t_voucher_amount', true)); ?></p>
                            <p class="gc-md-3">&pound;<?php echo sprintf('%01.2f', get_post_meta($voucher->ID, 'gc4t_voucher_amount_remaining', true)); ?></p>
                            <p class="gc-md-3"><?php echo strtoupper(get_post_meta($voucher->ID, 'gc4t_voucher_status', true)); ?></p>
                            <p class="gc-md-3"><?php echo strtoupper(get_post_meta($voucher->ID, 'gc4t_voucher_code', true)); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
<?php 
            else:
                //Check for virtual
                $coupon_wp = new WC_Coupon($voucher_code);
                $date_created = $coupon_wp->get_date_created();
                if ($date_created):
?>
<style>
    .gc-md-3 {
        float: left;
        width: 25%;
    }
    .gc-md-3 strong {
        color: black !important;
        font-weight: bold;
    }
</style>

                    <div class="gc-row">
                        <div class="gc-12">
                            <div class="gc-row">
                                <p class="gc-md-3"><strong>Amount</strong></p>
                                <p class="gc-md-3"><strong>Remaining</strong></p>
                                <p class="gc-md-3"><strong>Status</strong></p>
                                <p class="gc-md-3"><strong>Code</strong></p>
                            </div>
                            <div class="gc-row">
                                <?php $currency = get_post_meta( $coupon_wp->get_id(), 'gc4t_currency', true );
                                $currency_symbol = ($currency == 'EUR') ? '&euro;' : '&pound;';
                                if ($currency == 'EUR') :
                                    $rate = get_post_meta($coupon_wp->get_id(), 'gc4t_conversion_rate');
                                    $value = $rate * $coupon_wp->get_amount(); ?>
                                    <p class="gc-md-3">Currency: <strong>EUR</strong></p>
                                    <p class="gc-md-3"><?php echo $currency_symbol; ?><?php echo sprintf('%01.2f', get_post_meta($coupon_wp->get_id(), 'gc4t_currency_original_amount', true)); ?></p>
                                    <p class="gc-md-3"><?php echo $currency_symbol; ?><?php echo sprintf('%01.2f', $value); ?></p>
                                    <p class="gc-md-3"><?php echo ($coupon_wp->get_amount() > 0) ? 'ACTIVE' : 'USED'; ?></p>
                                    <p class="gc-md-3"><?php echo strtoupper($coupon_wp->get_code()); ?></p>
                                <?php else: ?>
                                    <p class="gc-md-3">Currency: <strong>GBP</strong></p>
                                    <p class="gc-md-3"><?php echo $currency_symbol; ?><?php echo sprintf('%01.2f', get_post_meta($coupon_wp->get_id(), 'original_amount', true)); ?></p>
                                    <p class="gc-md-3"><?php echo $currency_symbol; ?><?php echo sprintf('%01.2f', $coupon_wp->get_amount()); ?></p>
                                    <p class="gc-md-3"><?php echo ($coupon_wp->get_amount() > 0) ? 'ACTIVE' : 'USED'; ?></p>
                                    <p class="gc-md-3"><?php echo strtoupper($coupon_wp->get_code()); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="gc-row">
                        <div class="gc-12">
                            <div class="gc-row">
                                <p class="gc-12 text-center">NO GIFT CARD CODE FOUND</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
</div>
