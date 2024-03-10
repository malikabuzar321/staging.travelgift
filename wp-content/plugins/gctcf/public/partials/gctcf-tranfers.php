<?php
$vehicle_dept_location = isset($_REQUEST['vehicle_dept_location']) ? $_REQUEST['vehicle_dept_location'] : '';
$vehicle_dept_location2 = isset($_REQUEST['vehicle_dept_location2']) ? $_REQUEST['vehicle_dept_location2'] : '';
$location_type_dep = isset($_REQUEST['location_type_dep']) ? $_REQUEST['location_type_dep'] : '';
$vehicle_arrival_location = isset($_REQUEST['vehicle_arrival_location']) ? $_REQUEST['vehicle_arrival_location'] : '';
$vehicle_arrival_location2 = isset($_REQUEST['vehicle_arrival_location2']) ? $_REQUEST['vehicle_arrival_location2'] : '';
$location_type_arrival = isset($_REQUEST['location_type_arrival']) ? $_REQUEST['location_type_arrival'] : '';
$vehicle_sector_type = isset($_REQUEST['arrving_type']) ? $_REQUEST['arrving_type'] : '';
$vehicle_arriving_date = isset($_REQUEST['vehicle_arriving_date']) ? $_REQUEST['vehicle_arriving_date'] : '';
$vehicle_arriving_time = isset($_REQUEST['vehicle_arriving_time']) ? $_REQUEST['vehicle_arriving_time'] : '';

$hotel_lat_dep = isset($_REQUEST['hotel__location_by_latitude_dep']) ? $_REQUEST['hotel__location_by_latitude_dep'] : 0;
$hotel_long_dep = isset($_REQUEST['hotel__location_by_longitude_dep']) ? $_REQUEST['hotel__location_by_longitude_dep'] : 0;
$hotel_lat_arr = isset($_REQUEST['hotel__location_by_latitude_arrival']) ? $_REQUEST['hotel__location_by_latitude_arrival'] : 0;
$hotel_long_arr = isset($_REQUEST['hotel__location_by_longitude_arrival']) ? $_REQUEST['hotel__location_by_longitude_arrival'] : 0;

$hotel_latitude = 0;
$hotel_longitude = 0;
if ($vehicle_sector_type == 'RETURN') {
    $vehicle_return_time = $vehicle_arriving_time;
} else {
    $vehicle_return_time = 0;
}
if (isset($_REQUEST['vehicle_return_date']) && !empty($_REQUEST['vehicle_return_date'])) {
    $vehicle_return_date = $_REQUEST['vehicle_return_date'];
} else {
    $vehicle_return_date = '';
}

$vehicle_adult = isset($_REQUEST['vehicle_adult']) ? $_REQUEST['vehicle_adult'] : 0;
$vehicle_children = isset($_REQUEST['vehicle_children']) ? $_REQUEST['vehicle_children'] : 0;
$vehicle_type = isset($_REQUEST['vehicle_type']) ? $_REQUEST['vehicle_type'] : 1;

if ($location_type_dep == 'RT') {
    $hotel_latitude = $hotel_lat_dep;
    $hotel_longitude = $hotel_long_dep;
}
if ($location_type_arrival == 'RT') {
    $hotel_latitude = $hotel_lat_arr;
    $hotel_longitude = $hotel_long_arr;
}
?>
<div class="gc-transfer-top-sec">
    <h1>QUALITY TRANSFERS WORLDWIDE</h1>
    <p>Searching over 7500 cities & resorts via more than 700 airports, ports & train stations
    </p>
