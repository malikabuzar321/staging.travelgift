<?php
// Include fișierul wp-load.php pentru a inițializa mediul WordPress
require_once('wp-load.php');

// Apelează funcția și obține configurația API
$config = get_api_config();

// Afișează configurația la ecran
echo '<pre>';
print_r($config);
echo '</pre>';
?>