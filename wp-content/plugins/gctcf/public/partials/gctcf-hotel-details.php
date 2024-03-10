<?php
global $wpdb;
$hotel_id = isset($_REQUEST['hotel-id']) ? $_REQUEST['hotel-id'] : 0;
$check = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE hotel_id=".$hotel_id);
$hotel_data = [];
if($check):
    $hotel_data = ['feed' => ''];
    switch($check[0]->feed){
        case "travellanda":
            $hotel_data['hotel'] = get_travellanda_hotel($hotel_id);
            $hotel_data['feed'] = 'travellanda';
        break;
        case "stuba":
            $hotel_data['hotel'] = get_stuba_hotel($hotel_id);
            $hotel_data['feed'] = 'stuba';
        break;
        case "tbo":
            $hotel_data['hotel'] = get_tbo_hotel($hotel_id);
            $hotel_data['feed'] = 'tbo';
        break;
        default:
            $hotel_data = ['error' => 'Hotel not found.'];
        break;
    }

    // pre($hotel_data); exit();
// $search_request = WC()->session->get('search_request');

// WC()->session->set('search_request',[]);

// $date = $hotel_night = '';
// $rooms = $children = $adults = [];
// if(isset($search_request['hotel_id'])){
//     $request_hotel_id = $search_request['hotel_id'];
// } else {
//     $request_hotel_id = 0;
// }
// if(isset($search_request['hotel_check_in_date']) &&  ($request_hotel_id == $hotel_id || $request_hotel_id == 0)){
//     $date = ($search_request['hotel_check_in_date'])??'';
//     $hotel_night = $search_request['hotel_night_all']??'';
//     $rooms = is_array($search_request['no_of_room'])?$search_request['no_of_room']:['1'=>$search_request['no_of_room']];
//     $children = is_array($search_request['hotel_children'])?$search_request['hotel_children']:['1'=>$search_request['hotel_children']];
//     $adults = is_array($search_request['hotel_adults'])?$search_request['hotel_adults']:['1'=>$search_request['hotel_adults']];
// }


