<?php
$waf_allow_list = array (
  0 => '172.71.178.162',
  1 => '172.68.186.176',
  2 => '169.1.19.8',
);
return $waf->is_ip_in_array( $waf_allow_list );
