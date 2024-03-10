<?php

  global $wpdb;
function get_data($search){
  global $wpdb;
  $qr = "SELECT * FROM `attractions_data` WHERE `city_name` LIKE '%$search%'  OR `dest` LIKE '%$search%'  OR `destination` LIKE '%$search%'  OR `title` LIKE '%$search%'  OR `tag` LIKE '%$search%' ORDER BY `id` DESC";
  $attractions = $wpdb->get_results($qr);
  $attr_json = json_encode($attractions);
  return json_decode($attr_json,1);
}
  $allResponse = $all_destination = $destinationArr = $all_activities = $dosomethings = array();
  $searched = isset($_GET['search']) ? $_GET['search'] : '';
  $searchedTag = isset($_GET['tag']) ? $_GET['tag'] : '';
  $searchedDest = isset($_GET['dest']) ? $_GET['dest'] : '';

  $mode = get_option('options_attraction_api_mode');
  $attraction_api_user = get_option('options_attraction_'.$mode.'_username');
  $attraction_api_pass = get_option('options_attraction_'.$mode.'_password');
  $attraction_api_url = get_option('options_attraction_api_'.$mode.'_url');

  // $attraction_settings = get_option('attraction_api_option');
  // $attraction_api_url = rtrim($attraction_settings['attraction_api_url'],"/");
  // $attraction_api_user = $attraction_settings['attraction_api_username']; 
  // $attraction_api_pass = $attraction_settings['attraction_api_password'];

  // echo "<pre>"; print_r($attraction_settings); exit();

  if(isset($_GET['search'])){

      $data1 = $data2 = $data3 = [];
      $search = (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : (isset($_GET['tag'])?$_GET['tag']:'');
      $dest = (isset($_GET['dest']) && !empty($_GET['dest'])) ? $_GET['dest'] :'';

      if($search){
        $data01 = get_data($search);
        if($data01){
          foreach($data01 as $ind => $d){
            $data1[$ind] = $d;
            $data1[$ind]['id'] = $d['attraction_id'];
          }
        } 
        // else {
        //   $response1 =  wp_remote_get( $attraction_api_url.'/products?title='.$search.'&view=extended',
        //                     array(
        //                         'timeout'     => 120,
        //                     )
        //                 );
        //   $res1 = wp_remote_retrieve_body( $response1 );
        //   $data_1 = json_decode($res1, true);
        //   if(!isset($data_1['data']))$data_1 = ['data'=>[]];
        //   $response2 =  wp_remote_get( $attraction_api_url.'/products?tags='.$search.'&view=extended',
        //                   array(
        //                     'timeout'     => 120,
        //                   )
        //                 );
        
        //   $res2 = wp_remote_retrieve_body( $response2 );
        //   $data2 = json_decode($res2, true);
        //   if(!isset($data2['data']))$data2 = ['data'=>[]];
        //   $data1['data'] = array_merge($data_1['data'], $data2['data']);
          // $new = add_attraction_to_db($data1['data']);
          // $data01 = get_data($search);
          // if($data01){
          //   foreach($data01 as $ind => $d){
          //     $data1[$ind] = $d;
          //     $data1[$ind]['id'] = $d['attraction_id'];
          //   }
          // }
        // }
      }

      // echo "<pre>"; echo $attraction_api_url.'/products?tags='.$search.'&view=extended'; print_r($data1); exit();
      if($dest){

          $response3 = $wpdb->get_results("SELECT * FROM `attractions_data` WHERE `dest` LIKE '%$dest%' OR `destination` LIKE '%$dest%' OR `title` LIKE '%$dest%' ORDER BY `id` DESC");
          $res3 = json_encode($response3);
          $data3 = json_decode($res3,1);
          // $data03 = [];
          // get data from country name
          // $cities = $wpdb->get_results("SELECT * FROM `travellanda_cities` WHERE `country_name` = '".$dest."' OR `city_name` = '".$dest."'");
          // $city_names = [];
          // if($cities) {
          //   foreach($cities as $city) {
          //     $city_names[] = $city->city_name;
          //   }

          //   $response03 = $wpdb->get_results("SELECT * FROM `attractions_data` WHERE city_name IN ('".implode("', '", $city_names)."')");
          //   $res03 = json_encode($response03);
          //   $data03 = json_decode($res03,1);
          // }

          // $data3 = array_merge($data3, $data03);


          foreach($data3 as $index => $value){
            $data3[$index]['id'] = $value['attraction_id'];
          }
          // echo "<pre>"; print_r($data3); exit();

          // $response3 =  wp_remote_get( $attraction_api_url.'/products?dest='.$dest.'&view=extended',
          //                 array(
          //                   'timeout'     => 120,
          //                 )
          //               );
          // $res3 = wp_remote_retrieve_body( $response3 );
          // $data3 = json_decode($res3, true);
          // pre($data3);
      }


      // if(!empty($data1['data']) && !empty($data2['data']) && !empty($data3['data']))
      // {
      //     $allResponse = array_merge($data1['data'], $data2['data'], $data3['data']);
      // }
      // else if(!empty($data1['data']) && !empty($data2['data']))
      // {
      //     $allResponse = array_merge($data1['data'], $data2['data']);
      // }
      // else if(!empty($data2['data']) && !empty($data3['data']))
      // {
      //     $allResponse = array_merge($data2['data'], $data3['data']);
      // }
      // else if(!empty($data1['data']) && !empty($data3['data']))
      // {
      //     $allResponse = array_merge($data1['data'], $data3['data']);
      // }
      // else if(!empty($data1['data']))
      // {
      //     $allResponse = $data1['data'];
      // }
      // else if(!empty($data2['data']))
      // {
      //   $allResponse = $data2['data'];
      // }
      // else if(!empty($data3['data']))
      // {
      //   $allResponse = $data3['data'];
      // }
       $responses = array_merge($data1 , $data2 , $data3);
       // $allResponse = isset($responses['data'])?$responses['data']:[];
       $allResponse = $responses;

      // if(!empty($allResponse)){
      //     foreach($allResponse as $res)
      //     {
      //         if(!in_array($res['dest'], $destinationArr))
      //         {
      //             array_push($destinationArr, $res['dest']);
      //         }
      //     }
      // }
    $all_destination = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'attraction_destinations'." WHERE `Parent_id` = 0 AND `Description` != ''");
    $all_activities = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'attraction_tags'." WHERE `CategoryID` = 27");
    $dosomethings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'attraction_tags'." WHERE `CategoryID` = 24");

}


