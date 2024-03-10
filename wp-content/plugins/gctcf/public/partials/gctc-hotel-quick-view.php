<?php
    global $wpdb;
    $dir = wp_get_upload_dir();
    $url = $dir['baseurl'].'/hotel_api/'.$hotel_id.'.xml';
    // echo $url;

    $check = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE hotel_id=".$hotel_id);
    if($check){
        $feed = $check[0]->feed;
        $viewed = $check[0]->viewed;
        $viewed++;
        $wpdb->update('hotels_data',['viewed'=>$viewed],['id'=>$check[0]->id]);
    }
    if ($feed == 'stuba'):

    $xml_hotel_data = get_hotles($url);
    $avilable_hotel_facility =simplexml_load_string($xml_hotel_data);
    $facility_count = 0;
?>
    
    <div class="row">
        <h3><?php echo $avilable_hotel_facility->Name; ?></h3>
        <?php if($avilable_hotel_facility->Photo): ?>
        <div class="col-sm-12">
            <div class="hotel-full-img"><img src="https://api.stuba.com<?php echo $avilable_hotel_facility->Photo[0]->Url; ?>"></div>
            <ul class="gc-hotel-light-box">    
             <?php
                foreach($avilable_hotel_facility->Photo as $avilable_hotel_photo){
            ?>
                <li class="hotel-thumb"><img src="https://api.stuba.com<?php echo $avilable_hotel_photo->Url; ?>"></li>
            <?php }  ?>
            
            </ul>
        </div>
        <?php endif; ?>
        <div class="gc-row gc-hotel-addres-row">
            <div class="gc-col-8">
                <h5><u>Address</u></h5>

                <?php
                echo $avilable_hotel_facility->Address->Address1; 
                if(!empty($avilable_hotel_facility->Address->Address1))
                { 
                    echo ','.$avilable_hotel_facility->Address->Address1;
                } 
                if(!empty($avilable_hotel_facility->Address->Address2))
                {
                    echo ','.$avilable_hotel_facility->Address->Address2;
                }
                if(!empty($avilable_hotel_facility->Address->City))
                {
                    echo ','.$avilable_hotel_facility->Address->City;
                }
                if(!empty($avilable_hotel_facility->Address->State))
                {
                    echo ','.$avilable_hotel_facility->Address->State;
                }
                if(!empty($avilable_hotel_facility->Address->Zip))
                {
                    echo ','.$avilable_hotel_facility->Address->Zip;
                }
                if(!empty($avilable_hotel_facility->Address->Country))
                {
                    echo ','.$avilable_hotel_facility->Address->Country;
                }
                echo '<br/>'; 
                if(!empty($avilable_hotel_facility->Region->Name))
                {
                    echo 'Region: '.$avilable_hotel_facility->Region->Name;
                }
                ?>
                
            </div>
            <div class="gc-col-4">
                <h5><u>Rating</u></h5>
           <?php 
                if(!empty($avilable_hotel_facility->Stars))
                {
                    echo '<span class="rating">';
                    for($k=1;$k<=$avilable_hotel_facility->Stars;$k++)
                    {
                        echo '<i class="fa fa-star"></i>';
                    }
                    echo '</span>';
                }
            ?>
            </div>
        </div>
    
        <div class="gc-col-12 gc-hotel-description-col">
            <h4>Facility/Description</h4>
    
        <?php
            $i=1;
            foreach($avilable_hotel_facility->Description as $facility_list)
            {
                $facility_count++; 
        ?>
    
               <div class="gc-hotel-description-inner">
               <b><?php echo $i;?>. <?php echo $facility_list->Type;?>:</b><p><?php echo $facility_list->Text;?></p>
               </div>
        
      
    <?php
                $i++;
            }
        ?>
        </div>    
    </div>
    <?php if (!$facility_count) { ?>
            <div class="row">
                <div class="gc-col-12">
                    <h5>Hotel facility is  not avalible due to some technical reasons, please choose between any of the other hotels on the list.</h5>
                </div>
             </div>
   
<?php
    }     
    endif;

    if ($feed == 'travellanda'):
        require_once GCTCF_PATH . '/includes/Travellanda.class.php';
        $travellanda_settings = get_travellanda_api_config();

        $travellanda = new Travellanda();
        $travellanda->setUsername($travellanda_settings['user']);
        $travellanda->setPassword($travellanda_settings['pass']);
        $travellanda->setMode($travellanda_settings['mode']);

        $hotel_details = $travellanda->getHotelDetails(array($hotel_id));
        $hotel_details = json_decode($travellanda->convertToJson($hotel_details['body']), true);

        if (isset($hotel_details['Body']['Hotels']['Hotel'])):
        $hotel = $hotel_details['Body']['Hotels']['Hotel'];
        //pre($hotel);die("kkk");
        ?>
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
            <div class="gc-row gc-hotel-addres-row">
                <div class="gc-col-8">
                        <h5><u>Address</u></h5>
                        <?php echo $hotel['Address'] . ', ' . $hotel['Location']; ?><br>
                </div>
                <div class="gc-col-4">
                    <h5>Rating</h5>
                    <span class="rating">
                        <?php for ($a = 0; $a < $hotel['StarRating']; $a++) : ?>
                            <i class="fa fa-star"></i>
                        <?php endfor; ?>
                    </span>
                </div>
            </div>

            <div class="gc-col-12 gc-hotel-description-col">
                <h4>Facility/Description</h4>
                <p><strong>General</strong></p>
                <p><?php echo $hotel['Description']; ?></p>
                <?php $facilityCount = 1; 
                    foreach ($hotel['Facilities']['Facility'] as $index => $facility) : ?>
                        <?php if ($facility['FacilityType'] == 'Hotel Information') : ?>
                            <p><?php echo $facility['FacilityName']; ?></p>
                        <?php else: ?>
                            <?php if ($facilityCount == 1): ?>
                                <p><strong>Facilities</strong></p>
                            <?php endif; ?>
                            <p><?php echo $facilityCount; ?>. <?php echo $facility['FacilityName']; ?></p>
                            <?php $facilityCount++; ?>
                        <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif;
 endif;
 ?>  