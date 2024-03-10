                <section class="home-form booking_info" style="display: none;">
                    <form class="bookform form-inline dc-row" action="<?=home_url()?>/hotel-search/" method="post" onsubmit="gctcf_show_loader();" style="margin-bottom: 20px;">
                         
                    <div class="gc-md-12 gc-sm-12 content_section">
                        <input type="hidden" id="hotel__country__code" name="country_code" value="<?php echo $country_code; ?>">
                        
                
                        <div class="gc-form-group gc-md-6 gc-sm-6 gc-xs-12 hotel_list_both_padding padding-01" >
                            <input type="text" class="form-control" value="<?=isset($avilable_hotel_facility)?$avilable_hotel_facility->Name:$hotel['HotelName']?>" disabled />
                            <input type="hidden" name="hotel_id" value="<?=$hotel_id?>">
                        </div>
                    
                                    
                        <div class="gc-form-group gc-md-2 hotel_list_both_padding hotel-mobile-margin-top">
                            <div class="input-group">
                                <input type="text" class="form-control gctcf-datepicker" value="<?php if(!empty($date)){echo $date; }?>" name="hotel_check_in_date" placeholder="Check in" id="datepicker16" required="required" />
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        
                        <div class="gc-form-group gc-md-2 hotel_list_both_padding">
                            <div class="dropdown">
                                <select class="selectpicker" name="hotel_night" data-style="btn-white">
                                    <?php 
                                    for($i=1; $i<=15; $i++)
                                    { 
                                    ?>
                                        <option <?php if(!empty($hotel_night)){if ($hotel_night== $i ) echo 'selected' ;} ?> value="<?php echo $i ?>"><?php echo $i ?> Night</option>
                                    <?php 
                                    } 
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="gc-form-group gc-md-2">
                            <input type="submit" name="hotel_list" id="roomxml_hotel_search" class="btn btn-primary btn-block roomxml_hotel_search" value="BOOK NOW" />
                        </div>
                    </div>
                
                    <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_dropdown_section_hotel_search">
                        <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 hotel_view_list" id="hotel_result"></div>
                        <div class="gc-form-group gc-md-8 gc-sm-6 gc-xs-12"></div>
                    </div>             
                    <?php if($rooms): foreach($rooms as $ind => $room): if($ind <= 2): ?>
                        <div class="gc-md-12 gc-sm-12 gc-xs-12 rooms"> <!-- Displayed for # 1 and # 2 -->
                            <?php endif; if($ind == 2): ?> 
                                <div class="room_options"> <!-- Displayed for # 2 only -->
                            <?php endif; if($ind>1): ?>
                                <fieldset class="fldst room_add_on<?=$ind?>">
                            <?php endif; ?>
                                <div class="gc-md-1">
                                    <label>Room<?=$ind?></label>
                                    <input type="hidden" name="no_of_room[<?=$ind?>]" class="" value="<?=$ind?>" />
                                </div>
                                          
                                <div class="gc-md-2">
                                    <select class="" name="hotel_adults[<?=$ind?>]"  data-style="btn-whiter" style="width:100%;">
                                        <?php 
                                            for($hotAdui=1; $hotAdui<=10; $hotAdui++)
                                            { 
                                        ?>
                                            <option <?=$adults[$ind]==$hotAdui?'selected':''?> value="<?php echo $hotAdui; ?>"><?php echo $hotAdui; ?> Adults</option>
                                        <?php 
                                            } 
                                        ?>
                                    </select>    
                                </div>
                                           
                                <div class="gc-md-2">
                                    <select class="" name="hotel_children[<?=$ind?>]" data-style="btn-white" style="width:100%;">
                                        <?php 
                                            for($hotChil=0; $hotChil<=3; $hotChil++)
                                            { 
                                        ?>
                                                <option <?=$children[$ind]==$hotChil?'selected':''?> value="<?php echo $hotChil; ?>"><?php echo $hotChil; ?> Children</option>
                                        <?php 
                                            } 
                                        ?>
                                    </select> 
                                </div>
                                            
                                <div class="gc-md-1 btn_adds">
                                    <fieldset class="fldst">
                                        <?php if($ind==1): ?>
                                            <input type="button" id="btn_room_add_another" class="document_add_another" style="color:#fff;" value="Add" >
                                        <?php else: ?>
                                            <span id="del_room" class="<?=$ind?> room_add_on<?=$ind?>" onclick="delroom(<?=$ind?>)" style="cursor:pointer; margin-top: 24px;margin-right: 18px;background: #F44336;color: #fff;font-size: 12px;border-radius: 50%;padding: 4px;">X</span>
                                        <?php endif; ?>
                                    </fieldset>
                                </div> 
                            <?php if($ind > 1): ?>
                                </fieldset>
                            <?php endif; if($ind == 1): ?>
                                </div>
                            <?php endif; ?> 

                    <?php endforeach; ?> 
                    <?php if(sizeof($rooms) > 1): ?> 
                        </div></div>
                    <?php endif; else: ?>
                    <div class="gc-md-12 gc-sm-12 gc-xs-12 rooms">
                        <div class="gc-md-1">
                            <label>Room1</label>
                            <input type="hidden" name="no_of_room[1]" class="" value="1" />
                        </div>
                                  
                        <div class="gc-md-2">
                            <select class="" name="hotel_adults[1]"  data-style="btn-whiter" style="width:100%;">
                                <?php 
                                    for($hotAdui=1; $hotAdui<=10; $hotAdui++)
                                    { 
                                ?>
                                    <option value="<?php echo $hotAdui; ?>"><?php echo $hotAdui; ?> Adults</option>
                                <?php 
                                    } 
                                ?>
                            </select>    
                        </div>
                                   
                        <div class="gc-md-2">
                            <select class="" name="hotel_children[1]" data-style="btn-white" style="width:100%;">
                                <?php 
                                    for($hotChil=0; $hotChil<=3; $hotChil++)
                                    { 
                                ?>
                                        <option value="<?php echo $hotChil; ?>"><?php echo $hotChil; ?> Children</option>
                                <?php 
                                    } 
                                ?>
                            </select> 
                        </div>
                                    
                        <div class="gc-md-1 btn_adds">
                            <fieldset class="fldst">
                                <input type="button" id="btn_room_add_another" class="document_add_another" style="color:#fff;" value="Add" >
                            </fieldset>
                            
                         <input type="hidden" name="hotel_star_rating" class="hotel_star_rating_option" id="hotel_star_rating_option" value="<?php echo $hotel_star_rating; ?>" />
                                    
                        <input type="hidden" name="hotel_min_price_by_filter" class="hotel_min_price_option" id="hotel_min_price_option" value="<?php echo $hotel_min_price_val ?>" />
                                     
                        <input type="hidden" name="hotel_max_price_by_filter" class="hotel_max_price_option" id="hotel_max_price_option" value="<?php echo $hotel_max_price_val; ?>" />
                        </div>        
                    </div>
                                  
                    <div class="gc-sm-12 gc-md-12 gc-xs-12 rooms">
                        <div class="room_options">

                        </div> 
                    </div>
                    <?php endif; ?>     
                                  
                    <!-- <div class="gc-sm-12 gc-md-12 gc-xs-12 rooms">
                        <div class="room_options">

                        </div> 
                    </div> -->
                    </form>
                </section>