// $dir = wp_get_upload_dir();
// $url = $dir['baseurl'] . '/hotel_api/' . $hotel_id . '.xml';
// $feed = '';
// $check = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE hotel_id=".$hotel_id);
// echo "<pre>"; print_r($check[0]->feed);
// pre($check);
    $feed = $check[0]->feed;
    $viewed = $check[0]->viewed;
    $viewed++;
    $wpdb->update('hotels_data',['viewed'=>$viewed],['id'=>$check[0]->id]);

    if($hotel_data['hotel'] && $hotel_data['feed'] == 'travellanda'):
        $hotel = $hotel_data['hotel']; ?>
        <section class="gc-section clearfix">
            <div class="row">
                <h3><?php echo $hotel['HotelName']; ?></h3>
                <?php if(isset($hotel['Images']['Image']) && !empty($hotel['Images']['Image'])): ?>
                    <div class="col-sm-12">
                        <div class="hotel-full-img"><img src="<?php echo $hotel['Images']['Image'][0]; ?>"></div>
                        <ul class="gc-hotel-light-box">
                            <?php foreach ($hotel['Images']['Image'] as $image): ?>
                                <li class="hotel-thumb">
                                    <img src="<?php echo $image; ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="gc-row gc-hotel-addres-row" style="width: 100%;">
                    <div class="gc-col-8">
                            <h5><u>Address</u></h5>
                            <?php echo $hotel['Address']; $hotel['Location']?(!is_array($hotel['Location'])?', '.$hotel['Location']:''):''; ?><br>
                    </div>
                    <div class="gc-col-4" style="align-items: center;">
                        <h5>Rating</h5>
                        <span class="rating">
                            <?php for ($a = 0; $a < $hotel['StarRating']; $a++) : ?>
                                <i class="fa fa-star"></i>
                            <?php endfor; ?>
                        </span>
                    </div>
                    <div class="gc-col-4 text-right">
                            <button type="button" style="background-color: #f8d18e;border: unset;font-size: 14px;height: 45px;padding: 10px 34px;cursor: pointer;    transition: 0.3s;text-transform: uppercase;color: #061d2f !important;" onclick="jQuery('#room_modal').modal('show')">BOOK NOW</button>
                    </div>
                </div>

                <?php include_once('gctcf-hotel-details-info.php'); ?>
                <div class="gc-col-12 gc-hotel-description-col">
                    <h4>Facility/Description</h4>
                    <div class="gc-hotel-description-inner">
                        <b>General</b>
                        <p><?php echo $hotel['Description']; ?></p>
                    </div>
                    
                    <div class="gc-hotel-description-inner">
                    <?php $facilityCount = 1; $hotel_info = 1;  
                        foreach ($hotel['Facilities']['Facility'] as $index => $facility) : ?>
                            <?php if ($facility['FacilityType'] == 'Hotel Information') : if($hotel_info == 1):?>
                                    <b>Hotel Information</b><ul id="information_list">
                                <?php endif; ?>
                                    <li><?php echo $facility['FacilityName']; $hotel_info++;?></li>

                            <?php else: ?>
                                    <?php if ($facilityCount == 1): ?>
                                    </ul></div><div class="gc-hotel-description-inner"> 
                                    <b>Facilities</b><ul id="facilities_list">
                                    <?php endif; ?>
                                    <li><?php echo $facility['FacilityName']; ?></li>
                                    <?php $facilityCount++; ?>
                                
                            <?php endif; ?>
                    <?php endforeach; ?>
                   </ul></div>
                </div>
            </div>
        </section>
        <!--<div class="gc-col-12 gc-no-hotel">
                <p>No data found!</p>
                <a href="<?php echo  home_url('/hotels'); ?>" class="gc-new-hotels">Search Hotels</a>
            </div> -->


    <?php elseif($hotel_data['hotel'] && $hotel_data['feed'] == 'stuba'): ?>
        <section class="gc-section clearfix">
            <div class="row">
                <?php $facility_count=0; $avilable_hotel_facility = $hotel_data['hotel'];
                    if ($avilable_hotel_facility && isset($avilable_hotel_facility->Name)) { ?>
                        <h3><?php echo $avilable_hotel_facility->Name; ?></h3>
                        <?php if ($avilable_hotel_facility->Photo) { ?>
                            <div class="col-sm-12">
                                <div class="hotel-full-img"><img src="https://api.stuba.com<?php echo $avilable_hotel_facility->Photo[0]->Url; ?>" alt="image"></div>
                                <ul class="gc-hotel-light-box">
                                    <?php
                                    foreach ($avilable_hotel_facility->Photo as $avilable_hotel_photo) {
                                    ?>
                                        <li class="hotel-thumb"><img src="https://api.stuba.com<?php echo $avilable_hotel_photo->Url; ?>" alt="image"></li>
                                    <?php }  ?>

                                </ul>
                            </div>
                        <?php } ?>
                        <div class="gc-row gc-hotel-addres-row" style="width: 100%">
                            <div class="gc-col-8">
                                <h5><u>Address</u></h5>

                                <?php $add = json_decode(json_encode($avilable_hotel_facility->Address),1); 
                                    $i = 0; foreach($add as $ad){
                                        if(!is_array($ad)){
                                            if($i!=0)echo ", ";
                                            echo $ad;
                                        }
                                        $i++;

                                    }
                                
                                if (!empty($avilable_hotel_facility->Region->Name)) {
                                    echo '<p>Region: ' . $avilable_hotel_facility->Region->Name."</p>";
                                }
                                ?>

                            </div>
                            <div class="gc-col-4">
                                <h5><u>Rating</u></h5>
                                <?php
                                if (!empty($avilable_hotel_facility->Stars)) {
                                    echo '<span class="rating">';
                                    for ($k = 1; $k <= $avilable_hotel_facility->Stars; $k++) {
                                        echo '<i class="fa fa-star"></i>';
                                    }
                                    echo '</span>';
                                }
                                ?>
                            </div>

                            <div class="gc-col-4 text-right">
                                <button type="button" style="background-color: #f8d18e;border: unset;font-size: 14px;height: 45px;padding: 10px 34px;cursor: pointer;    transition: 0.3s;text-transform: uppercase;color: #061d2f !important;" onclick="jQuery('#room_modal').modal('show');">BOOK NOW</button>
                            </div>

                        </div>

                        
                        <div class="gc-col-12 gc-hotel-description-col">
                            <h4>Facility/Description</h4>

                            <?php
                            $i = 1;
                            foreach ($avilable_hotel_facility->Description as $facility_list) {
                                $facility_count++;
                            ?>
                                <?php if($facility_list->Type != 'RXLContentId'){ ?>
                                <div class="gc-hotel-description-inner">
                                    <b><?php echo $i; ?>. <?php echo ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $facility_list->Type)); ?>:</b>
                                    <p><?php echo $facility_list->Text; ?></p>
                                </div>
                                <?php } ?>


                            <?php
                                $i++;
                            }
                            ?>
                        </div>
                        <div class="gc-md-2 gc-sm-6 gc-xs-12">
                            <?php
                            $hotel_url_args = array(
                                'hotel-region-id' => (string) $avilable_hotel_facility->Region->Id,
                                'hotel-region-name' => (string) $avilable_hotel_facility->Region->Name,
                                'hotel-name' => (string) $avilable_hotel_facility->Name,
                            );
                            $hotel_url = add_query_arg(
                                $hotel_url_args,
                                home_url() . '/hotel-search',
                            );
                            ?>

                            <!-- <a href="#"><input type="button" name="booking_details" class="btn btn-primary btn-block" value="Book Now"  onclick="jQuery('#roomxml_hotel_search').trigger('click');"/></a> -->

                        </div>
                <?php }else { ?>
                    <div class="gc-col-12 gc-no-hotel">
                        <p>No data found!</p>
                        <a href="<?php echo  home_url('/hotels'); ?>" class="gc-new-hotels">Search Hotels</a>
                    </div>
                <?php } ?>
            </div>
        </section>

    <?php elseif($hotel_data['hotel'] && $hotel_data['feed'] == 'tbo'): 
        $hotel = $hotel_data['hotel']; ?>

        <section class="gc-section clearfix">
            <div class="row">
                <h3><?php echo $hotel['HotelName']; ?></h3>
                <?php if(isset($hotel['Images']) && !empty($hotel['Images'])): ?>
                    <div class="col-sm-12">
                        <div class="hotel-full-img"><img src="<?php echo $hotel['Images'][0]; ?>"></div>
                        <ul class="gc-hotel-light-box">
                            <?php foreach ($hotel['Images'] as $image): ?>
                                <li class="hotel-thumb">
                                    <img src="<?php echo $image; ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="gc-row gc-hotel-addres-row">
                    <div class="gc-col-8">
                            <h5><u>Address</u></h5>
                            <?php echo $hotel['Address'] . ', ' . $hotel['Map'] . ', ' .$hotel['PinCode']; ?>
                            <?php if($hotel['PhoneNumber']): ?>
                                <h5><u>Contact</u></h5>
                                <?="<b>Phone: </b>".$hotel['PhoneNumber'] . (isset($hotel['FaxNumber'])?" | <b>Fax Number: </b>".$hotel['FaxNumber']:''); ?>
                            <?php endif; ?>
                            <h5><u>Timings</u></h5>
                            <b>Check-in Time: </b><?=$hotel['CheckInTime']?><?=" | "?>
                            <b>Check-out Time: </b><?=$hotel['CheckOutTime']?>
                    </div>
                    <div class="gc-col-4" style="align-items: center;">
                        <h5>Rating</h5>
                        <span class="rating">
                            <?php for ($a = 0; $a < $hotel['HotelRating']; $a++) : ?>
                                <i class="fa fa-star"></i>
                            <?php endfor; ?>
                        </span>
                    </div>
                    <div class="gc-col-4 text-right">
                            <button type="button" id="book_now_btn" onclick="jQuery('#room_modal').modal('show');">BOOK NOW</button>
                    </div>
                </div>

                <div class="gc-col-12 gc-hotel-description-col">
                    <h4>Description</h4>
                    <div class="gc-hotel-description-inner">
                        <p><?php echo $hotel['Description']; ?></p>
                    </div>
                    
                    <?php if(isset($hotel['HotelFacilities'])): ?>
                    <h4>Hotel Facilities</h4>
                    <div class="gc-hotel-description-inner">
                        <ul>                        
                            <?php foreach($hotel['HotelFacilities'] as $hf){
                                echo "<li>".$hf."</li>";
                            } ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($hotel['Attractions'])): ?>
                    <h4>Attractions Near Hotel</h4>
                    <div class="gc-hotel-description-inner">                
                        <?php foreach($hotel['Attractions'] as $hf){
                            echo $hf;
                        } ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    <?php else: ?>
        <section class="gc-section clearfix">
            <h3>Sorry, Hotel not Found. Please try another hotel.</h3>
        </section>
    <?php endif; ?>
