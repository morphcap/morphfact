<?php

class GDI extends CApplicationComponent
{
	
	public $mapping = array(
		// Groups
		'groups' => array(
			'main' => array(
				'table' => 'GDIDEF',
				'fields' => array(
					'id' => 'IND1',
					'name' => 'F1',
				),
				'filter' => array(
					array('SATZART','SO'),
				),
			),
			'sub' => array(
				'table' => 'GDIDEF',
				'fields' => array(
					'id' => 'IND1',
					'name' => 'F1',
				),
				'filter' => array(
					array('SATZART','WG'),
				),
			),
		),
		
		// Products
		'products' => array(
			'table' => 'ARTIKEL',
		),
	);
	
	public function SyncGroups()
	{
		$model = new SupplierImport;
		$data = $model->GetCatalog();
		
		$connection=Yii::app()->db;
		
		#$sql = "SELECT * FROM :table WHERE :filter=:filterdata AND :field=:fielddata";
		#$command=$connection->createCommand($sql);

		foreach ($data as $act) {
			// prefix with "1"
			$act['name'] = substr($act['name'], 0, 50);
			
			if (isset($act['s_id_master']) && $act['s_id_master'] > 0 /*&& $act['s_id_parent'] != $act['s_id_master']*/) {
				// SUB
				$act['s_id'] = 10000 + $act['s_id'];

				$sql = "SELECT * FROM GDIDEF WHERE SATZART='WG' AND IND1='".$act['s_id']."'";
				$command=$connection->createCommand($sql);

								
				$dbr = $command->query();
				$rows = $dbr->readAll();
				if (!$rows) {
					#$sqli = "INSERT INTO :table (:filter, IND1, F1) VALUES (:filterdata, :fielddata, :name)";
					#$command=$connection->createCommand($sqli);
					#$command->bindParam(":name",$act['name'],PDO::PARAM_STR);
					$sqli = "INSERT INTO GDIDEF (SATZART, IND1, F1, K5) VALUES ('WG', '".$act['s_id']."', '".$act['name']."', '".$act['s_id_master']."')";
					
					$command=$connection->createCommand($sqli);
					$command->execute();
					#exit;
				}
			} else {
				if ($act['s_id_parent'] == 0 OR 1==1/*OR $act['s_id_parent'] != $act['s_id_master']*/) {
					// MAIN
					$sql = "SELECT * FROM GDIDEF WHERE SATZART='SO' AND IND1='".$act['s_id']."'";
					$command=$connection->createCommand($sql);
					$dbr = $command->query();
					$rows = $dbr->readAll();
					if (!$rows) {
						$sqli = "INSERT INTO GDIDEF (SATZART, IND1, F1) VALUES ('SO', '".$act['s_id']."', '".$act['name']."')";
						$command=$connection->createCommand($sqli);
						$command->execute();
					}
					
				} else {
					// MIDDLE GROUP => skip
					print_r($act);
				}
			}
			
		}
	}
	

