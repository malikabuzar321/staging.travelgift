<?php

class Travellanda {
    private $username = '';
    private $password = '';
    private $path = '';
    private $mode = 'live';

    public function __construct() {
        $this->path = dirname(__FILE__) . '/travellanda';
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setMode($mode = 'live') {
        $this->mode = $mode;
    }

    public function sendRequest($request_type, $body = array(), $hostname = '') {
        if ($this->mode == 'test') {
            $hostname = 'http://xmldemo.travellanda.com/xmlv1';
        } else {
            $hostname = ($hostname) ? $hostname : 'http://xml.travellanda.com/xmlv1';
        }

        $xml_writer = new XMLWriter();
        $xml_writer->openMemory();
        $xml_writer->setIndent(true);
        $xml_writer->startDocument('1.0', 'utf-8');
        $xml_writer->startElement('Request');
        $xml_writer->startElement('Head');
        $xml_writer->startElement('Username');
        $xml_writer->text($this->username);
        $xml_writer->endElement(); //Username
        $xml_writer->startElement('Password');
        $xml_writer->text($this->password);
        $xml_writer->endElement(); //Password
        $xml_writer->startElement('RequestType');
        $xml_writer->text($request_type);
        $xml_writer->endElement(); //RequestType
        $xml_writer->endElement(); //Head
        $xml_writer->startElement('Body');
        $this->buildBody($body, $xml_writer);
        $xml_writer->endElement(); //Body
        $xml_writer->endElement(); //Request
        $xml_writer->endDocument();

        $request_body = $xml_writer->flush(true);

        // echo '<pre>' . htmlentities($request_body) . '</pre>';
        // exit();
        return wp_remote_post($hostname, array(
            'headers' => array(
                'Accept' => 'application/xml',
                'Accept-Encoding' => 'gzip',
            ),
            'timeout' => 180,
            'body' => array(
                'xml' => $request_body,
            ),
        ));
    }

    public function buildBody($body, $xml_writer) {
        //echo '<pre>' . print_r($body, true) . '</pre>';
        if (!empty($body)) {
            foreach ($body as $element => $value) {
                //echo 'start ' . $element . '<br>';
                if (!is_numeric($element)) {
                    $xml_writer->startElement($element);
                }
                if (is_array($value)) {
                    //echo 'inner ' . $element . '<br>';
                    //echo '<pre>' . print_r($value, true) . '</pre>';
                    $this->buildBody($value, $xml_writer);
                } else {
                    //echo 'write ' . $value . '<br>';
                    $xml_writer->text($value);
                }
                if (!is_numeric($element)) {
                    $xml_writer->endElement();
                }
                //echo 'end ' . $element . '<br>';
            }
        }
    }

    public function convertToJson($xml, $file = '') {
        $simple_xml = simplexml_load_string($xml);
        $json = json_encode($simple_xml);
        if ($file) {
            file_put_contents($this->path . '/' . $file, $json);
        } else {
            return $json;
        }
    }

    public function getCountries() {
        $response = $this->sendRequest('GetCountries');
        if (isset($response['body'])) {
            $this->convertToJson($response['body'], 'countries.json');
        }
    }

    public function getCities($country_code = '') {
        $body = ($country_code) ? array(
            'CountryCode' => $country_code
        ) : array();

        $response = $this->sendRequest('GetCities', $body);
        if (isset($response['body'])) {
            $this->convertToJson($response['body'], 'cities.json');
        }
    }

    public function getAllHotels() {
        $countries = json_decode(file_get_contents($this->path . '/countries.json'), true);
        if ($countries) {
            echo '<pre>' . print_r($countries, true) . '</pre>';
            if (isset($countries['Body']['Countries']['Country'])) {
                foreach ($countries['Body']['Countries']['Country'] as $country) {
                    $this->getHotels($country['CountryCode']);
                }
            }
        }
        die;
    }

    public function getHotelsAll($country_code = '') {
        $body = array(
            'CountryCode' => $country_code
        );
        return $this->sendRequest('GetHotels', $body);
    }

    public function getHotels($country_code_or_city_id = '') {
        $body = (is_numeric($country_code_or_city_id)) ? array(
            'CityId' => $country_code_or_city_id
        ) : array(
            'CountryCode' => $country_code_or_city_id
        );

        // echo '<pre>' . print_r($body, true) . '</pre>';

        $response = $this->sendRequest('GetHotels', $body);
        if (isset($response['body'])) {
            //echo '<pre>' . print_r(htmlentities($response['body']), true) . '</pre>';
            //die;
            $data = $this->convertToJson($response['body']);
            return $data;
        }
    }

    public function getHotelDetails($hotel_ids = array()) {
        $body = array(
            'HotelIds' => array(),
        );
        array_splice($hotel_ids, 450);
        foreach ($hotel_ids as $hotel_id) {
            $body['HotelIds'][] = array(
                'HotelId' => $hotel_id,
            );
        }
        // return $body;
        return $this->sendRequest('GetHotelDetails', $body);
    }

    public function citySearch($region) {
        global $wpdb;
        $cities = $wpdb->get_results("SELECT * FROM `travellanda_cities` WHERE `city_name` LIKE '%$region%' ORDER BY `id` DESC LIMIT 0,10");
        if($cities){
            foreach($cities as $city) {
                $code = strtolower(trim($city->country_name) . '_' . strtolower(trim($city->city_name)));
                $results[$code]['country_code'] = trim($city->country_code);
                $results[$code]['country_name'] = trim($city->country_name);
                $results[$code]['city_name'] = trim($city->city_name);
                $results[$code]['ids'][] = trim($city->city_id);
            }
        }
    	// print_r($region);
        // $results = array();
        // $countries = json_decode(file_get_contents($this->path . '/countries.json'), true);
        
        // $cities = json_decode(file_get_contents($this->path . '/cities.json'), true);
        
        // if ($cities) {
        // 	//error_log(print_r($cities));
        // 	//die();
        //     //echo '<pre>';
        //     //print_r($cities);
        //     //echo '</pre>';
        //     foreach ($cities['Body']['Countries']['Country'] as $country) {
        //         foreach ($country['Cities']['City'] as $city) {
        //             $country_name = '';
        //             if (is_array($city) && isset($city['CityName']) && stripos($city['CityName'], $region) !== false) {
        //                 foreach ($countries['Body']['Countries']['Country'] as $country_match) {
        //                     if ($country_match['CountryCode'] == $country['CountryCode']) {
        //                         $country_name = $country_match['CountryName'];
        //                         if ($country_name == 'United States') {
        //                             $country_name = 'USA';
        //                         }
        //                         if ($country_name == 'United Kingdom') {
        //                             $country_name = 'UK';
        //                         }
        //                         break;
        //                     }
        //                 }
        //                 $code = strtolower(trim($country_name) . '_' . strtolower(trim($city['CityName'])));
        //                 $results[$code]['country_code'] = trim($country['CountryCode']);
        //                 $results[$code]['country_name'] = trim($country_name);
        //                 $results[$code]['city_name'] = trim($city['CityName']);
        //                 $results[$code]['ids'][] = trim($city['CityId']);
        //             }
        //         }
        //     }
        // }

        return $results;
    }

    public function hotelSearch($search = array()) {
        $body = array();
        if ($search['type'] == 'city') {
            $body['CityIds'] = array();
        } else {
            $body['HotelIds'] = array();
        }

        foreach ($search['locations'] as $location_id) {
            if ($search['type'] == 'city') {
                $body['CityIds'][] = array(
                    'CityId' => $location_id,
                );
            } else {
                $body['HotelIds'][] = array(
                    'HotelId' => $location_id,
                );
            }
        }

        $body['CheckInDate'] = $search['check_in_date'];
        $body['CheckOutDate'] = $search['check_out_date'];
        $body['Rooms'] = array();
        foreach ($search['rooms'] as $index => $occupants) {
            $room = array();
            foreach ($occupants as $occupant_type => $occupants_info)
            if ($occupant_type == 'adult') {
                $room['NumAdults'] = $occupants_info;
            } else {
                $room['Children'] = array();
                foreach ($occupants_info as $child_age) {
                    $room['Children'][] = array(
                        'ChildAge' => $child_age,
                    );
                }
            }

            $body['Rooms'][] = array(
                'Room' => $room,
            );
        }

        $body['Nationality'] = $search['nationality'];
        $body['Currency'] = $search['currency'];
        $body['AvailableOnly'] = ($search['available_only']) ? '1' : '0';

        return $this->sendRequest('HotelSearch', $body, 'http://xml5.travellanda.com/xmlv1');
    }

    public function hotelPolicies($option_id) {
        $body = array(
            'OptionId' => $option_id,
        );

        return $this->sendRequest('HotelPolicies', $body);
    }

    public function hotelBooking($booking = array()) {
        $body = array();
        $body['OptionId'] = $booking['option_id'];
        $body['YourReference'] = $booking['reference'];
        $body['Rooms'] = array();
        foreach ($booking['rooms'] as $room_detail) {
            $room = array();
            $room['RoomId'] = $room_detail['room_id'];
            $room['PaxNames'] = array();
            foreach ($room_detail['adults'] as $adult) {
                $room['PaxNames'][] = array(
                    'AdultName' => array(
                        'Title' => $adult['title'],
                        'FirstName' => $adult['first_name'],
                        'LastName' => $adult['last_name'],
                    ),
                );
            }

            foreach ($room_detail['children'] as $child) {
                $room['PaxNames'][] = array(
                    'ChildName' => array(
                        'FirstName' => $child['first_name'],
                        'LastName' => $child['last_name'],
                    ),
                );
            }

            $body['Rooms'][] = array(
                'Room' => $room,
            );
        }

        return $this->sendRequest('HotelBooking', $body);
    }

    public function hotelBookingCancel($booking_reference) {
        $body = array(
            'BookingReference' => $booking_reference,
        );

        return $this->sendRequest('HotelBookingCancel', $body);
    }
}