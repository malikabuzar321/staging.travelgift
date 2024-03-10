<?php
// if(isset($hotel_data['Body'])){
//     $hotel_data = $hotel_data['Body']['Hotels']['Hotel'];
//     $hotel_id=$hotel_data['HotelId'];
//     $hotel_name=$hotel_data['HotelName'];
//     $hotel_country=$hotel_data['Location'];
//     $hotel_city=$hotel_data['Address'];
//     $hotel_stars=$hotel_data['StarRating'];
//     $hotel_img=$hotel_data['Images']['Image'][0]??'';
// } else {
    $hotel_id=$hotel_data->hotel_id;
    $hotel_name=$hotel_data->hotel_name;
    // $hotel_country=$hotel_data->Address->Country;
    // $hotel_city=$hotel_data->Address->City;
    $hotel_stars=$hotel_data->star_rating;
    $hotel_img=$hotel_data->hotel_image;
    $hotel_desc = $hotel_data->hotel_desc;

// }

?>

<div class="hotel-item item">
    <div class="hotel-image">
        <a href="<?php echo home_url();?>/hotel-details?hotel-id=<?php echo $hotel_id; ?>">
        <img src="<?=$hotel_img?>"  data-srcset="<?=$hotel_img?>" data-src="<?=$hotel_img?>"  alt="Online giftcard for hotel <?=$hotel_name;?>" class="img-responsive lazyload">
        
    </div>
    <div class="hotel-contact-inner">
    <div class="hotel-title">
        <h5><a href="#" title=""><?php echo ucwords($hotel_name); ?></a></h5>
    </div>
    <div class="rating_and_booknow">
        <span class="rating">
             <?php for($k=1;$k<=$hotel_stars;$k++){ 
                echo '<i class="fa fa-star"></i>';
                 } ?>
        </span>
  
    </div>
    <p ><?php
		 // strip tags to avoid breaking any html
		// $hotel_description=($hotel_data->Description->Text??$hotel_data['Description']);
		$string = strip_tags($hotel_desc);
		if (strlen($string) > 150) {
			// truncate string
			$stringCut = substr($string,0,150);
			// make sure it ends in a word so assassinate doesn't become ass...
			$string = nl2br(substr($stringCut, 0, strrpos($stringCut, ' '))); 
		}
		echo $string; ?>
            
    </p>
    <div class="book_now_section">
        <a href="<?php echo home_url();?>/hotel-details?hotel-id=<?php echo $hotel_id; ?>">View Details</a>
    </div>
    </div>
</div>