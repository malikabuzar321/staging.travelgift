<div class="dca-form-list-sec">
  <div class="gc-search-form-wrapper">
    <form id="gc-search-box" class="gc-search-box" method="post">
      <div class="gc-group dca-pickup-sec">
        <label class="Pick-up location sub-title"><?php _e("Pick-up location", "discover-cars-api"); ?></label>
        <input type="text" id="dca-pickup-location" class="dca-location" placeholder="<?php _e("Enter airport or city", "discover-cars-api"); ?>">
        <input type="hidden" name="dca-pickup-id">
        <input type="hidden" name="dca-pickup-country">
        <div class="dca-search-placeholder" style="display: none;"><?php _e("we are currently searching...", "discover-cars-api"); ?></div>
      </div>
      <div class="gc-group">
        <input type="checkbox" id="is-drop-off" class="my-checkbox is-drop-off" checked>
        <label for="is-drop-off" class="my-checkbox-label drop-off"><?php _e("Return car in same location", "discover-cars-api"); ?></label>
      </div>
      <div class="gc-group dca-dropoff-sec" style="display: none;">
        <label class="Pick-up location sub-title"><?php _e("Drop-off location", "discover-cars-api"); ?></label>
        <input type="text" id="dca-dropoff-location" class="dca-location" placeholder="<?php _e("Enter airport or city", "discover-cars-api"); ?>">
        <input type="hidden" name="dca-dropoff-id">
        <input type="hidden" name="dca-dropoff-country">
        <div class="dca-search-placeholder" style="display: none;"><?php _e("we are currently searching...", "discover-cars-api"); ?></div>
      </div>
      <div class="gc-date-row">
        <div class="gc-date-left">
          <div class="gc-group dates">
            <label class="sub-title"><?php _e("Pick-up date", "discover-cars-api"); ?></label>
            <div class="fake-field clearfix">
              <input type="text" id="dca-pickup-date" name="dca-pickup-date" value="<?php echo date('d-m-Y'); ?>">
            </div>
          </div>
          <div class="gc-group time">
            <?php 
                $start=strtotime('00:00');
                $end=strtotime('23:59');
                $current_time = strtotime(date('H:i'));
                $frac = 1800;
                $r = $current_time % $frac;

                $new_time = $current_time + ($frac-$r);
            ?>
            <select id="dca-pickup-time" name="dca-pickup-time">
              <?php for ($halfhour=$start;$halfhour<=$end;$halfhour=$halfhour+1800) {
                      $selected = '';
                      if(date('H:i',$halfhour) == date('H:i',$new_time))
                      {
                        $selected = 'selected';
                      }
               ?>
                        <option <?= $selected; ?> value="<?php echo date('H:i',$halfhour); ?>"><?php echo date('H:i',$halfhour); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="gc-date-right">
          <div class="gc-group dates">
            <label class="sub-title"><?php _e("Drop-off date", "discover-cars-api"); ?></label>
            <div class="fake-field clearfix">
              <input type="text" id="dca-drop-date" name="dca-drop-date" value="<?php echo date('d-m-Y', strtotime('+4 days')); ?>">
            </div>
          </div>
          <div class="gc-group time">
            <select id="dca-drop-time" name="dca-drop-time">
              <?php for ($halfhour=$start;$halfhour<=$end;$halfhour=$halfhour+1800) {
                      $selected = '';
                      if(date('H:i',$halfhour) == date('H:i',$new_time))
                      {
                        $selected = 'selected';
                      }
               ?>
                        <option <?= $selected; ?> value="<?php echo date('H:i',$halfhour); ?>"><?php echo date('H:i',$halfhour); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
      </div>
      <div class="gc-table-view">
        <div class="gc-table-left">
          
        </div>
        <div class="gc-table-right">
          <button type="submit" id="location-submit" name="dca-search" class="button-submit"><?php _e("Search now", "discover-cars-api"); ?></button>
        </div>
      </div>
      <input type="hidden" name="action" value="dca_search_results">

    </form>
  </div>
  <div class="dca-loader" style="display: none;">
      <img src="<?= plugin_dir_url( dirname(__FILE__) ).'images/loading-car.gif' ?>" alt="image"/>
      <div class="dca-search-heading">We are currently searching . . .</div>
      <p>... for the best available offers among 500 car rental companies!</p>
    </div>
  <div class="dca-search-results"></div>
</div>
