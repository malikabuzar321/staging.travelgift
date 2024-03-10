<form class="bookform form-inline gc-row" action="<?php echo site_url(); ?>/transfers/" method="post">
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
            <input type="text" id="hotel_search_transfer" name="vehicle_dept_location2" onkeyup="hotel_transter_by_departure()" class="form-control" placeholder="Departure Location..." required="required" autocomplete="off">
            <input type="hidden" name="vehicle_dept_location" id="hotel__code__name">
            <input type="hidden" name="location_type_dep" id="location__type__code_dep">
            <input type="hidden" name="hotel__location_by_latitude_dep" id="hotel__location__by__latitude_dep">
            <input type="hidden" name="hotel__location_by_longitude_dep" id="hotel__location__by__longitude_dep">
        </div>
        <div class="gc-form-group gc-md-3 pad_left_right">
            <input type="text" id="hotel_search_arrival" name="vehicle_arrival_location2" onkeyup="hotel_transter_by_arrival()" class="form-control" placeholder="Arrival location..." required="required" autocomplete="off">
            <input type="hidden" name="vehicle_arrival_location" id="hotel__code__name_arrival">
            <input type="hidden" name="location_type_arrival" id="location__type__code_arrival">
            <input type="hidden" name="hotel__location_by_latitude_arrival" id="hotel__location__by__latitude_arrival">
            <input type="hidden" name="hotel__location_by_longitude_arrival" id="hotel__location__by__longitude_arrival">
        </div>
        <div class="gc-form-group gc-md-2 pad_left_right">
            <div class="input-group">
                <input type="text" class="form-control" name="vehicle_arriving_date" placeholder="Arrival Date.." autocomplete="off" id="datepicker_arriving" required="required">
                <div class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></div>
            </div>
        </div>
        <div class="gc-form-group gc-md-2 pad_left_right">
            <div class="input-group">
                <input type="text" class="form-control" name="vehicle_return_date" autocomplete="off" placeholder="Return Date.." id="datepicker_return">
                <div class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></div>
            </div>
        </div>
        <div class="gc-form-group gc-md-2 pad_left_right">
            <div class="input-group" style="width:100%;">
                <input type="time" class="form-control" name="vehicle_arriving_time" placeholder="Arrival time.." id="arriving_time" required="required">
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
                <input type="submit" name="vehicle_list" class="btn btn-primary btn-block" value="QUOTE ME">
            </div>
            <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 ">
            </div>
        </div>
    </div>
</form>