<?php

/**
 * Main Handler Template of the Plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.4.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */
?>

<?php $log_enable = Hubwoo::is_log_enable(); ?>
<div id="hubwoo-logs" class="hubwoo-content-wrap hubwoo-tabcontent">
	<div class="hubwoo-logs__header">
		<div class="hubwoo-logs__heading-wrap">
			<h2 class="hubwoo-section__heading">
				<?php esc_html_e( 'Sync Log', 'hubspot-for-woocommerce' ); ?>	
			</h2>
		</div>
		<?php if ( $log_enable ) : ?>
		<ul class="hubwoo-logs__settings-list">
			<li class="hubwoo-logs__settings-list-item">
				<a id="hubwoo-clear-log" href="#" class="hubwoo-logs__setting-link">
					<?php esc_html_e( 'Clear Log', 'hubspot-for-woocommerce' ); ?>	
				</a>
			</li>
			<li class="hubwoo-logs__settings-list-item">
				<a id="hubwoo-download-log" class="hubwoo-logs__setting-link">
					<?php esc_html_e( 'Download', 'hubspot-for-woocommerce' ); ?>	
				</a>
			</li>
		</ul>
		<?php endif; ?>
	</div>
	<?php if ( $log_enable ) : ?>
	<div class="hubwoo-table__wrapper">
		<table id="hubwoo-table" width="100%" class="hubwoo-table dt-responsive">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Expand', 'hubspot-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Feed', 'hubspot-for-woocommerce' ); ?></th>
					<th>
					<?php
					echo esc_html( Hubwoo::get_current_crm_name() );
					esc_html_e( ' Object', 'integration-with-quickbooks' );
					?>
					</th>
					<th><?php esc_html_e( 'Time', 'hubspot-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Request', 'hubspot-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Response', 'hubspot-for-woocommerce' ); ?></th>
				</tr>
			</thead>
		</table>
	</div>
	<?php else : ?>
	<div class="hubwoo-content-wrap">
		<?php esc_html_e( 'Please enable the log', 'hubspot-for-woocommerce' ); ?>
	</div>
	<?php endif; ?>
</div>
