<?php
    header("Content-Type:text/xml");
        
    $api_key    = $_GET['api_key'];
    $harvest_id     = intval($_GET['harvest_id']);

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
	
	if($harvest_id==0) {
		
		if(isset($_GET['url'])) 	$url = addslashes($_GET['url']);
		if(isset($_POST['url'])) 	$url = addslashes($_POST['url']);

		if(isset($_GET['contributor'])) 	$contributor = addslashes($_GET['contributor']);
		if(isset($_POST['contributor']))	$contributor = addslashes($_POST['contributor']);
		
		$results = mysqli_query($con,"INSERT INTO harvests(tstamp,api_key,url,contributor) VALUES(NOW(),'$api_key','$url','$contributor')");
		$harvest_id = mysql_insert_id();
		
	} else {

		$url = mysqli_query($con,"SELECT url, contributor FROM `harvests` WHERE api_key = '" . $api_key . "' AND id = '" . $harvest_id . "'" );
		$row_url = mysqli_fetch_array($url);
		$ret = "<harvest id=\"".$harvest_id."\">"
			  ."<url>".htmlentities($row_url["url"])."</url>"
			  ."<contributor>".htmlentities($row_url["contributor"])."</contributor>"
			  ."</harvest>";
		
		echo $ret;
		exit;
	}
	
    
 	header('HTTP/1.1 400 Bad Request', true, 400);
	exit;
    
?>