</div>
<div id="myCarousel" class="owl-carousel gc-transfer-carousel" data-ride="carousel">
    <div class="item" data-hash="zero">
        <img class="tranfer_images" src="<?php echo GCTCF_URL; ?>public/images/shuttle.jpg">
        <div class="carousel-caption">
            <h3>SHUTTLE</h3>
            <p>A great way to travel, from a variety of vehicles, including minibuses and coaches, you’ll be met at airport and taken to your hotel swiftly.</p>
        </div>
    </div>

    <div class="item" data-hash="one">
        <img class="tranfer_images" src="<?php echo GCTCF_URL; ?>public/images/shuttle_express.jpg">
        <div class="carousel-caption">
            <h3>EXPRESS SHUTTLE</h3>
            <p>Get to your destination hassle-free and a great price with no more than 4 stops guaranteed.</p>
        </div>
    </div>

    <div class="item" data-hash="two">
        <img class="tranfer_images" src="<?php echo GCTCF_URL; ?>public/images/luxury_private.jpg">
        <div class="carousel-caption">
            <h3>LUXURY PRIVATE</h3>
            <p>Luxury and executive vehicles are available in selected worldwide destinations.</p>
        </div>
    </div>

    <div class="item" data-hash="three">
        <img class="tranfer_images" src="<?php echo GCTCF_URL; ?>public/images/private_taxi.jpg">
        <div class="carousel-caption">
            <h3>PRIVATE TAXIS</h3>
            <p>For larger families or parties that want their own personal transfer without sharing with others.</p>
        </div>
    </div>

    <div class="item" data-hash="four">
        <img class="tranfer_images" src="<?php echo GCTCF_URL; ?>public/images/minibus.jpg" alt="image">
        <div class="carousel-caption">
            <h3>MINIBUSES</h3>
            <p>For larger families or parties that want their own personal transfer without sharing with others.</p>
        </div>
    </div>
</div>
<ul class="nav nav-pills nav-justified mobile-section-off">
    <li data-target="#myCarousel" data-slide-to="0" class=""><a href="#zero">SHUTTLE</a></li>
    <li data-target="#myCarousel" data-slide-to="1"><a href="#one">EXPRESS SHUTTLE</a></li>
    <li data-target="#myCarousel" data-slide-to="2"><a href="#two">LUXURY PRIVATE</a></li>
    <li data-target="#myCarousel" data-slide-to="3"><a href="#three">PRIVATE TAXIS</a></li>
    <li data-target="#myCarousel" data-slide-to="4"><a href="#four">MINIBUSES</a></li>
