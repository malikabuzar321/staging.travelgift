<?php 
  $tab1_c = ($def_tab == 'hotel') ? ' active' : '';
  $tab3_c = ($def_tab == 'giftcards') ? ' active' : '';

 ?>
<div class="home_header_section">
  <section class="section-light gctcf-tabs nopadding">
    <div id="withphone" class="container-full" style="margin-top:0px !important; position: absolute !important;">
      <div class="gc-container">
        <div class="section-container">
          <div class="home-form">
            <ul class="nav nav-tabs" role="tablist">
              <li class="gctcf-tab<?= $tab1_c; ?>"><a href="javascript:void(0);" tab="#tab_01" aria-controls="tab_01" role="tab" data-toggle="tab"><i class="fas fa-hotel"></i>
                  <div class="hotel">HOTELS</div>
                </a></li>
              <li class="gctcf-tab"><a href="javascript:void(0);" tab="#tab_03" aria-controls="tab_03" role="tab" data-toggle="tab"><i class="fa fa-bus"></i>
                  <div class="trans">Transfers</div>
                </a></li>
              <li class="gctcf-tab<?= $tab3_c; ?>"><a href="javascript:void(0);" tab="#tab_04" aria-controls="tab_04" role="tab" data-toggle="tab"><i class="fa fa-gift"></i>
                  <div class="gift">GIFT CARD</div>
                </a></li>

            </ul>
            <div class="tab-content">
              <div class="notice" style="background:#f1f1f1;padding: 5px;font-size: 12px;margin-bottom:10px;">
                <p><strong>Please note all pricing is in GBP (&pound;). If you have a EUR (&euro;) value gift card you can still redeem your card and the amount will be converted to GBP (&pound;) and applied to your purchase. For any queries please contact us at <a style="color:#f8b545;" href="mailto:info@travelgift.uk">info@travelgift.uk</a></strong><p>
              </div>
              <div role="tabpanel1" class="tab-pane<?= $tab1_c; ?>" id="tab_01">
                <div class="hotel-title">
                  <h6>ENTER DATE TO SEE PRICES AND AVAILABILITY</h6>
                </div>
                <form class="bookform form-inline gc-row" action="<?php echo home_url(); ?>/hotel-search/" method="post" onsubmit="gctcf_show_loader();" autocomplete="off">
                  <div class="gc-md-12 gc-sm-12 gc-xs-12">
                    <div class="gc-form-group gc-md-6 gc-sm-6 gc-xs-12 hotel_list_both_padding padding-01">
                      <input type="hidden" id="hotel__country__code" name="country_code" value="">
                      <input type="text" name="hotel_name" id="hotel-name" class="form-control" onkeyup="hotel_search()" placeholder="Destination: Region name...!!" autocomplete="off" required="required" />
                      <input type="hidden" name="hotel_regionid" id="hotel__region__id" />
                      <input type="hidden" name="hotel_country_by_region" value="<?php if (!empty($_REQUEST['hotel_country_by_region'])) {
                                                                                    echo $_REQUEST['hotel_country_by_region'];
                                                                                  } ?>" id="hotel_country_by_region" />
                      <input type="hidden" name="hotel__region__name" value="<?php if (!empty($_REQUEST['hotel__region__name'])) {
                                                                                echo $_REQUEST['hotel__region__name'];
                                                                              } ?>" id="hotel__region__name" />
                    </div>
                    <div class="gc-form-group gc-md-2 hotel_list_both_padding hotel-mobile-margin-top">
                      <div class="input-group">
                        <input type="text" class="form-control gctcf-datepicker" name="hotel_check_in_date" required="required" placeholder="Check in" id="datepicker16">
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                      </div>
                    </div>
                    <div class="hide_mobile">
                      <div class="gc-form-group gc-md-2 hotel_list_both_padding">
                        <!--<div class="dropdown">-->
                        <select class="hotel_selectpicker" name="hotel_night_all" id="hotel_night_for_all_device" data-style="btn-white">
                          <?php for ($i = 1; $i <= 15; $i++) { ?>
                            <option value="<?php echo $i ?>"><?php echo $i ?> Night</option>
                          <?php } ?>
                        </select>
                        <!-- </div>-->
                      </div>
                      <div class="gc-form-group gc-md-2">
                        <input type="submit" name="hotel_list" class="btn btn-primary btn-block" value="SEARCH" />
                      </div>
                    </div>
                  </div>
                  <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_dropdown_section">
                    <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 hotel_view_list" id="hotel_result"></div>
                    <div class="gc-form-group gc-md-8 gc-sm-6 gc-xs-12"></div>
                  </div>
                  <div class="gc-md-12 gc-sm-12 gc-xs-12 rooms">
                    <div class="gc-md-1">
                      <label>Room1</label>
                      <input type="hidden" name="no_of_room[1]" class="" value="1" />
                    </div>
                    <div class="gc-md-2">
                      <select class="" name="hotel_adults[1]" data-style="btn-whiter">
                        <?php for ($hotAdui = 1; $hotAdui <= 10; $hotAdui++) { ?>
                          <option value="<?php echo $hotAdui; ?>"><?php echo $hotAdui; ?> Adults</option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="gc-md-2">
                      <select class="" name="hotel_children[1]" data-style="btn-white">
                        <?php for ($hotChil = 0; $hotChil <= 3; $hotChil++) { ?>
                          <option value="<?php echo $hotChil; ?>"><?php echo $hotChil; ?> Children</option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="gc-md-1 btn_adds">
                      <fieldset class="fldst">
                        <input type="button" id="btn_room_add_another" class="document_add_another" value="Add">
                      </fieldset>
                    </div>
                  </div>
                  <div class="gc-sm-12 gc-md-12 gc-xs-12 rooms">
                    <div class="room_options">
                    </div>
                  </div>
                  <div class="show_mobile" style="display:none;">
                    <div class="gc-form-group gc-md-2 gc-sm-6 gc-xs-12 hotel_list_both_padding">
                      <div class="dropdown">
                        <select class="selectpicker hotel_selectpicker" name="hotel_night" id="hotel_night_mobile" data-style="btn-white">
                          <?php for ($i = 1; $i <= 15; $i++) { ?>
                            <option value="<?php echo $i ?>"><?php echo $i ?> Night</option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="gc-form-group gc-md-2 gc-sm-6 gc-xs-12">
                      <input type="submit" name="hotel_list" class="btn btn-primary btn-block" value="SEARCH" />
                    </div>
                  </div>
                </form>
              </div>
              <div role="tabpanel1" class="tab-pane" id="tab_03">
                <div class="transfer_title">
                  <h6>ENTER DATE TO SEE PRICES AND AVAILABILITY?</h6>
                </div>
                <form class="bookform form-inline gc-row" action="<?php echo home_url(); ?>/transfers/" method="post" autocomplete="off">
                  <div class="gc-sm-12 gc-md-12 gc-xs-12">
                    <div class="buying-selling-group" id="buying-selling-group" data-toggle="buttons">
                      <label class="btn btn-default buying-selling active">
                        <input type="radio" id="toggle-on" class="singel_arrving" name="arrving_type" value="SINGLE" onchange="arrivingType()" checked="checked">
                        <span class="radio-dot"></span>
                        <span class="buying-selling-word">One Way</span>
                      </label>
                      <label class="btn btn-default buying-selling">
                        <input type="radio" id="toggle-off" class="return_arrving" name="arrving_type" value="RETURN" onchange="arrivingType()">
                        <span class="radio-dot"></span>
                        <span class="buying-selling-word">Return</span>
                      </label>
                    </div>
                  </div>
                  <div class="gc-sm-12 gc-md-12 gc-xs-12 cabs_search">
                    <div class="gc-form-group gc-md-3 pad_left_right">
                      <input type="text" id="hotel_search_transfer" name="vehicle_dept_location2" onkeyup="hotel_transter_by_departure()" class="form-control" placeholder="Departure Location..." required="required" autocomplete="off" />
                      <input type="hidden" name="vehicle_dept_location" id="hotel__code__name" />
                      <input type="hidden" name="location_type_dep" id="location__type__code_dep" />
                      <input type="hidden" name="hotel__location_by_latitude_dep" id="hotel__location__by__latitude_dep" />
                      <input type="hidden" name="hotel__location_by_longitude_dep" id="hotel__location__by__longitude_dep" />
                    </div>
                    <div class="gc-form-group gc-md-3 pad_left_right">
                      <input type="text" id="hotel_search_arrival" name="vehicle_arrival_location2" onkeyup="hotel_transter_by_arrival()" class="form-control" placeholder="Arrival location..." required="required" autocomplete="off" />
                      <input type="hidden" name="vehicle_arrival_location" id="hotel__code__name_arrival" />
                      <input type="hidden" name="location_type_arrival" id="location__type__code_arrival" />
                      <input type="hidden" name="hotel__location_by_latitude_arrival" id="hotel__location__by__latitude_arrival" />
                      <input type="hidden" name="hotel__location_by_longitude_arrival" id="hotel__location__by__longitude_arrival" />
                    </div>
                    <div class="gc-form-group gc-md-2 pad_left_right">
                      <div class="input-group">
                        <input type="text" class="form-control" name="vehicle_arriving_date" placeholder="Arrival Date.." autocomplete="off" id="datepicker_arriving" required="required" />
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                      </div>
                    </div>
                    <div class="gc-form-group gc-md-2 pad_left_right">
                      <div class="input-group">
                        <input type="text" class="form-control gctcf-datepicker" name="vehicle_return_date" autocomplete="off" placeholder="Return Date.." id="datepicker_return" />
                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                      </div>
                    </div>
                    <div class="gc-form-group gc-md-2 pad_left_right">
                      <div class="input-group" style="width:100%;">
                        <input type="time" class="form-control" name="vehicle_arriving_time" placeholder="Arrival time.." id="arriving_time" required="required" />
                        <div class="input-group-addon"></div>
                      </div>
                    </div>
                  </div>
                  <div class="gc-sm-12 gc-md-12 gc-xs-12 cabs_search">
                    <div class="gc-form-group gc-md-3 pad_left_right transfer-mobile-top-dep">
                      <div id="hotel_result_transfer" class="gc-form-group gc-md-3 gc-sm-6 gc-xs-12 hotel_result_transfer">
                      </div>
                    </div>
                    <div class="gc-form-group gc-md-3 pad_left_right transfer-mobile-top-arriv">
                      <div id="hotel_result_arrival" class="gc-form-group gc-md-3 gc-sm-6 gc-xs-12 hotel_result_arrival">
                      </div>
                    </div>
                    <div class="gc-form-group gc-md-6 pad_left_right">
                    </div>
                  </div>
                  <div class="gc-sm-12 gc-md-12 gc-xs-12 gc-xs-12 transfer-order-section">
                    <div class="gc-row">
                      <div class="gc-form-group gc-md-2 mobile-adult-list">
                        <div class="dropdown">
                          <select class="selectpicker mobile-selectpicker" name="vehicle_adult" data-style="btn-white">
                            <option value="1">1 Adults</option>
                            <option value="2">2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                            <option value="5">5 Adults</option>
                            <option value="6">6 Adults</option>
                            <option value="7">7 Adults</option>
                            <option value="8">8 Adults</option>
                            <option value="9">9 Adults</option>
                            <option value="10">10 Adults</option>
                            <option value="11">11 Adults</option>
                            <option value="12">12 Adults</option>
                            <option value="13">13 Adults</option>
                            <option value="14">14 Adults</option>
                            <option value="15">15 Adults</option>
                          </select>
                        </div>
                      </div>
                      <div class="gc-form-group gc-md-2">
                        <div class="dropdown">
                          <select class="selectpicker" name="vehicle_children" data-style="btn-white">
                            <option value="0">0 Children</option>
                            <option value="1">1 Children</option>
                            <option value="2">2 Children</option>
                            <option value="3">3 Children</option>
                          </select>
                        </div>
                      </div>
                      <div class="gc-form-group gc-md-2">
                        <div class="dropdown">
                          <select class="selectpicker" name="vehicle_type" data-style="btn-white">
                            <option value="1">Shuttle</option>
                            <option value="2">Private</option>
                          </select>
                        </div>
                      </div>
                      <div class="gc-form-group gc-md-2">
                        <input type="submit" name="vehicle_list" class="btn btn-primary btn-block" value="SEARCH" />
                      </div>
                      <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 ">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div role="tabpanel" class="tab-pane<?= $tab3_c; ?>" id="tab_04">
                <?php
                $paged = (get_query_var('page')) ? get_query_var('page') : 1;
                query_posts("post_type=product&posts_per_page=4&post_status=publish&orderby=meta_value");
                $i = 0;
                ?>
                <div class="gc-row secend_section product_border">
                  <?php
                  if (have_posts()) : while (have_posts()) : the_post(); ?>
                      <div class="gc-sm-3 gc-md-3">
                        <div class="card_holder">
                          <h5><a class="product_details_link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
                          <div class="product_section_img">
                            <a class="product_details_link" href="<?php the_permalink(); ?>">
                              <img src="<?php echo get_the_post_thumbnail_url(); ?>" alt="image"/>
                            </a>
                          </div>
                          <div class="product_excerpt">
                            <a class="product_details_link" href="<?php the_permalink(); ?>">
                              <?php the_excerpt(); ?>
                            </a>
                            <!-- <a class="product_details_link" href="<?php //the_permalink(); 
                                                                        ?>">See more</a>-->
                          </div>
                          <div class="addtocart_inline secend_section_addcart">
                            <?php
                            $add_to_cart_shortcode_string = '[add_to_cart id="' . $post->ID . '"]';
                            echo do_shortcode('[add_to_cart id="' . $post->ID . '"]');
                            ?>
                          </div>
                        </div>
                      </div>
                  <?php endwhile;
                  endif;
                  wp_reset_query(); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>