<?php
    header("Content-Type:text/xml");
        
    $api_key    = $_GET['api_key'];
    $harvest_id     = $_GET['harvest_id'];
    $c_token       = $_GET['c_token'];

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
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $row_url["url"] . "&ucuser=" . $row_url["contributor"]);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);

	$xml = simplexml_load_string($output);

	$items = new SimpleXMLElement("<items></items>");
	
	foreach ($xml->xpath('//item') as $item) {
		$child = $items->addChild($item->getName());
		foreach($item->attributes() as $a => $b) {
			$child->addAttribute($a, $b);
		}
	
	}
	
	echo $items->asXML();
    
?>