</ul>
</div>
<div class="home-form gc-tranfer-form tab-content">
    <div role="tabpanel1" class="tab-pane" id="tab_03">
        <div class="transfer_title">
            <h6>WHERE WOULD YOU LIKE TO GO?</h6>
        </div>
        <?php $arr_type = ($vehicle_sector_type == 'RETURN') ? 'RETURN' : 'SINGLE'; ?>
        <?php $active_c1 = ($arr_type == 'SINGLE') ? ' active' : ''; ?>
        <?php $active_c2 = ($arr_type == 'RETURN') ? ' active' : ''; ?>
        <form class="bookform form-inline gc-row" action="<?php echo site_url(); ?>/transfers/" method="post" onsubmit="gctcf_loader();">
            <div class="gc-sm-12 gc-md-12 gc-xs-12">
                <div class="buying-selling-group" id="buying-selling-group" data-toggle="buttons">
                    <label class="btn btn-default buying-selling<?php echo $active_c1; ?>">
                        <input type="radio" id="toggle-on" class="singel_arrving" name="arrving_type" value="SINGLE" onchange="arrivingType()" <?php if ($arr_type == 'SINGLE') { ?> checked="checked" <?php } ?>>
                        <span class="radio-dot"></span>
                        <span class="buying-selling-word">One Way</span>
                    </label>
                    <label class="btn btn-default buying-selling<?php echo $active_c2; ?>">
                        <input type="radio" id="toggle-off" class="return_arrving" name="arrving_type" value="RETURN" onchange="arrivingType()" <?php if ($arr_type == 'RETURN') { ?> checked="checked" <?php } ?>>
                        <span class="radio-dot"></span>
                        <span class="buying-selling-word">Return</span>
                    </label>
                </div>
            </div>
            <div class="gc-sm-12 gc-md-12 gc-xs-12 cabs_search">
                <div class="gc-form-group gc-md-3 pad_left_right">
                    <input type="text" id="hotel_search_transfer" name="vehicle_dept_location2" onkeyup="hotel_transter_by_departure()" class="form-control" placeholder="Departure Location..." required="required" autocomplete="off" value="<?php echo $vehicle_dept_location2; ?>">
                    <input type="hidden" name="vehicle_dept_location" id="hotel__code__name" value="<?php echo $vehicle_dept_location; ?>">
                    <input type="hidden" name="location_type_dep" id="location__type__code_dep" value="<?php echo $location_type_dep; ?>">
                    <input type="hidden" name="hotel__location_by_latitude_dep" id="hotel__location__by__latitude_dep" value="<?php echo $hotel_lat_dep ?>">
                    <input type="hidden" name="hotel__location_by_longitude_dep" id="hotel__location__by__longitude_dep" value="<?php echo $hotel_long_dep ?>">
                </div>
                <div class="gc-form-group gc-md-3 pad_left_right">
                    <input type="text" id="hotel_search_arrival" name="vehicle_arrival_location2" onkeyup="hotel_transter_by_arrival()" class="form-control" placeholder="Arrival location..." required="required" autocomplete="off" value="<?php echo $vehicle_arrival_location2; ?>">
                    <input type="hidden" name="vehicle_arrival_location" id="hotel__code__name_arrival" value="<?php echo $vehicle_arrival_location; ?>">
                    <input type="hidden" name="location_type_arrival" id="location__type__code_arrival" value="<?php echo $location_type_arrival; ?>">
                    <input type="hidden" name="hotel__location_by_latitude_arrival" id="hotel__location__by__latitude_arrival" value="<?php echo $hotel_lat_arr; ?>">
                    <input type="hidden" name="hotel__location_by_longitude_arrival" id="hotel__location__by__longitude_arrival" value="<?php echo $hotel_long_arr; ?>">
                </div>
                <div class="gc-form-group gc-md-2 pad_left_right">
                    <div class="input-group">
                        <input type="text" class="form-control" name="vehicle_arriving_date" placeholder="Arrival Date.." autocomplete="off" id="datepicker_arriving" required="required" value="<?php echo $vehicle_arriving_date; ?>">
                        <div class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></div>
                    </div>
                </div>
                <div class="gc-form-group gc-md-2 pad_left_right">
                    <div class="input-group">
                        <input type="text" class="form-control" name="vehicle_return_date" autocomplete="off" placeholder="Return Date.." id="datepicker_return" value="<?php echo $vehicle_return_date; ?>">
                        <div class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></div>
                    </div>
                </div>
                <div class="gc-form-group gc-md-2 pad_left_right">
                    <div class="input-group" style="width:100%;">
                        <input type="time" class="form-control" name="vehicle_arriving_time" placeholder="Arrival time.." id="arriving_time" required="required" value="<?php echo $vehicle_arriving_time; ?>">
                        <div class="input-group-addon"></div>
                    </div>
                </div>
            </div>
            <div class="gc-sm-12 gc-md-12 gc-xs-12 cabs_search gc-tranfer-mobile-hide">
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
                                <option value="1" <?php if ($vehicle_adult == 1) echo "selected"; ?>>1 Adults</option>
                                <option value="2" <?php if ($vehicle_adult == 2) echo "selected"; ?>>2 Adults</option>
                                <option value="3" <?php if ($vehicle_adult == 3) echo "selected"; ?>>3 Adults</option>
                                <option value="4" <?php if ($vehicle_adult == 4) echo "selected"; ?>>4 Adults</option>
                                <option value="5" <?php if ($vehicle_adult == 5) echo "selected"; ?>>5 Adults</option>
                                <option value="6" <?php if ($vehicle_adult == 6) echo "selected"; ?>>6 Adults</option>
                                <option value="7" <?php if ($vehicle_adult == 7) echo "selected"; ?>>7 Adults</option>
                                <option value="8" <?php if ($vehicle_adult == 8) echo "selected"; ?>>8 Adults</option>
                                <option value="9" <?php if ($vehicle_adult == 9) echo "selected"; ?>>9 Adults</option>
                                <option value="10" <?php if ($vehicle_adult == 10)  echo "selected"; ?>>10 Adults</option>
                                <option value="11" <?php if ($vehicle_adult == 11)  echo "selected"; ?>>11 Adults</option>
                                <option value="12" <?php if ($vehicle_adult == 12)  echo "selected"; ?>>12 Adults</option>
                                <option value="13" <?php if ($vehicle_adult == 13)  echo "selected"; ?>>13 Adults</option>
                                <option value="14" <?php if ($vehicle_adult == 14)  echo "selected"; ?>>14 Adults</option>
                                <option value="15" <?php if ($vehicle_adult == 15)  echo "selected"; ?>>15 Adults</option>
                            </select>
                        </div>
                    </div>
                    <div class="gc-form-group gc-md-2">
                        <div class="dropdown">
                            <select class="selectpicker" name="vehicle_children" data-style="btn-white">
                                <option value="0" <?php if ($vehicle_children == 0)  echo 'selected'; ?>>0 Children</option>
                                <option value="1" <?php if ($vehicle_children == 1)  echo 'selected'; ?>>1 Children</option>
                                <option value="2" <?php if ($vehicle_children == 2)  echo 'selected'; ?>>2 Children</option>
                                <option value="3" <?php if ($vehicle_children == 3)  echo 'selected'; ?>>3 Children</option>
                            </select>
                        </div>
                    </div>
                    <div class="gc-form-group gc-md-2">
                        <div class="dropdown">
                            <select class="selectpicker" name="vehicle_type" data-style="btn-white">
                                <option value="1" <?php if ($vehicle_type == 1) echo 'selected'; ?>>Shuttle</option>
                                <option value="2" <?php if ($vehicle_type == 2) echo 'selected'; ?>>Private</option>
                            </select>
                        </div>
                    </div>
                    <div class="gc-form-group gc-md-2">
                        <input type="submit" name="vehicle_list" class="btn btn-primary btn-block" value="QUOTE ME">
                    </div>
                    <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 ">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<?php
