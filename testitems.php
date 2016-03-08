<?php

	$api_key    = "PolMay";
    $harvest_id     = '2';

	$provider_id = "25";
	$provider_name = "CHB";
	
	$xml1 = simplexml_load_file("/home/eafiontzi/paulmaeyaert_test.xml");

	$agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	
	$xml_header = "<?xml version='1.0' encoding='utf-8'?>";
	$xml_head = "<rdf:RDF xmlns:oai_dc='http://www.openarchives.org/OAI/2.0/oai_dc/' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:edm='http://www.europeana.eu/schemas/edm/' xmlns:dcterms='http://purl.org/dc/terms/' xmlns:ore='http://www.openarchives.org/ore/terms/' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#' xmlns:wgs84_pos='http://www.w3.org/2003/01/geo/wgs84_pos#' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:skos='http://www.w3.org/2004/02/skos/core#'>\n";
	$xml_foot = " </rdf:RDF>\n";
	
	foreach ($xml1->xpath('//item') as $item) {
		$cc = curl_init();		
		$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=".$api_key."&harvest_id=".$harvest_id."&item_id=".str_replace(' ', '_', $item['title']);
		echo $url;	
		curl_setopt($cc, CURLOPT_URL, $url);
		curl_setopt($cc, CURLOPT_HEADER, 0);
		curl_setopt($cc, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cc, CURLOPT_USERAGENT, $agent);
		$ccoutput = curl_exec($cc);
		curl_close($cc);
		$ccxml = simplexml_load_string($ccoutput);
		//echo $ccxml->asXML();		
		
		//EDM
		$xml = "<edm:ProvidedCHO rdf:about='http://more.locloud.eu/object/".$provider_name."/".$ccxml->file->sha1."'>\n";
		if( isset($ccxml->file->author)) $xml.= "<dc:creator>".str_replace('&#039;', '&apos;', htmlspecialchars(strip_tags($ccxml->file->author), ENT_QUOTES))."</dc:creator>\n";	
		if( isset($ccxml->file->date)) $xml.= "<dc:date>".str_replace('&#039;', '&apos;', htmlspecialchars(strip_tags($ccxml->file->date), ENT_QUOTES))."</dc:date>\n";
		$language= $ccxml->description->language;
		$des_lans = explode(" ", $language);
		$lan_array = array();
		for ($i=0;$i<count($des_lans);$i++){
			if (strpos($des_lans[$i],'lang=') !== false) array_push($lan_array,$des_lans[$i]);		
		}		
		$descr= explode("<div", $language);
		$descr_temp= explode("<table", $language);
		//echo (count($descr));
		//echo (count($descr_temp));
		//only has table
		if (count($descr)==1 && count($descr_temp)>1){
			$descrtag="<table";
			//remove span elements if table exists
			$dom = new DOMDocument();
			$dom->loadHTML($descrtag.$descr_temp[1]);
			$dom->preserveWhiteSpace = false;
			/*$elements = $dom->getElementsByTagName('span');
			while($span = $elements->item(0)) {    
				$span_class = $span->getAttribute('class');
				echo $span_class."<br/>";
				if (strpos($span_class,'description') !== false) {} 
				else echo 'den exo';
					$span->parentNode->removeChild($span);
					
			}*/
			$xpath = new DOMXPath($dom);
			foreach($xpath->query('//span[contains(attribute::class, "layouttemplateargs")]') as $e ) {
				// Delete this node
				$e->parentNode->removeChild($e);
			}
			foreach($xpath->query('//span[contains(attribute::class, "plainlinks noprint")]') as $e ) {
				// Delete this node
				$e->parentNode->removeChild($e);
			}
			foreach($xpath->query('//span[contains(attribute::class, "wlmreview plainks")]') as $e ) {
				// Delete this node
				$e->parentNode->removeChild($e);
			}
			
			$descr= array ('', preg_replace('/<[^>]*>/', '', str_replace('&#039;', '&apos;', str_replace('&amp;#160;', '',htmlspecialchars(strip_tags($dom->saveHTML()), ENT_QUOTES)))));
			$table = 1;
		}
		//only has div
		else if (count($descr)>1 && count($descr_temp)==1) {
			//echo "div";
			$descrtag="<div";
			$table = 0;
		}	
		//has neither table not div
		else if (count($descr)==1 && count($descr_temp)==1) {
			$descr= array ('', preg_replace('/<[^>]*>/', '', str_replace('&#039;', '&apos;', str_replace('&amp;#160;', '',htmlspecialchars(strip_tags($language), ENT_QUOTES)))));
		}
		//has both table and div
		else{
			//echo "both";
			$descrtag="<table";		
			$descr= array ('',preg_replace('/<[^>]*>/', '', str_replace('&#039;', '&apos;', str_replace('&amp;#160;', '',htmlspecialchars(strip_tags($descrtag.$descr_temp[1]), ENT_QUOTES)))));
			$table = 1;			
		}
	
		$xml_is = "";
                
		for ($i=1;$i<count($descr);$i++){
			if (count($lan_array)>1){
				if(strpos($lan_array[$i-1], 'xml:') !== false) $xml_is = "";
				else	$xml_is = "xml:";
			}

			if ($table == 0) $xml.= "<dc:description " .$xml_is.preg_replace('/<[^>]*>/', '', $lan_array[$i-1].">").preg_replace('/<[^>]*>/', '', str_replace('&#039;', '&apos;', str_replace('&amp;#160;', '',htmlspecialchars(strip_tags($descrtag.$descr[$i]), ENT_QUOTES))))."</dc:description>\n";
			else $xml.= "<dc:description xml:lang='en'>".$descr[$i]."</dc:description>\n";
		}		
		if( isset($ccxml->file->sha1)) $xml.= "<dc:identifier>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->sha1, ENT_QUOTES))."</dc:identifier>\n";		
		for ($i=0;$i<count($lan_array);$i++){
			$xml.= "<dc:language>".$rest = substr($lan_array[$i], -3, -1)."</dc:language>\n";		
		}		
		if( isset($ccxml->file->uploader)) $xml.= "<dc:publisher>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->uploader, ENT_QUOTES))."</dc:publisher>\n";	
		for ($i=0;$i<count($ccxml->categories->category);$i++){
			$xml.= "<dc:subject>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->categories->category[$i], ENT_QUOTES))."</dc:subject>\n";		
		}	
		$xml.= "<dc:title>".str_replace('&#039;', '&apos;', htmlspecialchars($item['title'], ENT_QUOTES))."</dc:title>\n";		
		$xml.= "<dc:type>IMAGE</dc:type>\n";
		$xml.= "<edm:type>IMAGE</edm:type>\n";
		if( isset($ccxml->file->size)) $xml.= "<dcterms:extent>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->size, ENT_QUOTES))."</dcterms:extent>\n";		
		if( isset($ccxml->file->width)) $xml.= "<dcterms:extent>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->width, ENT_QUOTES))."</dcterms:extent>\n";		
		if( isset($ccxml->file->height)) $xml.= "<dcterms:extent>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->height, ENT_QUOTES)) ."</dcterms:extent>\n";			
		if( isset($ccxml->file->upload_date)) $xml.= "<dcterms:issued>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->upload_date, ENT_QUOTES)) ."</dcterms:issued>\n";			
		$xml.= "</edm:ProvidedCHO>\n";
		$xml.= "<edm:WebResource rdf:about='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'>\n";
		$xml.= "<dc:format>JPG</dc:format>\n";		
		if( isset($ccxml->licenses->license->name)) $xml.= "<dc:rights>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->licenses->license->name, ENT_QUOTES))."</dc:rights>\n";		
		$xml.= "</edm:WebResource>\n";
		$xml.= "<ore:Aggregation rdf:about='http://more.locloud.eu/object/".$provider_name."/".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->sha1, ENT_QUOTES))."#aggregation'>\n";
		$xml.= "<edm:aggregatedCHO rdf:resource='http://more.locloud.eu/object/".$provider_name."/".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->sha1, ENT_QUOTES))."'/>\n";	
		$xml.= "<edm:dataProvider>".$provider_name."</edm:dataProvider>\n";
		$xml.= "<edm:isShownAt rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->description, ENT_QUOTES))."'/>\n";		
		$xml.= "<edm:isShownBy rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'/>\n";
		if( isset($ccxml->file->urls->file)) $xml.= "<edm:object rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'/>\n";		
		$xml.= "<edm:provider>LoCloud</edm:provider>\n";	
		if( $ccxml->licenses->license->name=='CC-BY-SA-4.0') $xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/4.0/'/>\n";								
		$xml.= "</ore:Aggregation>\n";		
		$xmlDoc = $xml_header.$xml_head.$xml.$xml_foot;
	
	
		echo $xmlDoc;
	}
?>
