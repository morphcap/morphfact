<?php

class Csonepar extends CApplicationComponent
{
	/*
	 * MISC HELPER FUNCTIONS
	 */
	
	static function trace($msg)
	{
		Yii::trace($msg,'Csonepar');		
	}
	

	/*
	 * IMPORT CATALOG (GROUPS)
	 */
	
	public function ImportCatalog()
	{
		// Set filename to store download (cached version)
		$file = Yii::app()->basePath.'/data/sonepar/DATANORM.WRG';

		$result = array();
		
		// get data from file
		$data = file($file);

		// Main Loop
		foreach ($data as $act) {
			$d = split(";",utf8_encode($act));
			
			if (isset($d[0]) && $d[0] == "S") {
				
				$md = array(
					'id' => $d[2],
					'name' => $d[3],
				);
				
				$this->trace("ID: ".$md['id']." \tNAME: ".$md['name']);


				$result[] = $md;
			}
			
		}
		
		print_r($result);exit;

		return $result;
	}
	
	/*
	 * IMPORT PRODUCTS
	 */
	
	public function ImportProducts($model)
	{
		$result = array();
		$r = array();
	
		// read products
		
		// Set filename to store download (cached version)
		$file = Yii::app()->basePath.'/data/sonepar/DATANORM.001';
		// get data from file
		$data = file($file);
		
		foreach ($data as $act) {
			$d = split(";",utf8_encode($act));
			
			if (isset($d[0]) && (($d[0] == "A") OR ($d[0] == "B"))) {

				$artnr = $d[2];
				
				if (!isset($r[$artnr]))
					$r[$artnr] = array();

				$r[$artnr][$d[0]] = $d;
			}
		}
		
		// read prices
		
		// Set filename to store download (cached version)
		$file = Yii::app()->basePath.'/data/sonepar/DATPREIS.001';
		// get data from file
		$data = file($file);
		
		foreach ($data as $act) {
			$d = split(";",utf8_encode($act));
			
			if (isset($d[0]) && ($d[0] == "P")) {

				$artnr = $d[2];
				
				if (!isset($r[$artnr]))
					$r[$artnr] = array();

				$r[$artnr][$d[0]] = $d;
			}
		}
		
		// parse raw data and prepare result
		//foreach ($r as $key=>$value)
		//	$result[] = $this->ImportProducts_parseProduct($key, $value);
		
		return $r;
	}


	public function ImportProducts_parseProduct($key, $value)
	{				
		$r = array();
		//print_r($d);
		$r["product-id"] = $key;
		print_r($value);
		$text1 = $value['A'][4];
		$text2 = $value['A'][5];
		$price_brutto = $value['A'][9];
		$price_netto = $value['P'][4];
		$wgr = $value['A'][11];
		$r["eancode"] = $value['B'][9];
		
		/*
		$r["modification-date"] = $d["modification-date"];
		$r["category"] = $d["category-links"]["category-link"]["@id"];
		$r["name"] = $d["name"][0];
		
		// Text handling
		$r["txt_short"] = '';
		$r["txt_long"] = '';
		$r["manufacturer"] = (isset($d["distributor"]["manufacturer"]["name"]) ? $d["distributor"]["manufacturer"]["name"] : null);
		$r["manufacturer_product-id"] = (isset($d["distributor"]["manufacturer"]["manufacturer-product-id"]) ? $d["distributor"]["manufacturer"]["manufacturer-product-id"] : null);

		$r["prices"] = (isset($d["distributor"]["prices"]["price"]) ? $d["distributor"]["prices"]["price"] : null);
		$r["tax"] = (isset($d["distributor"]["tax"]["@value"]) ? $d["tax"]["@value"] : null);
		*/
		//$r['RAW'] = $value;
						
		return $r;
	}

	
}

?>