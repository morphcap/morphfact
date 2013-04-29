<?php

class SupplierImport extends CApplicationComponent
{
	public $collection = "si";
	
	public function UpdateCatalog($data)
	{
		foreach ($data as $main) {
			$criteria = array('s_id'=>(int)$main['id']);
			$r = EDMSQuery::instance($this->collection."_groups")->findOne($criteria);
			if (!$r) {
				// Insert
				$d = array(
					's_id' => (int)$main['id'],
					's_id_parent' => 0,
					's_id_master' => 0,
					'name' => (string)$main['name'],
					'source' => (string)$main['source'],
				);
				
				EDMSQuery::instance($this->collection."_groups")->insert($d);
			}

			if (isset($main['subs']))
				$this->UpdateCatalog_Sub($main['subs'], $main['id']);
		}
 
	}

	private function UpdateCatalog_Sub($data, $parent_id, $master_id=0)
	{
		
		if ($master_id == 0)
			$master_id = $parent_id;
		
		foreach($data as $sub) {
			$criteria = array('s_id'=>(int)$sub['id']);
			$r = EDMSQuery::instance($this->collection."_groups")->findOne($criteria);
			if (!$r) {
				// Insert
				$d = array(
					's_id' => (int)$sub['id'],
					's_id_parent' => (int)$parent_id,
					's_id_master' => (int)$master_id,
					'name' => (string)$sub['name'],
					'source' => (string)$sub['source'],
				);
				
				EDMSQuery::instance($this->collection."_groups")->insert($d);

				if (isset($sub['subs']))
					$this->UpdateCatalog_Sub($sub['subs'], $sub['id'], $master_id);

			}
			
		}
	}
	
	public function GetCatalog()
	{
		$data = EDMSQuery::instance($this->collection."_groups")->findArray();
		return $data;
	}
	
	public function UpdateProduct($data)
	{
		//print_r($data);
		//exit;
		$criteria = array('s_id'=>(int)$data['product-id']);
		$r = EDMSQuery::instance($this->collection."_products")->findOne($criteria);
		if (!$r) {
			// Insert
			$d = array(
				's_id' => (int)$data['product-id'],
				'source' => (string)$data['source'],
				'name' => (string)$data['name'],
				'category' => (int)$data['category'],
				'txt_short' => (string)$data['txt_short'],
				'txt_long' => (string)$data['txt_long'],
				'eancode' => (string)$data['eancode'],
				'manufacturer' => (string)$data['manufacturer'],
				'manufacturer_product-id' => (string)$data['manufacturer_product-id'],
				'data' => $data,
			);
			
			EDMSQuery::instance($this->collection."_products")->insert($d);
		}
 
	}

	public function GetProducts()
	{
		$data = EDMSQuery::instance($this->collection."_products")->findArray();
		return $data;
	}

}

?>