<?php else: ?>
    <section class="gc-section clearfix">
        <h3>Sorry, Hotel not Found. Please try another hotel.</h3>
    </section>
<?php endif; ?>


<?php $hotel_start_date = $hotel_night = $hotel_star_rating = $hotel_min_price_val = $hotel_max_price_val = ''; ?>
<style type="text/css">
    .ui-datepicker{
        z-index: 1100 !important;
    }
    #book_now_btn{
        background-color: #f8d18e;
        border: unset;
        font-size: 14px;
        height: 45px;
        padding: 10px 34px;
        cursor: pointer;
        transition: 0.3s;
        text-transform: uppercase;
        color: #061d2f !important;
    }
</style>
<!-- Modal -->
<div class="modal fade" id="room_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Check Availability</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="room_form" class="" action="hotel-availability " method="post" onsubmit="gctcf_show_loader();">
            <div class="hotel-title my-3">
                <h4>ENTER DATE TO SEE PRICES AND AVAILABILITY</h4>
            </div>
            <div class="form-row mb-3">
                <div class="input-group col-md-6">
                    <input type="text" class="form-control gctcf-datepicker" value="<?!empty($hotel_start_date)?$hotel_start_date:''?>" name="hotel_check_in_date" placeholder="Check in" id="datepicker16" required="required" />
                </div>

                <div class="col-md-6">
                    <select class="form-control" name="hotel_night" data-style="btn-white">
                        <?php for ($i = 1; $i <= 15; $i++) { ?>
                            <option <?=!empty($hotel_night)?($hotel_night==$i?'selected':''):''?> value="<?php echo $i ?>"><?php echo $i ?> Night<?=$i>1?'s':''?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_dropdown_section_hotel_search">
                <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 hotel_view_list" id="hotel_result"></div>
                <div class="gc-form-group gc-md-8 gc-sm-6 gc-xs-12"></div>
            </div> -->

            <div class="form-row rooms mb-3">
                <div class="col-md-2">
                    <label>Room1</label>
                    <input type="hidden" name="no_of_room[1]" class="" value="1" />
                </div>

                <div class="col-md-4">
                    <select class="form-control" name="hotel_adults[1]" data-style="btn-whiter" style="width:100%;">
                        <?php for ($hotAdui = 1; $hotAdui <= 10; $hotAdui++) { ?>
                            <option value="<?php echo $hotAdui; ?>"><?php echo $hotAdui; ?> Adults</option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <select class="form-control" name="hotel_children[1]" data-style="btn-white" style="width:100%;">
                        <?php for ($hotChil = 0; $hotChil <= 3; $hotChil++) { ?>
                            <option value="<?php echo $hotChil; ?>"><?php echo $hotChil; ?> Children</option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2 btn_adds">
                    <button type="button" id="btn_room_add_another" class="btn btn-sm btn-outline-success text-success document_add_another" style="color:#fff;" value=>+</button>
                    <input type="hidden" name="hotel_star_rating" class="hotel_star_rating_option" id="hotel_star_rating_option" value="<?php echo $hotel_star_rating; ?>" />

                    <input type="hidden" name="hotel_min_price_by_filter" class="hotel_min_price_option" id="hotel_min_price_option" value="<?php echo $hotel_min_price_val ?>" />

                    <input type="hidden" name="hotel_max_price_by_filter" class="hotel_max_price_option" id="hotel_max_price_option" value="<?php echo $hotel_max_price_val; ?>" />
                </div>
            </div>

            <div class="rooms room_options">
                
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <!-- <input type="submit" name="hotel_list" id="roomxml_hotel_search" class="btn btn-primary btn-block roomxml_hotel_search" value="SEARCH" /> -->
        <button type="button" class="btn btn-primary btn-block roomxml_hotel_search" id="btn_room_types">See Room Types</button>
      </div>
    </div>
  </div>
</div>