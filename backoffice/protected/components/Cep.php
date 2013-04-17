<?php

class Cep extends CApplicationComponent
{
	public $domain;
	public $user;
	public $password;
	
	/*
	 * MISC HELPER FUNCTIONS
	 */
	
	static function trace($msg)
	{
		Yii::trace($msg,'Cep');		
	}
	
    public function DownloadFile($url)	
    {
    	$error = false;
		
    	$tmp = tempnam(sys_get_temp_dir(),'EP');

		$fp = fopen($tmp, 'w');
		$url = $this->domain.$url;
		$ch = curl_init($url);
		self::trace("Download: $url");

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_USERPWD, $this->user.":".$this->password);
		//curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		
		$cres = curl_exec($ch);
		
		if ($cres === false) {
			Yii::log('Curl-Error: ' . curl_error($ch),'error', 'Cep');
			$error = true;
		} else {
			self::trace('Download speed: '.curl_getInfo( $ch, CURLINFO_SPEED_DOWNLOAD ));		
		}
		
		if (curl_getInfo( $ch, CURLINFO_HTTP_CODE ) != 200) {
			Yii::log('Curl-HTTP-Code not ok: ' . curl_getInfo( $ch, CURLINFO_HTTP_CODE ), 'error', 'Cep');
			$error = true;			
		}
		
		curl_close($ch);
		fclose($fp);
		
		// return false in case of curl error
		if ($error) {
			#unlink($tmp);
			return false;
		}
		
