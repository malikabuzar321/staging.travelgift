 <?php 
if(isset($_GET['id']))
{
  global $wpdb;
  $id = isset($_GET['id']) ? $_GET['id']: 0;

  $mode = get_option('options_attraction_api_mode');
  $attraction_api_user = get_option('options_attraction_'.$mode.'_username');
  $attraction_api_pass = get_option('options_attraction_'.$mode.'_password');
  $attraction_api_url = get_option('options_attraction_api_'.$mode.'_url');
  $credentials = base64_encode( $attraction_api_user.':'.$attraction_api_pass);

  $futureDate = array();
  $datesArr = array();
  $datesAvilability = array();
  $dateTimeArr = array();
  $apiUrl = $attraction_api_url.'products/'.$id;

  $datesArr['date_from'] = date("Y-m-d");
  $datesArr['date_to'] = date("Y-m-d", strtotime("+1 year"));
  $apiUrl1 = $attraction_api_url.'products/'.$id.'?date_from='.$datesArr['date_from'].'&date_to='.$datesArr['date_to'];
  $response1 = wp_remote_get($apiUrl1, ['timeout'=> 120,'headers' => ['Authorization' => 'Basic ' . $credentials]]);

  $responseBody1 = wp_remote_retrieve_body( $response1 );
  $result1 = json_decode($responseBody1, true);

  $has_data = false;
  if($result1 && !isset($result1['error_desc'])) {
    $has_data = true;
    if(!is_array($result1['tickets'])){
      $tickets = json_decode($result1['tickets'],1);
    } else {
      $tickets = $result1['tickets'];
    }
    foreach ($tickets as $value) {
      if(isset($value['availability'])){
        foreach ($value['availability'] as $value1){
            if(!empty($value1['date'])){
                $datesAvilability[] = $value1['date'];
            }
        }

      }
    }

    $i=1;
    $date = '';
    $month = '';
    $year = '';
    $avilable = '';
    $date_time = '';
    foreach (array_unique($datesAvilability) as $value) {
      if($value >= date('Y-m-d')){
        if($i == 1){
          $futureDate[] = $value;
          $date_time = $value;
          $date_create = date_create($value);
          $avilable = date_format($date_create,"F d, Y");
          $date = date_format($date_create,"d");
          $month = date_format($date_create,"m");
          $year = date_format($date_create,"Y");
        }
        $i++;
      }
    }

    foreach ($tickets as $value) {
      if(isset($value['availability'])){
        foreach ($value['availability'] as $value1){
            if(($value1['date'] == $date_time) && ($value1['time'] != '')){
              $dateTimeArr[] = $value1['time'];
            }
        }
      }
    }

    $date_ajaxClass = !empty($dateTimeArr) ? 'date_ajaxClass' : '';
    $result = $result1;
  } 
  if($result):
 ?>
  <h2><?php echo $result['title']; ?></h2>
  <div class="gc-tour-single-content-row gc-hotel-detail-single">
    <div class="gc-tour-single-left-content">
      <div class="gc-single-tour-with-lightbox">
        <div class="gc-col-12">
           <div class="hotel-full-img">
              <?php 
              $i = 1;
             
              foreach( $result['photos'] as $value ): 
                   
                if($i == 1){  if(file_exists($value['img_med'])):?>
                        <img src="<?php echo $value['img_med']??$result['img_sml']; ?>" alt="image"/> 
                      <?php else :?>
                        <img src="<?=$result['img_sml']; ?>" alt="image"/> 

                <?php endif;  }
                $i++; 
              endforeach; ?> 
           </div> 
          <ul class="gc-hotel-light-box">
            <?php foreach( $result['photos'] as $value ): if(file_exists($value['img_med'])):?>
                <li class="hotel-thumb">
                  <img src="<?php echo $value['img_med'];?>" alt="image"/>
                </li>
            <?php endif; endforeach; ?>
        </div>
        
      </div>
    </div>
    <div class="gc-tour-single-right-content">
      <?php if(!empty($futureDate)){ ?>
        <div class="gc-tour-single-avilability">
          <form action="<?php echo home_url('attraction-booking'); ?>" method="post">
            <?php $j = 1;
              // echo "<pre>"; print_r($result['tickets']);
              foreach($result['tickets'] as $value){
                if($j != 1){
                  continue;
                } ?>
                  <p>From<span>£<?php echo $value['price_from']; ?></span> </p>
              <?php $j++;        
            }?>
            <input type="text" name="select_date" id="datepicker" class="<?php echo $date_ajaxClass; ?>" autocomplete="off">
            <input type="hidden" id="avilableDates" data-avilable_date="<?php echo $date;?>" data-avilable_month="<?php echo $month;?>" data-avilable_year="<?php echo $year;?>" data-avilable="<?php echo $avilable;?>" data-date='<?php echo json_encode(array_unique($datesAvilability)); ?>'>
            <?php if(!empty(array_unique($dateTimeArr))){ ?>
             <div class="gctcf-select-time">
              <select id="selectedTime" class="selectedTime" name="select_time">
                <?php foreach(array_unique($dateTimeArr) as $value){ 
                  ?>
                <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                <?php } ?>
              </select>
            </div>
            <?php } ?>
            <div class="gc-tour-price-variation">
              <?php foreach($result['tickets'] as $value){
                  $date = date_create($datesArr['date_to']); ?>
                <div class="ticket-wrapper">
                  <div class="ticket-wrap">
                    <label class="option" for="edit-tickets=ticket-id-<?php echo $value['ticket_id']; ?>"> <?php echo $value['type']; ?> (<?=$value['type_description']!=''?$value['type_description']:'Per Person'; ?>)</label>

                    <input type="hidden" name="tickets[<?php echo $value['ticket_id']; ?>][type]" value="<?php echo $value['type']; ?> (<?php echo $value['type_description']; ?>)">
                    <input type="hidden" name="tickets[<?php echo $value['ticket_id']; ?>][ticket_id]" value="<?php echo $value['ticket_id']; ?>">

                    <div class="qty-button-wrap">
                      <div class="qtybtn qtyminus" field="tickets[ticket-id-<?php echo $value['ticket_id']; ?>]">-</div>
                      <div class="align-left">
                        <div class="form-item form-type-numberfield form-item-tickets-ticket-id-<?php echo $value['ticket_id']; ?>">
                          <input class="qty form-text form-number" type="number" id="edit-tickets-ticket-id-<?php echo $value['ticket_id']; ?>" name="tickets[<?php echo $value['ticket_id']; ?>][ticket-qty]" data-value="0" data-price="<?php echo $value['price_from']; ?>"value="0" min="0" max="" step="1">
                        </div>
                      </div>
                      <div class="qtybtn qtyplus" field="tickets[ticket-id-<?php echo $value['ticket_id']; ?>]">+</div>
                    </div>
                    <span class="price">£<?php echo $value['price_from']; ?> </span>
                    <input type="hidden" class="ticket-price-pure" name="tickets[<?php echo $value['ticket_id']; ?>][price]" value="<?php echo $value['price_from']; ?>">
                    <?php $inputValue = array_key_exists('date_id', $value) ? $value['date_id'] : 'valoare_implicită'; ?>
                    <input value="<?php echo date_format($date,"Ymd"); ?>" id="dateid_<?php echo $inputValue; ?>" name="dateid" type="hidden">
                    <?php foreach ($value['attributes'] as $key => $attribute) { ?>
                      <input value="<?php echo $attribute['attribute_id']; ?>" name="tickets[<?php echo $value['ticket_id']; ?>][attributes][<?php echo $key; ?>][attribute_id]" type="hidden">
                      <input value="<?php echo $attribute['title']; ?>" name="tickets[<?php echo $value['ticket_id']; ?>][attributes_name][<?php echo $attribute['attribute_id']; ?>]" type="hidden">
                      
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
              <div class="gc-tour-order-button-wrapper">


                <input type="hidden" name="dateid" id="dateId">
                <input type="hidden" name="date_from" id="datefrom" value="<?php echo $datesArr['date_from']; ?>">
                <input type="hidden" name="date_to" id="dateto" value="<?php echo $datesArr['date_to']; ?>">
                <input type="hidden" class="ticket-total-price" name="total-price" value="0">
                <input type="hidden" name="product_name" value="<?php echo $result['title']?>">
                <input type="hidden" id="productId" name="product_id" value="<?php echo $value['product_id']; ?>">
                <input type="hidden" name="ticket_id" value="<?php echo $value['ticket_id']; ?>">

                <button type="submit" class="button book-now-button" name="book-now">Book Now</button>
                <div class="gc-tour-total-price">
                  <p>Total<span>£0</span></p>
                </div>
              </div>
            </div>
          </form>
        </div>
      <?php } else { ?>
        <div class="gc-tour-single-avilability">
          <p class="attraction-not-avilable">Please call for details</p>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="gctcf-attraction-content">
    <p><?php echo $result['desc_short'];?></p>
    <div class="gc-tour-discover-content">
        <div class="field-item">
          <p><?php echo $result['desc'];?></p>
          <?php echo $result['faq'];?>
        </div>
        <div class="field-item">
          <h3>Experience Includes</h3>
          <ul>
            <li><?php echo $result['ticket_includes'];?></li>
            
          </ul>
        </div>
        <div class="field-item">
          <h3>Experience Excludes</h3>
          <ul>
            <li>
              <div><?php echo $result['ticket_excludes'];?></div>
            </li>
          </ul>
        </div> 
        <div class="field-item">
          <!-- <h3>Departs</h3> -->
          <div class="field extra-field"><?php echo $result['departure_time'];?></div>
        </div>
        <div class="field-item">
          <!-- <h3>Start times</h3> -->
          <div class="field extra-field"><?php echo $result['start_time'];?></div>
        </div>
        <div class="field-item">
          <!-- <h3>Duration</h3> -->
          <div class="field extra-field"><?php echo $result['duration'];?></div>

        </div>
    </div>
  </div>
<?php else: ?>
  <h1>Some Error Occurred.</h1>
<?php endif; } ?>