//error_reporting(E_ALL & ~E_NOTICE);
if (isset($_POST['vehicle_list']) && !empty($_POST['vehicle_list'])) {

    $settings = get_transfer_api_config();

    $user_xmldata = '<?xml version="1.0" encoding="UTF-8"?>
                        <TCOML version="NEWFORMAT">
                          <TransferOnly>
                            <Availability>
                              <Request>
                                <Username>' . $settings['user'] . '</Username>
                                <Password>' . $settings['pass'] . '</Password>
                                <Lang>EN</Lang>
                                <DeparturePointCode>' . $vehicle_dept_location . '</DeparturePointCode>
                                <ArrivalPointCode>' . $vehicle_arrival_location . '</ArrivalPointCode>
                                <SectorType>' . $vehicle_sector_type . '</SectorType>
                                <ArrDate>' . $vehicle_arriving_date . '</ArrDate>
                                <ArrTime>' . $vehicle_arriving_time . '</ArrTime>
                                <RetDate>' . $vehicle_return_date . '</RetDate>
                                <RetTime>' . $vehicle_return_time . '</RetTime>
                                <Adults>' . $vehicle_adult . '</Adults>
                                <Children>' . $vehicle_children . '</Children>
                                <Infants>0</Infants>
                                <Vehicletype>' . $vehicle_type . '</Vehicletype>
                                <Latitude>' . $hotel_latitude . '</Latitude>
                                <Longitude>' . $hotel_longitude . '</Longitude>
                              </Request>
                            </Availability>
                          </TransferOnly>
                        </TCOML>';

    $p2p_api_url = $settings['url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $p2p_api_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: text/xml',
        'Content-length: ' . strlen($user_xmldata)
    ));

    $a2b_xml_api_output_for_hotel_search = curl_exec($ch);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (curl_exec($ch) === false) {
        //echo 'Curl error: ' . curl_error($ch);
    } else {
        // echo '<br />';
        //echo 'Room Search operation completed';

    }

    curl_close($ch);

    $p2p_api_xml_response_for_cars_search = simplexml_load_string($a2b_xml_api_output_for_hotel_search);
