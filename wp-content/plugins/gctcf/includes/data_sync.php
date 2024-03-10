<?php
function attraction_destination(){
	$mode = get_option('options_attraction_api_mode');
	$username = get_option('options_attraction_'.$mode.'_username');
	$password = get_option('options_attraction_'.$mode.'_password');
	$url = get_option('options_attraction_api_'.$mode.'_url');
    // Fetch data from the external API
    $credentials = base64_encode( $username . ':' . $password );

	$headers = array(
	    'Authorization' => 'Basic ' . $credentials
	);
    $response = wp_remote_get($url."destinations",['headers'=>$headers]);

    if (is_wp_error($response)) {
        // Handle error
        error_log('Failed to fetch data from API: ' . $response->get_error_message());
        return;
    }

    $data = wp_remote_retrieve_body($response);

    // Parse and process the data
    $parsed_data = json_decode($data, true);
    $destination_data = $parsed_data['data'];
    // Insert or update data in the database
    if ($destination_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'attraction_destinations';

        foreach ($destination_data as $item) {

        	$path = wp_upload_dir()['baseurl'];
        	$default = $path."/2024/default-image-icon-missing-picture-page-vector-40546530.jpg";
        	$response = wp_remote_head( $item['img_sml'] );

        	if(wp_remote_retrieve_response_code($response) === 200){
        		$image = $item['img_sml'];
        	} else {
        		continue;
        	}
        	$new_item = [
        		'Dest_ID' => $item['id'],
        		'Parent_id' => $item['parent_id'],
        		'Title' => $item['title'],
        		'Img_sml' => $image,
        		'Description' => $item['desc'],
        		'Updated' => $item['updated'] 	
        	];
        	//Check if destination exists 
        	$check = $wpdb->get_results("SELECT * FROM $table_name WHERE `Dest_id`=".$item['id']);
        	if(!$check){
            	$wpdb->insert($table_name, $new_item); // Example data types
        	} else {
        		if($check[0]->Updated != $item['updated']){
        			$wpdb->update($table_name, $new_item,['id'=>$check[0]->id]);
        		}
        	}
        }
    }
}

function attraction_tags(){
	$mode = get_option('options_attraction_api_mode');
	$username = get_option('options_attraction_'.$mode.'_username');
	$password = get_option('options_attraction_'.$mode.'_password');
	$url = get_option('options_attraction_api_'.$mode.'_url');
    // Fetch data from the external API
    $credentials = base64_encode( $username . ':' . $password );

	$headers = array(
	    'Authorization' => 'Basic ' . $credentials
	);
    $response = wp_remote_get($url."tags",['headers'=>$headers]);

    if (is_wp_error($response)) {
        // Handle error
        error_log('Failed to fetch data from API: ' . $response->get_error_message());
        return;
    }

    $data = wp_remote_retrieve_body($response);

    // Parse and process the data
    $parsed_data = json_decode($data, true);

    $tags_data = $parsed_data['data'];
    // Insert or update data in the database
    if ($tags_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'attraction_tags';

        foreach ($tags_data as $item) {
        	$new_item = [
        		'Tag_ID' => $item['id'],
        		'Tag' => $item['tag'],
        		'CategoryID' => $item['category_id'],
        		'Category' => $item['category'],
        		'ParentID' => $item['parent_id'],
        		'Parent' => $item['parent'] 	
        	];
        	//Check if Tag exists 
        	$check = $wpdb->get_results("SELECT * FROM $table_name WHERE `Tag_id`=".$item['id']);
        	if(!$check){
            	$wpdb->insert($table_name, $new_item); // Example data types
        	} else {
        		if($check[0]->Updated != $item['updated']){
        			$wpdb->update($table_name, $new_item,['id'=>$check[0]->id]);
        		}
        	}
        }
    }
}

function attraction_products($offset=0){
	$ins = $upd = 0;
	$mode = get_option('options_attraction_api_mode');
	$url = get_option('options_attraction_api_'.$mode.'_url');
	$response = wp_remote_get($url."products?limit=100&offset=".$offset, ['timeout'=> 120]);
	if (is_wp_error($response)) {
		$error = 'Failed to fetch data from API: ' . $response->get_error_message();
        echo $error;
        error_log($error);
        return;
    }
    $data = wp_remote_retrieve_body($response);
    // Parse and process the data
    $parsed_data = json_decode($data, true);
    $products_data = $parsed_data['data'];
	global $wpdb; $upd = 0;
	if($products_data){
		foreach($products_data as $a){
			$offset++;
			$check_res = $wpdb->get_results("SELECT * FROM `attractions_data` WHERE attraction_id = ".$a['id']);	
			$check = [];
			if($check_res){
				$check = json_decode(json_encode($check_res[0]),1);
			}
			$rec = [
				'attraction_id' => $a['id'],
		        'title' => $a['title'],
		        'updated' =>  $a['updated'],
		        'dest' => $a['dest'],
		        'price_from_adult' => $a['price_from_adult'],
		        'price_from_child' => $a['price_from_child'],
		        'rrp_adult' => $a['rrp_adult'],
		        'rrp_child' => $a['rrp_child'],
		        'price_from_all' => json_encode($a['price_from_all']),
		        'img_sml' => $a['img_sml']
			];
			if($check){
				if($check['updated'] != $a['updated']){
					$wpdb->update('attractions_data',$rec,['id'=>$check['id']]);
					$upd++;
				}
			} else {
				$wpdb->insert('attractions_data',$rec);
				$ins++;
			}
		} 
		pre($offset);
		pre($ins." records added");
		pre($upd." records updated");
		attraction_products($offset);
	}
}