?>
<div class="tour-sidebar">
  <div class="row">
    <div class="tour-palace">
      <ul class="accordion">
        <li class="list-heading"> <a class="toggle" href="javascript:void(0);">Destinations</a>
          <ul class="inner" style="display: block;">
            <?php if(!empty($all_destination)){ foreach($all_destination as $dest){ 
             ?>
              <li>
                <input type="checkbox" id="<?php echo $dest->Dest_ID.'-'.$dest->Title; ?>" class="country-checkbox gctcf-attraction-attr" value="<?php echo $dest->Title; ?>" data-id="<?php echo $dest->Dest_ID; ?>" data-type="destination">
                <label class="country-name" for="<?php echo $dest->Dest_ID.'-'.$dest->Title; ?>"><?php echo $dest->Title; ?></label>
              </li>
            <?php } }?>
          </ul>
        </li>
        <li class="list-heading"> <a class="toggle" href="javascript:void(0);" onclick="toggle_click(2);"> ACTIVITIES</a>
          <ul class="inner">
            <?php if(!empty($all_activities)){ foreach($all_activities as $activity){ 
             ?>
              <li>
                <input type="checkbox" id="<?php echo $activity->Tag_ID.'-'.$activity->Tag; ?>" class="country-checkbox gctcf-attraction-attr" value="<?php echo $activity->Tag; ?>" data-id="<?php echo $activity->Tag_ID; ?>" data-type="tag">
                <label class="country-name" for="<?php echo $activity->Tag_ID.'-'.$activity->Tag; ?>"><?php echo $activity->Tag; ?></label>
              </li>
            <?php } }?>
          </ul>
        </li>
        <li class="list-heading"> <a class="toggle" href="javascript:void(0);" onclick="toggle_click(3);"> DO SOMETHING</a>
          <ul class="inner">
            <?php if(!empty($dosomethings)){ foreach($dosomethings as $dosomething){ 
             ?>
              <li>
                <input type="checkbox" id="<?php echo $dosomething->Tag_ID.'-'.$dosomething->Tag; ?>" class="country-checkbox gctcf-attraction-attr" value="<?php echo $dosomething->Tag; ?>" data-type="tag" data-id="<?php echo $activity->Tag_ID; ?>">
                <label class="country-name" for="<?php echo $dosomething->Tag_ID.'-'.$dosomething->Tag; ?>"><?php echo $dosomething->Tag; ?></label>
              </li>
            <?php } }?>
          </ul>
        </li>
      </ul>
      <input type="hidden" id="searchedProduct" value="<?php echo $searched; ?>">
      <input type="hidden" id="searchedTag" value="<?php echo $searchedTag; ?>">
      <input type="hidden" id="searchedDest" value="<?php echo $searchedDest; ?>">
    </div>

    <div class="view-content">
      <div class="gctcf-loader attraction-loader" style="display: none;"><div class="gctcf-loader-wrap"><img src="https://giftcards4travel.co.uk/staging/wp-content/plugins/gctcf/public/images/loader.gif"><div class="loader-message"><p>The result will appear within a few seconds.</p></div></div></div>
      
      <?php //pre($allResponse);  ?>


      <?php if(!empty($allResponse)){ foreach($allResponse as $destination){ 
        // echo '<pre>';
        // print_r($destination);
        // echo '</pre>';
          ?>
        <div class="tour-view-row">
          <div class="list-image"> <a target="_blank" href="<?php echo site_url('attraction-details?id=').$destination['id'] ?>"> <img src="<?php echo $destination['img_sml']; ?>" alt="img"></a> </div>
          <div class="img-desc">
            <div class="img-heading">
              <h2> <a target="_blank" href="<?php echo site_url('attraction-details?id=').$destination['id'] ?>"> <?php echo $destination['title']; ?></a></h2>
            </div>
            <div class="short-des">
              <?php if($destination['desc_short'] != ''){ ?>
                <p><?php echo $destination['desc_short']; ?></p>
              <?php } ?>
            </div>
            <div class="price-list">
              <?php if($destination['price_from_adult'] != 0) : ?>
                <?php if($destination['price_from_child'] != 0): ?>
                  <p> Child from <span>£<?=$destination['price_from_child']; ?></span> </p>
                <?php endif; ?>
                  <p> Adult from <span>£<?=$destination['price_from_adult']; ?></span> </p>
              <?php else: $general_price = 0.00; $all_prices = json_decode($destination['price_from_all'],1); if(isset($all_prices[0]['price_from']))$general_price = $all_prices[0]['price_from'];?>
                <?php if($general_price != 0): ?>
                  <p> Price from <span>£<?=$general_price; ?></span> </p>
                <?php endif; ?> 
              <?php endif; ?>
            </div>
            <p class="loction"> <?php echo $destination['dest']; ?></p>
          </div>
        </div>
      <?php } } else{ ?>
        <div class="no-records-msg">No Data Found against your search criteria. Please try something else!</div>
      <?php } ?>
    </div>
  </div>
  </div>
</div>