		self::trace("Temp-Filename for Download: $tmp");
		return $tmp; // return tmp filename
    }


	/*
	 * IMPORT CATALOG (GROUPS)
	 */
	
	public function ImportCatalog($useCache)
	{
		// Set filename to store download (cached version)
		$cacheFile = Yii::app()->basePath.'/data/EP/catalog.zip';

		if (!$useCache) {
			// Download file
			$file = $this->DownloadFile('/catalog/');
			if (!$file) {
				Yii::log('Download failed', 'error', 'Cep');
				return false;
			}
			
			// move tmp file to storage
			if (!rename($file, $cacheFile)) {
				Yii::log('Download tmp move failed', 'error', 'Cep');
				return false;	
			}
			
		} else {
				Yii::log('Using cached version', 'info', 'Cep');			
		}
		
		// extract file
		$zip = new ZipArchive();
		
		if ($zip->open($cacheFile)) {
			if ($zip->numFiles == 1) {
				// gez first and only file
				$first_file = $zip->statIndex(0);
				$this->trace("Found file in zip: ".$first_file["name"]);
				if ($zip->extractTo(sys_get_temp_dir(), $first_file["name"])) {
					$file = $cacheFile.".xml";
					rename(sys_get_temp_dir()."/".$first_file["name"], $file);
				} else {
					Yii::log('Extract zip failed', 'error', 'Cep');
					return false;	
				}
				
			} else {
				Yii::log('Read zip failed (file count is '.$zip->numFiles.')', 'error', 'Cep');
				return false;	
			}
			
		} else {
			Yii::log('Open zip failed', 'error', 'Cep');
			return false;	
		}
		
		
		$xml = new MyDOMDocument();
		$xml->load($file);
		$d = $xml->toArray();

		$result = array();

		// Main Loop
		foreach ($d['ep']['category']['category'] as $main) {
			#echo $key."\n";
			if (is_array($main)) {
				#print_r($main);
				$md = array(
					'id' => $main['id'],
					'name' => $main['attribute'][0],
				);
				
				$this->trace("ID: ".$md['id']." \tNAME: ".$md['name']);
				
				// Subs?
				if (is_array($main['category'])) {
					// Add main to result
					$md["subs"] = $this->ImportCatalog_parseSub($main['category']);
				}

				$result[] = $md;
				
			}
		}

		unlink($file);
		return $result;
	}

	private function ImportCatalog_parseSub($data, $level=1)
	{
		#print_r($main['category']);
		if (!is_array($data[0])) {
			$data = array($data);
		}
		
		$subs = array();
		
		foreach ($data as $sub) {
			#echo $key."\n";
			if (is_array($sub)) {
				#print_r($sub);
				$sd = array(
					'id' => $sub['id'],
					'name' => $sub['attribute'][0],
				);
				
				$this->trace("ID: ".$sd['id']." \t".str_repeat("\t",$level)."NAME: ".$sd['name']);
				
				// Subs?
				if (isset($sub['category']) && is_array($sub['category'])) {
					// Add main to result
					$sd["subs"] = $this->ImportCatalog_parseSub($sub['category'], $level+1);
				}

				$subs[] = $sd;
			}
		}
		
		return $subs;
		
	}

	/*
	 * IMPORT PRODUCTS
	 */
	
	public function ImportProducts($useCache, $model)
	{
		// Set filename to store download (cached version)
		$cacheFile = Yii::app()->basePath.'/data/EP/products.zip';

		if (!$useCache) {
			// Download file
			$file = $this->DownloadFile('/products/');
			if (!$file) {
				Yii::log('Download failed', 'error', 'Cep');
				return false;
			}
			
			// move tmp file to storage
			if (!rename($file, $cacheFile)) {
				Yii::log('Download tmp move failed', 'error', 'Cep');
				return false;	
			}
			
		} else {
				Yii::log('Using cached version', 'info', 'Cep');			
		}
		
		// extract file
		$zip = new ZipArchive();
		
		if ($zip->open($cacheFile)) {
			if ($zip->numFiles == 1) {
				// gez first and only file
				$first_file = $zip->statIndex(0);
				$this->trace("Found file in zip: ".$first_file["name"]);
				if ($zip->extractTo(sys_get_temp_dir(), $first_file["name"])) {
					$file = $cacheFile.".xml";
					rename(sys_get_temp_dir()."/".$first_file["name"], $file);
				} else {
					Yii::log('Extract zip failed', 'error', 'Cep');
					return false;	
				}
				
			} else {
				Yii::log('Read zip failed (file count is '.$zip->numFiles.')', 'error', 'Cep');
				return false;	
			}
			
		} else {
			Yii::log('Open zip failed', 'error', 'Cep');
			return false;	
		}
		
		// prepare variables
		$units = array();
		
		
		// start parsing
		$xml = new XMLReader();
		$xml->open($file);
		while($xml->read())
		{
			if ( ($xml->nodeType == XMLReader::ELEMENT) && ($xml->name == "category") && ($xml->getAttribute('name') == "units") )
			{
				$t = $xml->readInnerXML();
				$units = $this->ImportProducts_parseUnits($t);
			}
			if ( ($xml->nodeType == XMLReader::ELEMENT) && ($xml->name == "product") )
			{
				$t = $xml->readInnerXML();
				$pd = $this->ImportProducts_parseProduct($t);
				$model->UpdateProduct($pd);
				
				//break;
			}
				
		}

		unlink($file);
		
	}

	public function ImportProducts_parseUnits($xml_str)
	{
		$xml_str = "<base>$xml_str</base>";
		$xml = new MyDOMDocument();
		$xml->loadXML($xml_str);
		$d = $xml->toArray();
		
		$r = array();
		foreach ($d['base']['setting'] as $act) {
			if (isset($act[0]))
				$r[$act['@name']] = $act[0];
		}
		
		return $r;
	}


	public function ImportProducts_parseProduct($xml_str)
	{
		$xml_str = "<base>$xml_str</base>";
		$xml = new MyDOMDocument(); 
		$xml->loadXML($xml_str);
		$d = $xml->toArray();
				
		$d = $d["base"];
		$r = array();
		//print_r($d);
		$r["product-id"] = $d["product-id"];
		$r["modification-date"] = $d["modification-date"];
		$r["category"] = $d["category-links"]["category-link"]["@id"];
		$r["name"] = $d["name"][0];
		
		// Text handling
		$r["txt_short"] = '';
		$r["txt_long"] = '';
		if (isset($d["text"][0])) {
			print_r($d["text"][0]);
			if (is_array($d["text"][0])) {
				foreach ($d["text"] as $act) {
					if ($act["@type"] == "shortDescription")
						if (isset($act[0]))
							$r["txt_short"] = $act[0];
					if ($act["@type"] == "longDescription")
						if (isset($act[0]))
							$r["txt_long"] = $act[0];
				}
			} else {
				$r["txt_short"] = $d["text"][0];
			}
		}
		
		$r["eancode"] = (isset($d["ean"]) ? $d["ean"]: null);
		
		$r["manufacturer"] = (isset($d["distributor"]["manufacturer"]["name"]) ? $d["distributor"]["manufacturer"]["name"] : null);
		$r["manufacturer_product-id"] = (isset($d["distributor"]["manufacturer"]["manufacturer-product-id"]) ? $d["distributor"]["manufacturer"]["manufacturer-product-id"] : null);

		$r["prices"] = (isset($d["distributor"]["prices"]["price"]) ? $d["distributor"]["prices"]["price"] : null);
		$r["tax"] = (isset($d["distributor"]["tax"]["@value"]) ? $d["tax"]["@value"] : null);
		
		$r['RAW'] = $d;
						
		return $r;
	}

	
}

?>