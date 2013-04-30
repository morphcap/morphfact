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
		
		print_r($result);
		exit;

		return $result;
	}
	
	/*
	 * IMPORT PRODUCTS
	 */
	
	public function ImportProducts($model)
	{
		$result = array();
		$r = array();
		
		// Set filename to store download (cached version)
		$file = Yii::app()->basePath.'/data/sonepar/DATANORM.001';
		// get data from file
		$data = file($file);
		
		foreach ($data as $act) {
			$d = split(";",utf8_encode($act));
			
			if (isset($d[0]) && (($d[0] == "A") OR ($d[0] == "B") )) {

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
		foreach ($r as $key=>$value) {
			$d = $this->ImportProducts_parseProduct($key, $value);
	
			// save product
			if ($d)
				$model->UpdateProduct($d);			
		}
		
		return $d;
	}


	public function ImportProducts_parseProduct($artnr, $data)
	{				
		$r = array();
		//print_r($data);
		$r["source"] = "sonepar";
		
		// main data handling
		if (!isset($data['A'])) {
			//print_r($data);
			return false;
		}
		if (!isset($data['B'])) {
			//print_r($data);
			return false;
		}
				
		
		$r["product-id"] = $artnr;
		$r["eancode"] = $data['B'][9];
		$r["category"] =  $data['A'][11];

		// empty
		$r["txt_long"] = '';
		$r["manufacturer"] = '';
		$r["manufacturer_product-id"] = '';
		
		
		// Text handling
		$text1 = $data['A'][4];
		$text2 = $data['A'][5];
		$r["name"] = $text1." ".$text2;
		$r["txt_short"] = $r["name"];
		//$r["txt_long"] = '';
		
		// Price handling
		if (!isset($data['P'])) {
			//print_r($data);
			return false;
		}
		$price_brutto = $data['A'][9];
		$price_netto = $data['P'][4];
		
		$r['prices'] = array(
			array(
				'@currency' => 'EUR',
				'@quantity' => '1',
				'@type' => 'ZEVP',
				'@value' => number_format($price_netto/100,2),
			),
			array(
				'@currency' => 'EUR',
				'@quantity' => '1',
				'@type' => 'ZVK0',
				'@value' => number_format((($price_brutto/100)*1.19),2),
			),
		);
		

		$r['RAW'] = $data;
						
		return $r;
	}

	
}

?>