?>

    <div class="ct-section-inner-wrap gc-transfer-container-wrap">
        <?php
        if (!empty($p2p_api_xml_response_for_cars_search
            ->TransferOnly
            ->errors
            ->error)) {
        ?>
            <div class="gc-row gc-transfer-no-hotel-search">
                <h5>
                    <?php echo $p2p_api_xml_response_for_cars_search->TransferOnly->errors->error->text; ?>
                </h5>
                <p style="margin:0; color: #ff0023;">Please recheck the following details while trying to book a cab :-</p>
                <ul>
                    <li style="list-style:none; color: #ff0023;">1. Pickup and drop will not be avaliable between 2 cities it has to be within same city. </li>
                    <li style="list-style:none; color: #ff0023;">2. The transfer will be available between Airport to Resort and Resort to Airport. </li>
                </ul>
            </div>

            <?php
        }
        if (!empty($p2p_api_xml_response_for_cars_search->TransferOnly)) {
            foreach ($p2p_api_xml_response_for_cars_search->TransferOnly as $cab_array) {
                $id = 1;
                if (!empty($cab_array->Availability->Avline)) {
                    foreach ($cab_array->Availability->Avline as $cars_list) {

            ?>
                        <div class="gc-row gc-transfer-result-row">
                            <form name="" action="<?php echo home_url(); ?>/airport-transfer-booking" method="post">

                                <input type="hidden" name="transfer_code" value="<?php echo $cars_list->TransferCode; ?>" />
                                <input type="hidden" name="vehicle_dept_location" value="<?php echo $vehicle_dept_location; ?>" />
                                <input type="hidden" name="vehicle_arrival_location" value="<?php echo $vehicle_arrival_location; ?>" />
                                <input type="hidden" name="vehicle_sector_type" value="<?php echo $vehicle_sector_type; ?>" />
                                <input type="hidden" name="vehicle_arriving_date" value="<?php echo $vehicle_arriving_date; ?>" />
                                <input type="hidden" name="vehicle_arriving_time" value="<?php echo $vehicle_arriving_time; ?>" />
                                <input type="hidden" name="vehicle_return_date_two" value="<?php echo $vehicle_return_date; ?>" />
                                <input type="hidden" name="vehicle_return_time" value="<?php echo $vehicle_return_time; ?>" />
                                <input type="hidden" name="vehicle_adult" value="<?php echo $vehicle_adult; ?>" />
                                <input type="hidden" name="vehicle_children" value="<?php echo $vehicle_children; ?>" />
                                <input type="hidden" name="vehicle_type" value="<?php echo $vehicle_type; ?>" />
                                <input type="hidden" name="hotel_latitude" value="<?php echo $hotel_latitude; ?>" />
                                <input type="hidden" name="hotel_longitude" value="<?php echo $hotel_longitude; ?>" />
                                <div class="gc-transfer-details-inner">
                                    <div class="gc-sm-12 gc-md-12 gc-xs-12 vehicle_details">
                                        <h2><i class="fa fa-taxi" aria-hidden="true"></i></i><?php echo $cars_list->VehicleDetails->Vehicle; ?></h2>
                                        <ul>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i>
                                                <?php echo 'Vehicle Code: ' . $cars_list->VehicleDetails->VehicleCode; ?>
                                            </li>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i><?php echo 'Passengers:  ' . $cars_list
                                                                                                            ->VehicleDetails->MinCapacity . '-' . $cars_list
                                                                                                            ->VehicleDetails->MaxCapacity; ?></li>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i><?php echo $cars_list
                                                                                                            ->VehicleDetails->NumberOfBags . ' X Suitcase: Per person' ?></li>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i><?php echo 'Vehicles:  ' . $cars_list
                                                                                                            ->VehicleDetails->NumberOfVehicles ?></li>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i><?php echo 'Estimated transfer time:  ' . $cars_list
                                                                                                            ->OutboundTransferDetails->OutboundJourneyTime . '/' . $cars_list->DistanceKM . ' Km'; ?></li>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i><?php echo 'Adult: ' . $vehicle_adult; ?></li>
                                            <li><i class="fa fa-hand-o-right" aria-hidden="true"> </i><?php echo 'Child: ' . $vehicle_children; ?></li>

                                        </ul>
                                    </div>
                                    <div class="gc-sm-12 gc-md-12 gc-xs-12 vehicle_details_origin">
                                        <div class="gc-md-4 gc-sm-6 gc-xs-12 outbound_origin">
                                            <h5>Outbound Origin</h5>
                                            <ul>
                                                <li><i class="fa fa-map-marker" aria-hidden="true"></i><?php echo ' Origin:  ' . $cars_list
                                                                                                            ->OutboundTransferDetails->OutboundOrigin; ?></li>
                                                <li><i class="fa fa-map-marker" aria-hidden="true"></i><?php echo ' Destination:  ' . $cars_list
                                                                                                            ->OutboundTransferDetails->OutboundDestination; ?></li>
                                                <li><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ' JourneyTime:  ' . $cars_list
                                                                                                        ->OutboundTransferDetails->OutboundJourneyTime; ?></li>
                                                <li><i class="fa fa-calendar" aria-hidden="true"></i><?php echo ' Arrival Date:  ' . $cars_list
                                                                                                            ->OutboundTransferDetails->OutboundArrivalDate; ?></li>
                                                <li><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ' Arrival Time:  ' . $cars_list
                                                                                                        ->OutboundTransferDetails->OutboundArrivalTime; ?></li>
                                            </ul>

                                        </div>

                                        <div class="gc-sm-5 gc-sm-6 gc-xs-12 return_origin">

                                            <?php if ($vehicle_sector_type == 'RETURN') { ?>
                                                <h5>Return Origin</h5>
                                                <ul>
                                                    <li><i class="fa fa-map-marker" aria-hidden="true"></i><?php echo ' Return Origin:  ' . $cars_list
                                                                                                                ->ReturnTransferDetails->ReturnOrigin ?></li>
                                                    <li><i class="fa fa-map-marker" aria-hidden="true"></i><?php echo ' Return Destination:  ' . $cars_list
                                                                                                                ->ReturnTransferDetails->ReturnDestination ?></li>
                                                    <li><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ' Return Journey Time:  ' . $cars_list
                                                                                                            ->ReturnTransferDetails->ReturnJourneyTime ?></li>
                                                    <li><i class="fa fa-calendar" aria-hidden="true"></i><?php echo ' Return Departure Date:  ' . $cars_list
                                                                                                                ->ReturnTransferDetails->ReturnDepartureDate ?></li>
                                                    <li><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo ' Return Departure Time:  ' . $cars_list
                                                                                                            ->ReturnTransferDetails->ReturnDepartureTime ?></li>
                                                </ul>

                                            <?php
                                            } ?>
                                        </div>



                                        <div class="modal fade product_view" id="product_view" style="display: none;">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header" style="border-bottom:0px solid #fff;">
                                                        <a href="#" data-dismiss="modal" class="class pull-right"><span class="glyphicon glyphicon-remove"></span></a>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div id="transfer_facility_result">

                                                        </div>

                                                        <div class="modal-header" style="border-bottom:0px solid #fff; text-align:center;">
                                                            <a href="#" data-dismiss="modal" class="class "><span class="glyphicon glyphicon-remove"></span></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="transfer-car-booking-sec">
                                        <div class="gc-md-3 gc-sm-6 gc-xs-12 car_booking_button">

                                            <div class="gc-xs-12 gc-sm-12 gc-md-12 gc-lg-12 total_price">
                                                <div>Total Price (<?php echo $vehicle_sector_type; ?>)</div>
                                                <div class="txt_price">£<?php echo $cars_list->TransferTotalPrice; ?></div>
                                            </div>
                                            <input type="submit" name="vehicle_book_now" class="btn btn-primary btn-block" value="Book Now" />
                                            <?php if ($cars_list->Disclaimer == 1) {

                                            ?>
                                                <button type="button" class="btn btn-link" id="hotel__id_ajax_<?php echo $id; ?>" value="<?php echo $cars_list->TransferCode; ?>" onclick="transfer_facility_ajax(this.id)" data-toggle="modal" data-target="#product_view">Disclaimer</button>

                                            <?php
                                            }
                                            ?>

                                        </div>
                                    </div>
                                </div>

                            </form>
                            <?php if ($cars_list->Disclaimer == 1) {
                            ?>
                                <div class="gc-row">
                                    <img src="<?php echo get_template_directory_uri() ?>/images/a2b_info_disclaimer_icon.jpg" style="float:left; width:35px;" />
                                    <p style="font-size:18px; margin:0px; float:left;padding: 7px; color: #e69a19;">Important information about this vehicle type.</p>
                                </div>
                                <p>Restrictions can sometimes apply for larger vehicles in Barcelona City Centre and Shared Shuttle Buses might not always be able to guarantee a drop-off/pick-up right outside the door of your hotel. However, we’ll get as close as possible, meaning its only a short distance away. </p>
                            <?php
                            }
                            ?>

                        </div>
            <?php
                        $id++;
                    } //foreach for transfer list

                } //if(!empty

            }
        } else {
            ?>
            <div class="gc-row gc-transfer-search-notice">
                <h5>Note : Only Airport to Resort or Resort to Airport searches are allowed.</h5>
            </div>

        <?php
        }
        ?>
    </div>
<?php
} else { ?>
    <div class="" style="padding-top:0px; padding-bottom:50px;">
        <div class="gc-container">
            <div class="gc-row airport-to-resort">
                <h5 style="text-align:center;">Note : Only Airport to Resort or Resort to Airport searches are allowed.</h5>
            </div>
        </div>
    </div>
<?php
} ?>