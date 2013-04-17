<?php

class SoneparCommand extends CConsoleCommand
{
	public $verbose=false;

	public function init()
	{
		// automatically send every new message to available log routes
		Yii::getLogger()->autoFlush = 1;
		// when sending a message to log routes, also notify them to dump the message
		// into the corresponding persistent storage (e.g. DB, email)
		Yii::getLogger()->autoDump = true;
		parent::init();
		
		set_time_limit(2000);
		ini_set('memory_limit', '1280M');
	}

	public function actionImport($type)
	{
		Yii::log("Starting Import Type [$type]",'info','SoneparCommand');
		
		switch ($type) {
			case 'catalog':
				$data = Yii::app()->sonepar->ImportCatalog();
				if ($data) {
					$model = new SupplierImport;
					//$model->UpdateCatalog($data);
				}
			break;

			case 'products':
				$model = new SupplierImport;
				$data = Yii::app()->sonepar->ImportProducts($model);
				print_r($data);
			break;
				
			default:
				Yii::log('Unknown type','error','SoneparCommand');
				return 1;
			break;
		}

		Yii::log('Finishing Import','info', 'SoneparCommand');
	}


}