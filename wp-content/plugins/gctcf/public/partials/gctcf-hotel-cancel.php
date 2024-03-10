<?php
if(isset($_POST['hotel_cancel']) && !empty($_POST['hotel_cancel']))
{ 

  $settings = get_api_config();
  $api_url = $settings['url'];
  $api_org = $settings['org'];
  $api_user = $settings['user'];
  $api_pass = $settings['pass'];
	$booking_id=$_REQUEST['hotel_booking_id'];

  /************************API calling********************************/		 
  $user_xmldata='<?xml version="1.0" encoding="utf-8"?>	 
  	  <BookingCancel>
        <Authority>
        <Org>testgc4t</Org>
        <User>testxml</User>
        <Password>testxml</Password>
      <Currency>GBP</Currency>
      <Language>en</Language>
      <TestDebug>false</TestDebug>
      <Version>1.28</Version>
    </Authority>
    <BookingId>'.$booking_id.'</BookingId>
    <CommitLevel>confirm</CommitLevel>
  </BookingCancel>';		 
		 

  $roomxml_api_url = 'http://www.stubademo.com/RXLStagingServices/ASMX/XmlService.asmx';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $roomxml_api_url);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                         'Content-type: text/xml', 
                                         'Content-length: ' . strlen($user_xmldata)
                                       ));
  $room_xml_api_output_for_hotel_search = curl_exec($ch);


  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  if(curl_exec($ch) === false)
  {
      echo 'Curl error: ' . curl_error($ch);
  }
  else
  {
     // echo '<br />';
  	//echo 'Room Search operation completed';
  }

  curl_close($ch);

  $room_api_xml_response_for_hotel_search = simplexml_load_string($room_xml_api_output_for_hotel_search);

  echo '<pre>';
  var_dump($room_api_xml_response_for_hotel_search);
  echo '</pre>';
  echo '<hr/>';

}
else
{
?>
<div class="gc-container">
  <div class="gc-row">
    <form action="" method="post">
         <div class="form-group gc-sm-4 ">
          <input type="text" name="hotel_booking_id" class="gc-form-control" placeholder="Enter booking id" />
         </div>
         <div class="form-group gc-sm-2 ">          
          <input type="submit" name="hotel_cancel" class="btn btn-primary btn-block" value="CANCEL" style="width: 100%;margin: auto;display: -webkit-box;" />
         </div>
    </form>
  </div>
</div>

<?php } ?>