	public function SyncProducts($source)
	{
		$model = new SupplierImport;

		// prepare groups to get sortiment id by group-id
		$sortiments = array();
		$groups = $model->GetCatalog();
		foreach ($groups as $act) {
			$sortiments[$act["s_id"]] = $act["s_id_master"];
		}

		$data = EDMSQuery::instance($model->collection."_products")->findCursor( array('source'=>$source) );
		
		$connection=Yii::app()->db;

		foreach ($data as $act) {
			
				// prepare data
				$artikelnr = 0; //$act['s_id'];
				
				if ($source == "sonepar") {
					$liefnr = 70003;
					$wgr = 30000;
					$sortiment = 1;		
				}

				if ($source == "ep") {
					$liefnr = 70002;
				
					if (isset($act['category'])) {
						$wgr = 10000+$act['category'];
						$sortiment = $sortiments[$act['category']];
					} else {
						$wgr = '';
						$sortiment = '';
					}
				}

				// preset variables
				$arttext = str_replace("'", "''", $act['name']);
				$eancode = (isset($act['eancode']) ? trim($act['eancode']) : '');
				$liefartnr = (isset($act['s_id']) ? $act['s_id'] : '');
				$langtext = (isset($act['txt_short']) ? str_replace("'", "''", $act['txt_short']) : '');
				$herstell = (isset($act['manufacturer']) ? $act['manufacturer'] : '');
				$herartnr = (isset($act['manufacturer_product-id']) ? $act['manufacturer_product-id'] : '');
				
				// state
				$state = false;
				$artnr = false;

				// check if article with supplier mapping exists
				$sql = "SELECT ARTIKELNR FROM ADRART WHERE KZADRTYP='L' AND ADRESSNR='$liefnr' AND BESTNR='$liefartnr'";
				$command=$connection->createCommand($sql);								
				$dbr = $command->query();
				$rows = $dbr->readAll();
				if ($rows) {
					// found artikel mapping
					$state = "mapping-exists";
					$artnr = $rows[0]['artikelnr'];
					if (sizeof($rows) > 1) {
						Yii::log("More than one article mapping found for BESTENR $liefartnr",'error', 'GDICommand');					
					}
				}
				
				// check for dublicate EAN, if mapping doesnt exist
				if (!$state && $eancode != "") {
					$sql = "SELECT ARTIKELNR FROM ARTIKEL WHERE EANCODE='$eancode'";
					$command=$connection->createCommand($sql);								
					$dbr = $command->query();
					$rows = $dbr->readAll();
					if ($rows) {
						// found artikel mapping
						$state = "ean-exists";
						$artnr = $rows[0]['artikelnr'];
						Yii::log("EAN Mapping exists ARTIKEL $artnr - BESTENR $liefartnr - EAN [$eancode]",'info', 'GDICommand');
						if (sizeof($rows) > 1) {
							Yii::log("More than one article with ean found for BESTENR $liefartnr - EAN $eancode",'error', 'GDICommand');					
						}
					}
				}
				
				// get new artikelnr when nothing found
				if (!$state) {
					// find next articlenumber
					$command=$connection->createCommand("Select MAX(cast(ARTIKELNR as integer)) as new From ARTIKEL");
					$dbr = $command->query();
					$rowsc = $dbr->read();
					$artnr = $rowsc['new']+1;
					$state = "new-article";
				}
				
				// Create new article					
				if ($state == "new-article") {
					$sqli = "INSERT INTO ARTIKEL (ARTIKELNR, SORTIMENT, WGR, ARTTEXT, EANCODE, LANGTEXT, HERSTELL, HERARTNR) VALUES ('$artnr', '$sortiment', '$wgr', '$arttext', '$eancode', '$langtext', '$herstell', '$herartnr')";
					$command=$connection->createCommand($sqli);
					try {
						$command->execute();
					} catch (Exception $e) {
						$state = "new-article-failed";
						Yii::log("Exception while inserting new article BESTENR $liefartnr: ".$e->getMessage(),'error', 'GDICommand');
					}
				}
				
				// Link article with suppiert - when not already linked
				if ($state == "new-article" || $state == "ean-exists") {
					//delete old mappings first (if other BESTNR)
					$sqli = "DELETE FROM ADRART WHERE KZADRTYP='L' AND ADRESSNR='$liefnr' AND ARTIKELNR='$artnr'";
					$command=$connection->createCommand($sqli);
					$command->execute();
										
					$sqli = "INSERT INTO ADRART (KZADRTYP, ADRESSNR, ARTIKELNR, ISOWAEHR, BESTNR) VALUES ('L', '$liefnr', '$artnr', 'EUR', '$liefartnr')";
					$command=$connection->createCommand($sqli);
					$command->execute();						
				}
					
				
				// Update Prices
				
				// EK = ZVK0
				$prices = array();
				if (isset($act['data']['prices']) && is_array($act['data']['prices'])) {
					foreach ($act['data']['prices'] as $p) {
						if (isset($p['@type']) && isset($p['@value']))
							$prices[$p['@type']] = $p['@value'];
					}
				}

				if ($artnr > 0 && isset($prices['ZVK0'])) {
					$sqli = "DELETE FROM PREISE WHERE ART='I' AND ADRESSNR='$liefnr' AND ARTIKELNR='$artnr'";
					$command=$connection->createCommand($sqli);
					$command->execute();
					// create new
					$sqli = "INSERT INTO PREISE (ART, POSNR, ADRESSNR, ARTIKELNR, MATERIAL) VALUES ('I', 1, '$liefnr', '$artnr', '".$prices['ZVK0']."')";
					$command=$connection->createCommand($sqli);
					$command->execute();
				}

				// VK = ZEVP
				$plist = 1;
				if ($artnr > 0 && isset($prices['ZEVP'])) {
					$sqli = "DELETE FROM PREISE WHERE ART='I' AND ADRESSNR='0' AND ARTIKELNR='$artnr' AND PREISLST='$plist'";
					$command=$connection->createCommand($sqli);
					$command->execute();
					// create new
					$sqli = "INSERT INTO PREISE (ART, POSNR, PREISLST, ARTIKELNR, MATERIAL) VALUES ('I', 1, '$plist', '$artnr', '".$prices['ZEVP']."')";
					$command=$connection->createCommand($sqli);
					$command->execute();
				}
		}
	}

