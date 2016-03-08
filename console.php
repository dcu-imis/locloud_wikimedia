<!DOCTYPE HTML>
<html> 
	
<head>
<style>
	table, th, td {
		border: 1px solid black;
		border-collapse: collapse;
	}
	th, td {
		padding: 5px;
		text-align: left;    
	}
</style>
</head>
<body>

	<form action="console.php" method="post">
	API url: <input type="text" name="api"> 
	User: <input type="text" name="user"><input type="submit"><br>
	</form>

	<?php

	echo "</br></br>";

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $_POST["api"].'&ucuser='.$_POST["user"]);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);

	$xml = simplexml_load_string($output);

	if( isset($_POST["api"]) && isset($_POST["user"]) ) 
		echo "<table style=\"width:100%\">
		  <tr>
			<th>Author</th>
			<th>Description</th>
			<th>Source</th>
			<th>Date</th>
			<th>Image</th>
		  </tr>";
		  
	$agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
		  
	foreach ($xml->xpath('//item') as $item) {
		$cc = curl_init();

		curl_setopt($cc, CURLOPT_URL, "http://tools.wmflabs.org/magnus-toolserver/commonsapi.php?image=" . str_replace(' ', '_', $item['title']));
		curl_setopt($cc, CURLOPT_HEADER, 0);
		curl_setopt($cc, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cc, CURLOPT_USERAGENT, $agent);		
		$ccoutput = curl_exec($cc);
		curl_close($cc);
		
		$ccxml = simplexml_load_string($ccoutput);
		
		if( isset($ccxml->file->author)){
		  echo "<tr>
			<td>" . $ccxml->file->author . "</td>
			<td>" . $ccxml->description->language . "</td>
			<td>" . $ccxml->file->source . "</td>
			<td>" . $ccxml->file->date . "</td>
			<td><a href=\"" . $ccxml->file->urls->file . "\"><img src=\"" . $ccxml->file->urls->file . "\" alt=\"" . $ccxml->file->name . "\" height=\"150\" width=\"150\"></a></td>
		  </tr>";
		}
	
	}

	if( isset($_POST["api"]) && isset($_POST["user"]) ) 
	echo "</table>";
	
	echo "</br></br>API url for the next items: " . $_POST["api"].'&ucuser='.$_POST["user"] . "&uccontinue=" . $xml->{'query-continue'}->usercontribs["uccontinue"];

	?>

</body>
</html>
