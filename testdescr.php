<?php
	$agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	
		$cc = curl_init();
		//****div
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:Troyes%20PM%2082793%20F.jpg";
		//***div+table
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:Vilanova%20de%20Meià-PM%2016137.jpg";
		//***table
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:Campillo,_Iglesia_de_San_Pedro_de_la_Nave-PM_17870.jpg";
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:Braga,%20Bom%20Jesus%20do%20Monte%20PM%2033990.jpg";
		//****no div, no table
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:So_Domingo_de_Silos_PM_73918_E.jpg";
		//*****one lang
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:La_Seu_d'Urgell,_Seu-PM_67570.jpg";
		//$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:Vilanova%20de%20Meià-PM%2016140.jpg";
		
		//subjects
		$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=PolMay&harvest_id=2&item_id=File:Madrid%2C_Plaza_Mayor-PM_52933.jpg";
		
				
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
		//if( isset($ccxml->file->author)) $xml.= "<dc:creator>".str_replace('&#039;', '&apos;', htmlspecialchars(strip_tags($ccxml->file->author), ENT_QUOTES))."</dc:creator>\n";
		$xml.= "<dc:creator>PMRMaeyaert</dc:creator>\n";		
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
			$dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$descrtag.$descr_temp[1]);
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
			else if (count($lan_array)==1) $xml_is = "xml:";
			
			if ($table == 0) $xml.= "<dc:description " .$xml_is.preg_replace('/<[^>]*>/', '', $lan_array[$i-1].">").preg_replace('/<[^>]*>/', '', str_replace('&#039;', '&apos;', str_replace('&amp;#160;', '',htmlspecialchars(strip_tags($descrtag.$descr[$i]), ENT_QUOTES))))."</dc:description>\n";
			else $xml.= '<dc:description xml:lang="en">'.$descr[$i]."</dc:description>\n";
		}		
		if( isset($ccxml->file->sha1)) $xml.= "<dc:identifier>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->sha1, ENT_QUOTES))."</dc:identifier>\n";		
		for ($i=0;$i<count($lan_array);$i++){
			$xml.= "<dc:language>".$rest = substr($lan_array[$i], -3, -1)."</dc:language>\n";		
		}		
		if( isset($ccxml->file->uploader)) $xml.= "<dc:publisher>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->uploader, ENT_QUOTES))."</dc:publisher>\n";	
		for ($i=0;$i<count($ccxml->categories->category);$i++){
			$subject = (string)($ccxml->categories->category[$i]);
			if (strpos($subject ,'Uploaded with UploadWizard')===false && strpos($subject ,'Wiki Loves Monuments')===false && strpos($subject ,'known')===false && strpos($subject ,'lacking')===false) {			
				$xml.= "<dc:subject>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->categories->category[$i], ENT_QUOTES))."</dc:subject>\n";	
			}
		}	
		$xml.= "<dc:subject>monument</dc:subject>\n";	
		$xml.= "<dc:subject>building</dc:subject>\n";	
		$xml.= "<dc:subject>Wiki Loves Monuments</dc:subject>\n";	
		for ($i=0;$i<count($ccxml->categories->category);$i++){
			$spatial = (string)($ccxml->categories->category[$i]);
			if (strpos($spatial ,'Spain')>0) 	$xml.= "<dcterms:spatial>Spain</dcterms:spatial>\n";				
			else if (strpos($spatial ,'Portugal')>0) $xml.= "<dcterms:spatial>Portugal</dcterms:spatial>\n";
			else if (strpos($spatial ,'France')>0) $xml.= "<dcterms:spatial>France</dcterms:spatial>\n";
			else if (strpos($spatial ,'Belgium')>0) $xml.= "<dcterms:spatial>Belgium</dcterms:spatial>\n";
			else if (strpos($spatial ,'Netherlands')>0) $xml.= "<dcterms:spatial>Netherlands</dcterms:spatial>\n";
		}
		$xml.= "<dc:title>".str_replace('&#039;', '&apos;', htmlspecialchars($item['title'], ENT_QUOTES))."</dc:title>\n";		
		$xml.= "<dc:title>".str_replace('&#039;', '&apos;', str_replace('File:', '', str_replace('.jpg', '', htmlspecialchars("File:So Domingo de Silos PM 73862 E.jpg", ENT_QUOTES))))."</dc:title>\n";		
		$xml.= "<dc:type>image</dc:type>\n";
		$xml.= "<dc:type>photo</dc:type>\n";
		$xml.= "<edm:type>IMAGE</edm:type>\n";		
		if( isset($ccxml->file->upload_date)) $xml.= "<dcterms:issued>".str_replace('&#039;', '&apos;', htmlspecialchars(substr($ccxml->file->upload_date, 0, 4), ENT_QUOTES)) ."</dcterms:issued>\n";			
		$xml.= "</edm:ProvidedCHO>\n";
		$xml.= "<edm:WebResource rdf:about='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'>\n";
		$xml.= "<dc:format>JPG</dc:format>\n";		
		if( isset($ccxml->licenses->license->name)) $xml.= "<dc:rights>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->licenses->license->name, ENT_QUOTES))."</dc:rights>\n";	
		if( isset($ccxml->file->size)) $xml.= "<dcterms:extent>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->size, ENT_QUOTES))." B</dcterms:extent>\n";		
		//if( isset($ccxml->file->width)) $xml.= "<dcterms:extent>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->width, ENT_QUOTES))."</dcterms:extent>\n";		
		//if( isset($ccxml->file->height)) $xml.= "<dcterms:extent>".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->height, ENT_QUOTES)) ."</dcterms:extent>\n";			
		$xml.= "</edm:WebResource>\n";
		$xml.= "<ore:Aggregation rdf:about='http://more.locloud.eu/object/".$provider_name."/".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->sha1, ENT_QUOTES))."#aggregation'>\n";
		$xml.= "<edm:aggregatedCHO rdf:resource='http://more.locloud.eu/object/".$provider_name."/".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->sha1, ENT_QUOTES))."'/>\n";	
		$xml.= "<edm:dataProvider>".$provider_name."</edm:dataProvider>\n";
		$xml.= "<edm:isShownAt rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->description, ENT_QUOTES))."'/>\n";		
		$xml.= "<edm:isShownBy rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'/>\n";
		if( isset($ccxml->file->urls->file)) $xml.= "<edm:object rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'/>\n";		
		$xml.= "<edm:provider>LoCloud</edm:provider>\n";	
		//if( $ccxml->licenses->license->name=='CC-BY-SA-4.0') $xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/4.0/'/>\n";		
		if( $ccxml->licenses->license->name=='CC-BY-SA-4.0') $xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/4.0/'/>\n";								
		else if( $ccxml->licenses->license->name=='CC-BY-SA-3.0' || $ccxml->licenses->license->name=='CC-BY-SA-3.0-ES' || $ccxml->licenses->license->name=='CC-BY-SA-3.0-NL') $xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/3.0/'/>\n";								
								
		$xml.= "</ore:Aggregation>\n";		
		$xmlDoc = $xml_header.$xml_head.$xml.$xml_foot;
	
	
	echo $xmlDoc;


?>
