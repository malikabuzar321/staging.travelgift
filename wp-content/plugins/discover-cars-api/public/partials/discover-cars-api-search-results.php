<?php

// SEARCH KEYWORD / Mendoza Airport

if (!empty($cars) && !isset($cars['errors'])) :

  $policy_tbl = $wpdb->prefix . 'discovercars_fuel_policies';
  $location_types_tbl = $wpdb->prefix . 'discovercars_location_types';
  $jk = 0;
  foreach ($cars as $car) :



    $VehicleImage = isset($car['VehicleImageUrl']) ? $car['VehicleImageUrl'] : '';
    $Name = isset($car['Name']) ? $car['Name'] : '';
    $seats = isset($car['PasengerCount']) ? $car['PasengerCount'] : '';
    $bag = isset($car['Bags']) ? $car['Bags'] : '';
    $doors = isset($car['Doors']) ? $car['Doors'] : '';
    $air_condition = isset($car['AirCon']) ? $car['AirCon'] : '';
    $fuel_policy_id = isset($car['FuelPolicy']['ID']) ? $car['FuelPolicy']['ID'] : '';
    $fuel_policy = '';
    if ($fuel_policy_id) {
      $fuel_policy = $wpdb->get_row("SELECT * FROM $policy_tbl WHERE `policy_id` = '$fuel_policy_id'");
    }
    $pickup_id = isset($car['PickupLocationType']['ID']) ? $car['PickupLocationType']['ID'] : '';
    $pickup_location_type = '';
    if ($pickup_id) {
      $pickup_location_type = $wpdb->get_row("SELECT * FROM $location_types_tbl WHERE `location_id` = '$pickup_id'");
    }
    $price = isset($car['Price']) ? '£ ' . $car['Price'] : '£ 0';
    $car_price = isset($car['Price']) ? $car['Price'] : 0;


    //JSON DATA FOR BOOKING FORM


    if ($jk % 3 == 0) : ?>
      <div class="gc_car_box_sec_row">
      <?php endif; ?>
      <div class="gc_car_box_sec">
        <div class="gc_car_box_sec_inner">
          <div class="gc_box_sec_top">
            <div class="box_sec_left">
              <?php if ($VehicleImage) : ?>
                <img src="<?= $VehicleImage; ?>" class="img-responsive">
              <?php endif; ?>
            </div>
            <div class="box_sec_right">
              <h4>
                <?= $Name; ?>
              </h4>
              <ul class="gc-car-feature">
                <?php if ($seats) : ?>
                  <li class="gc-icon-seats">
                    <?= $seats ?> <?php _e("seats", "discover-cars-api"); ?></li>
                <?php endif; ?>
                <?php if ($bag) : ?>
                  <li class="gc-icon-bags">
                    <?= $bag; ?> <?php _e("bags", "discover-cars-api"); ?></li>
                <?php endif; ?>
                <?php if ($doors) : ?>
                  <li class="gc-icon-doors">
                    <?= $doors; ?> <?php _e("doors", "discover-cars-api"); ?></li>
                <?php endif; ?>
                <?php if ($air_condition) : ?>
                  <li class="gc-icon-ac"><?php _e("Air Conditioning", "discover-cars-api"); ?></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
          <div class="gc_box_mid">
            <div class="gc_box_mid_row">
              <div class="gc_box_col-4">
                <?php if ($fuel_policy) : ?>
                  <div class="gc_fuel_info top tooltip">
                    <div class="gc_data_title"><?php _e("Fuel policy", "discover-cars-api"); ?></div>
                    <div class="gc_data_value">
                      <?= $fuel_policy->name; ?>
                    </div>
                    <span class="tooltiptext">
                      <?= $fuel_policy->description; ?>
                    </span>
                  </div>
                <?php endif; ?>
                <?php if ($pickup_location_type) : ?>
                  <div class="gc_fuel_info bottom tooltip">
                    <div class="gc_data_title"><?php _e("Pick-up location", "discover-cars-api"); ?></div>
                    <div class="gc_data_value"><?= $pickup_location_type->name; ?></div>
                    <span class="tooltiptext"><?= $pickup_location_type->description; ?></span>
                  </div>
                <?php endif; ?>
              </div>
              <div class="gc_box_col-4">
                <?php if (isset($car['IncludedOptions']) && !empty($car['IncludedOptions'])) : ?>
                  <?php $i = 1; ?>
                  <ul class="gc_box_mid_list">
                    <?php foreach ($car['IncludedOptions'] as $option) :
                      $class = '';
                      if ($i > 3)  $class = 'dca-more-options';
                    ?>

                      <li class="<?= $class; ?>"><?= $option; ?></li>
                      <?php $i++; ?>
                    <?php endforeach; ?>
                    <?php if (count($car['IncludedOptions']) > 3) : ?>
                      <li><a href="javascript:void(0);" class="dca-show-options" data-count="<?= count($car['IncludedOptions']); ?>"><?php _e("show", "discover-cars-api"); ?> <?= count($car['IncludedOptions']); ?> <?php _e("more", "discover-cars-api"); ?></a></li>
                    <?php endif; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </div>
            <div class="gc_box_col-12">
              <div class="gc_bonus disinfected tooltip"><?php _e("Disinfection", "discover-cars-api"); ?>
                <span class="tooltiptext"><?php _e("Travel safely — this rental company disenfects all of its cars after each renter to prevent the spread of the coronavirus.", "discover-cars-api"); ?></span>
              </div>
            </div>
          </div>
          <div class="gc_box_mid gc_box_last">
            <div class="gc_box_mid_row">
              <div class="gc_box_col-12">
                <div class="gc_price_box">
                  <div class="gc_price_vertical_left">
                    <div class="gc_price-item-name-main"> <?php echo sprintf(__("Rental cost for %s days", "discover-cars-api"), $days); ?> </div>
                    <div class="price-item-price-main"><?= $price; ?></div>
                  </div>
                  <div class="gc_price_vertical_right">
                    <form method="post" action="<?php echo site_url('/car-hire-2/'); ?>">

                      <input type="hidden" value="<?php //echo $jsondata;
                                                  ?>" id="dca_car_ID">


                      <input type="hidden" name="car_amount" value="<?= $car_price; ?>" class="car_amount">
                      <input type="hidden" name="itemName" value="<?php echo $Name; ?>" class="itemName">
                      <input type="hidden" name="vehicleImage" value="<?php echo $VehicleImage; ?>" class="vehicleImage">
                      <input type="hidden" name="rentdays" value="<?php echo $days; ?>" class="rentdays">
                      <input type="hidden" name="car_seats" value="<?php echo $seats; ?>" class="car_seats">
                      <input type="hidden" name="car_bags" value="<?php echo $bag; ?>" class="car_bags">
                      <input type="hidden" name="car_doors" value="<?php echo $doors; ?>" class="car_doors">
                      <input type="hidden" name="car_ac" value="<?php echo $air_condition; ?>" class="car_ac">
                      <input type="hidden" name="fuel_policy" value="<?php echo $fuel_policy_id; ?>" class="fuel_policy">
                      <input type="hidden" name="pickup_id" value="<?php echo $pickup_id; ?>" class="pickup_id">
                      <input type="hidden" name="pick_date_time" value="<?php echo $pick_date_time; ?>" class="pick_date_time">
                      <input type="hidden" name="drop_date_time" value="<?php echo $drop_date_time; ?>" class="drop_date_time">
                      <input type="hidden" name="pick_location" value="<?php echo $pick_location; ?>" class="pick_location">
                      <input type="hidden" name="dropoff_location" value="<?php echo $dropoff_location; ?>" class="dropoff_location">
                      <button type="submit" name="car_book" class="gc_btn_price_view"><?php _e("Book Now", "discover-cars-api"); ?></button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php if ($jk % 3 == 2) : ?>
      </div>
    <?php endif; ?>
    <?php $jk++; ?>
  <?php endforeach; ?>
<?php else : ?>
  <div class="dca-no-results-found">
    <p><?php _e("No cars found! please try other locations or date.", "discover-cars-api"); ?></p>
  </div>
<?php endif; ?>