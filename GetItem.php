<?php
    header("Content-Type:text/xml");
        
    $api_key    = $_GET['api_key'];
    $harvest_id     = $_GET['harvest_id'];
    $item_id       = $_GET['item_id'];

    $username="root";
    $password="wewantmore";
    $server="localhost";
    $database="wikimedia";

    $con=mysqli_connect($server,$username,$password,$database);

    if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $api_key_check = mysqli_query($con,"SELECT api_key FROM `users` WHERE api_key = '" . $api_key . "'" );

    $row_api_key_check = mysqli_fetch_array($api_key_check);
    
    if(!isset($row_api_key_check)){
		header('HTTP/1.1 400 Bad Request', true, 400);
		exit;
	}
	
	$harvest_id_check = mysqli_query($con,"SELECT id FROM `harvests` WHERE id = '" . $harvest_id . "'" );

    $row_harvest_id_check = mysqli_fetch_array($harvest_id_check);
    
    if(!isset($row_harvest_id_check)){
		header('HTTP/1.1 400 Bad Request', true, 400);
		exit;
	}
	
	$url = mysqli_query($con,"SELECT url, contributor FROM `harvests` WHERE api_key = '" . $api_key . "' AND id = '" . $harvest_id . "'" );
	
	$row_url = mysqli_fetch_array($url);
	
	$agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "http://tools.wmflabs.org/magnus-toolserver/commonsapi.php?image=" . str_replace(' ', '_', $item_id));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	$output = curl_exec($ch);
	curl_close($ch);

	$xml = simplexml_load_string($output);
	
	echo $xml->asXML();
    
?>