	public function TransferWG2Products()
	{
		// IND1 = WG-Nummer
		// W2 = Umsatzgruppe = UMSATZGR
		// F2 = Name
		// B1 = Lagerartikel = KZLAGER
		// B2 = rabattfähig = KZRABATT
		// B3 = skontofähig = KZSKONTO
		// B4 = provisionsfähig = KZPROV
		// B5 = bonusfähig = KZBONUS
		// B6 = Adress/Artikel (?)
		// K1 = Steuer EK = STEUEREK
		// K2 = Steuer VK = STEUERVK
		// K5 = Sortiment
		// 
		$connection=Yii::app()->db;

		$sql = "SELECT * FROM GDIDEF WHERE SATZART='WG'";
		$command=$connection->createCommand($sql);

		$dbr = $command->query();
		$rows = $dbr->readAll();
		if ($rows) {
			foreach ($rows as $act) {
				if ($act['w2'] > 0) {
					$sqli = "UPDATE ARTIKEL SET UMSATZGR='".$act['w2']."'";
					//,KZSKNOTO='".$act['b3']."', KZPROV='".$act['b4']."', KZBONUS='".$act['b5']."', STEUEREK='".$act['k1']."', STEUERVK='".$act['k2']."

					$act['b1'] = trim($act['b1']);
					if ($act['b1'] == "0" OR $act['b1'] == "1")
						$sqli .= ", KZLAGER='".$act['b1']."'";

					$act['b2'] = trim($act['b2']);
					if ($act['b2'] == "0" OR $act['b2'] == "1")
						$sqli .= ", KZRABATT='".$act['b1']."'";

					$act['b3'] = trim($act['b3']);
					if ($act['b3'] == "0" OR $act['b3'] == "1")
						$sqli .= ", KZSKONTO='".$act['b3']."'";

					$act['b4'] = trim($act['b4']);
					if ($act['b4'] == "0" OR $act['b4'] == "1")
						$sqli .= ", KZPROV='".$act['b4']."'";

					$act['b5'] = trim($act['b5']);
					if ($act['b5'] == "0" OR $act['b5'] == "1")
						$sqli .= ", KZBONUS='".$act['b5']."'";

					if ($act['k1'] > 0)
						$sqli .= ", STEUEREK='".$act['k1']."'";

					if ($act['k2'] > 0)
						$sqli .= ", STEUERVK='".$act['k2']."'";

					$sqli .= " WHERE WGR='".$act['ind1']."'";
						
					$command=$connection->createCommand($sqli);
					$command->execute();
				}
			}
		}
	}
	
}

?>