function attraction_products_extension(){
	global $wpdb;
	$prod_ids = [];
	$products = $wpdb->get_results("SELECT * FROM `attractions_data` WHERE `desc_short`=''");
	if($products){
		foreach($products as $pr){
			$prod_ids[] = $pr->attraction_id;
		}
	}
	$mode = get_option('options_attraction_api_mode');
	$url = get_option('options_attraction_api_'.$mode.'_url');
	$username = get_option('options_attraction_'.$mode.'_username');
	$password = get_option('options_attraction_'.$mode.'_password');
    $credentials = base64_encode( $username . ':' . $password );
	$headers = array(
	    'Authorization' => 'Basic ' . $credentials
	);
	$response = wp_remote_get($url."products?view=extended&ids=".implode(',',$prod_ids), ['headers'=>$headers,'timeout'=>120]);
	if (is_wp_error($response)) {
		$error = 'Failed to fetch data from API: ' . $response->get_error_message();
        echo $error;
        error_log($error);
        return;
    }
    $data = wp_remote_retrieve_body($response);
    // Parse and process the data
    $parsed_data = json_decode($data, true);
    if(isset($parsed_data['data'])){
	    $products_data = $parsed_data['data'];
		if($products_data){
			foreach($products_data as $a){
				$rec = [
			        'title' => $a['title'],
			        'updated' =>  $a['updated'],
			        'dest' => $a['dest'],
			        'price_from_adult' => $a['price_from_adult'],
			        'price_from_child' => $a['price_from_child'],
			        'rrp_adult' => $a['rrp_adult'],
			        'rrp_child' => $a['rrp_child'],
			        'price_from_all' => json_encode($a['price_from_all']),
			        'url' => $a['url'],
			        'desc_short' => $a['desc_short'],
			        'img_sml' => $a['img_sml']
				];
				echo $wpdb->update('attractions_data',$rec,['attraction_id'=>$a['id']]);
			} 
		}
    }
}
// $rec = [
			// 	'attraction_id' => $a['id'],
		    //     'title' => $a['title'],
		    //     'updated' =>  $a['updated'],
		    //     'dest' => $a['dest'],
		    //     'price_from_adult' => $a['price_from_adult'],
		    //     'price_from_child' => $a['price_from_child'],
		    //     'rrp_adult' => $a['rrp_adult'],
		    //     'rrp_child' => $a['rrp_child'],
		    //     'price_from_all' => json_encode($a['price_from_all']),
		    //     'tickets' =>json_encode($a['tickets']),
		    //     'pricing_method' => $a['pricing_method'],
		    //     'seasons' => json_encode($a['seasons']),
		    //     'url' => $a['url'],
		    //     'refundable' => $a['refundable'],
		    //     'on_request' => $a['on_request'],
		    //     'desc_short' => $a['desc_short'],
		    //     'img_sml' => $a['img_sml']
			// ];
			// $response2 = wp_remote_get($url."products?offset=".$offset, ['timeout'=> 120]);
				// if (is_wp_error($response2)) {
				// 	$error = 'Failed to fetch data from API: ' . $response2->get_error_message();
			    //     echo $error;
			    //     error_log($error);
			    //     return;
			    // }
			    // $data2 = wp_remote_retrieve_body($response2);
			    // // Parse and process the data
			    // $parsed_data2 = json_decode($data2, true);
			    // $product_data = $parsed_data2['data'];
			    // $rec2 = [
			    //     'tickets' =>json_encode($products_data['tickets']),
			    //     'pricing_method' => $products_data['pricing_method'],
			    //     'seasons' => json_encode($products_data['seasons']),
			    //     'url' => $products_data['url'],
			    //     'refundable' => $products_data['refundable'],
			    //     'on_request' => $products_data['on_request'],
			    //     'desc_short' => $products_data['desc_short']
				// ];
				// $merged = array_merge($rec,$rec2);
?>