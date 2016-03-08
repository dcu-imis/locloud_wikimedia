<?php

	$api_key    = $_GET['api_key'];
    $harvest_id     = $_GET['harvest_id'];

	$provider_id = "25";
	$provider_name = "CHB";
	$provider_name_full = "PMR Maeyaert";
	
	/*$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://more.locloud.eu/wikimedia/ListItems.php?api_key=".$api_key."&harvest_id=".$harvest_id);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);*/
	$xml1 = simplexml_load_file("/home/kravvaritis/paulmaeyaert.xml"); //simplexml_load_string($output);

	$agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	
	$xml_header = "<?xml version='1.0' encoding='utf-8'?>";
	//$xml_head = "<oai_dc:dc xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:oai_dc='http://www.openarchives.org/OAI/2.0/oai_dc/' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd'>\n";
	//$xml_foot = " </oai_dc:dc>\n";
	$xml_head = "<rdf:RDF xmlns:oai_dc='http://www.openarchives.org/OAI/2.0/oai_dc/' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:edm='http://www.europeana.eu/schemas/edm/' xmlns:dcterms='http://purl.org/dc/terms/' xmlns:ore='http://www.openarchives.org/ore/terms/' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#' xmlns:wgs84_pos='http://www.w3.org/2003/01/geo/wgs84_pos#' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:skos='http://www.w3.org/2004/02/skos/core#'>\n";
	$xml_foot = " </rdf:RDF>\n";
	
	mkdir("Publication_wiki");
	mkdir("Publication_wiki/Publication_0");
	
	$c=0;	
	foreach ($xml1->xpath('//item') as $item) {	
		$cc = curl_init();
		//$url="http://tools.wmflabs.org/magnus-toolserver/commonsapi.php?image=".str_replace(' ', '_', $item['title']);
		$url="http://more.locloud.eu/wikimedia/GetItem.php?api_key=".$api_key."&harvest_id=".$harvest_id."&item_id=".str_replace(' ', '_', $item['title']);
		curl_setopt($cc, CURLOPT_URL, $url);
		curl_setopt($cc, CURLOPT_HEADER, 0);
		curl_setopt($cc, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cc, CURLOPT_USERAGENT, $agent);
		$ccoutput = curl_exec($cc);
		curl_close($cc);
		$ccxml = simplexml_load_string($ccoutput);
		//echo $ccxml->asXML();	
		
		//DC
		/*$xml = "<dc:title>".$item['title']."</dc:title>\n";
		if( isset($ccxml->file->author)) $xml.= "<dc:creator>".strip_tags($ccxml->file->author)."</dc:creator>\n";
		if( isset($ccxml->file->sha1)) $xml.= "<dc:identifier>".$ccxml->file->sha1."</dc:identifier>\n";
		if( isset($ccxml->description->language)) $xml.= "<dc:description>".strip_tags($ccxml->description->language)."</dc:description>\n";
		if( isset($ccxml->file->uploader)) $xml.= "<dc:publisher>".$ccxml->file->uploader."</dc:publisher>\n";
		if( isset($ccxml->licenses->license->name)) $xml.= "<dc:rights>".$ccxml->licenses->license->name."</dc:rights>\n";			
		if( isset($ccxml->file->date)) $xml.= "<dc:date>".strip_tags($ccxml->file->date)."</dc:date>\n";
		$xml.= "<dc:type>Image</dc:type>\n";
		if( isset($ccxml->file->size)) $xml.= "<dc:format>".$ccxml->file->size."</dc:format>\n";		
		if( isset($ccxml->file->width)) $xml.= "<dc:format>".$ccxml->file->width."</dc:format>\n";		
		if( isset($ccxml->file->height)) $xml.= "<dc:format>".$ccxml->file->height ."</dc:format>\n";		
		if( isset($ccxml->file->urls->description)) $xml.= "<dc:source>".$ccxml->file->urls->description."</dc:source>\n";	
		if( isset($ccxml->file->urls->file)) $xml.= "<dc:source>".$ccxml->file->urls->file."</dc:source>\n";		
		
		$language= $ccxml->description->language;	
		$des_lans = explode(" ", $language);
		for ($i=0;$i<count($des_lans);$i++){
			if (strpos($des_lans[$i],'lang=') !== false) $xml.= "<dc:language>".$rest = substr($des_lans[$i], -3, -1)."</dc:language>\n";		
		}*/
		
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
		
		/*
		$descr= explode("<div", $language);
		if (count($descr)==1){
			$descr= explode("<table", $language);
			$descrtag="<table";
		}
		else $descrtag="<div";		
		*/
		//*******correction 21/4/2015 for description
		$descr= explode("<div", $language);
		$descr_temp= explode("<table", $language);
		//only has table
		if (count($descr)==1 && count($descr_temp)>1){
			$descrtag="<table";
			//remove span elements if table exists
			$dom = new DOMDocument();
			$dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$descrtag.$descr_temp[1]);
			$dom->preserveWhiteSpace = false;
			/*$elements = $dom->getElementsByTagName('span');
			while($span = $elements->item(0)) {       
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
		//************
		$xml_is = "";
                
		for ($i=1;$i<count($descr);$i++){
			if (count($lan_array)>1){
				if(strpos($lan_array[$i-1], 'xml:') !== false) $xml_is = "";
				else	$xml_is = "xml:";
			}
			else if (count($lan_array)==1) $xml_is = "xml:";
			
			if ($table == 0) $xml.= "<dc:description " .$xml_is.preg_replace('/<[^>]*>/', '', $lan_array[$i-1].">").preg_replace('/<[^>]*>/', '', str_replace('&#039;', '&apos;', str_replace('&amp;#160;', '',htmlspecialchars(strip_tags($descrtag.$descr[$i]), ENT_QUOTES))))."</dc:description>\n";
			else $xml.= "<dc:description xml:lang='en'>".$descr[$i]."</dc:description>\n";
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
		$xml.= "<dc:title>".str_replace('&#039;', '&apos;', str_replace('File:', '', str_replace('.jpg', '',  htmlspecialchars($item['title'], ENT_QUOTES))))."</dc:title>\n";		
		$xml.= "<dc:type>image</dc:type>\n";
		$xml.= "<dc:type>photo</dc:type>\n";						
		if( isset($ccxml->file->upload_date)) $xml.= "<dcterms:issued>".str_replace('&#039;', '&apos;', htmlspecialchars(substr($ccxml->file->upload_date, 0, 4), ENT_QUOTES)) ."</dcterms:issued>\n";			
		for ($i=0;$i<count($ccxml->categories->category);$i++){
			$spatial = (string)($ccxml->categories->category[$i]);
			if (strpos($spatial ,'Spain')>0) 	$xml.= "<dcterms:spatial>Spain</dcterms:spatial>\n";				
			else if (strpos($spatial ,'Portugal')>0) $xml.= "<dcterms:spatial>Portugal</dcterms:spatial>\n";
			else if (strpos($spatial ,'France')>0) $xml.= "<dcterms:spatial>France</dcterms:spatial>\n";
			else if (strpos($spatial ,'Belgium')>0) $xml.= "<dcterms:spatial>Belgium</dcterms:spatial>\n";
			else if (strpos($spatial ,'Netherlands')>0) $xml.= "<dcterms:spatial>Netherlands</dcterms:spatial>\n";
		}		
		$xml.= "<edm:type>IMAGE</edm:type>\n";	
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
		$xml.= "<edm:dataProvider>".$provider_name_full."</edm:dataProvider>\n";
		$xml.= "<edm:isShownAt rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->description, ENT_QUOTES))."'/>\n";		
		$xml.= "<edm:isShownBy rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'/>\n";
		if( isset($ccxml->file->urls->file)) $xml.= "<edm:object rdf:resource='".str_replace('&#039;', '&apos;', htmlspecialchars($ccxml->file->urls->file, ENT_QUOTES))."'/>\n";		
		$xml.= "<edm:provider>LoCloud</edm:provider>\n";	
		//Europeana does not support CC Rights 3.0 BUT we will map them (3.0, ES, NL) to 3.0 
		if( $ccxml->licenses->license->name=='CC-BY-SA-4.0') $xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/4.0/'/>\n";								
		else if( $ccxml->licenses->license->name=='CC-BY-SA-3.0' || $ccxml->licenses->license->name=='CC-BY-SA-3.0-ES' || $ccxml->licenses->license->name=='CC-BY-SA-3.0-NL') $xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/3.0/'/>\n";								
		//$xml.= "<edm:rights rdf:resource='http://creativecommons.org/licenses/by-sa/4.0/'/>\n";								
		$xml.= "</ore:Aggregation>\n";		
		$xmlDoc = $xml_header.$xml_head.$xml.$xml_foot;
	
		mkdir("Publication_wiki/Publication_0/item_".$c);
		$my_file = "Publication_wiki/Publication_0/item_".$c."/EDM.xml";
		header("Content-Type:text/xml");
		$handle = fopen($my_file, 'w');
		fwrite($handle, $xmlDoc);
		fclose($handle);
		
		//readfile($my_file); 		
		
		$xmlInfo.="<item id='".$ccxml->file->sha1."' name='".str_replace('&#039;', '&apos;', htmlspecialchars($item['title'], ENT_QUOTES))."' filename='Publication_0/item_".$c."/EDM.xml' />\n";
		
		$c++;
	}
	
	$xmlInfo_head = "<package timestamp='".date("Y-m-d H:i:s")."' size='".$c."' schema_id='EDM' ><project name='locloud' /><provider id='".$provider_id."' name='".$provider_name."' /> \n<items>\n";
	$xmlInfo = $xml_header.$xmlInfo_head.$xmlInfo." </items>\n</package>";
	$my_file_info = "Publication_wiki/Publication_0/info.xml";
	header("Content-Type:text/xml");
	/*$handle = fopen($my_file_info, 'w');
	fwrite($handle, $xmlInfo);
	fclose($handle);
	readfile($my_file_info); */
	
	$a = new PharData('package.tar');
    // ADD FILES TO package.tar FILE
	$a->buildFromDirectory('Publication_wiki');
	
	$handle = fopen($my_file_info, 'w');
	fwrite($handle, $xmlInfo);
	fclose($handle);
	readfile($my_file_info);
	
	$a->addFile('Publication_wiki/Publication_0/info.xml' , 'Publication_0/info.xml' );
	
    // COMPRESS package.tar FILE. COMPRESSED FILE WILL BE package.tar.gz
    $a->compress(Phar::GZ);
    // NOTE THAT BOTH FILES WILL EXISTS. SO IF YOU WANT YOU CAN UNLINK package.tar
    unlink('package.tar');

	$prefix = "../ingest/WIKIMEDIA/";	
	$dir="Publication_".date("Y-m-d H:i:s");
	mkdir($prefix.$dir);
	$dir_copy = $prefix.$dir."/package.tar.gz";
	copy('package.tar.gz', $dir_copy);
	unlink('package.tar.gz');

?>
