<?php

class GDICommand extends CConsoleCommand
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
		
		set_time_limit(200);
		ini_set('memory_limit', '1280M');
	}

	public function actionSync($type, $source)
	{
		Yii::log("Starting Sync: type [$type] source [$source]",'info','GDICommand');
		
		switch ($type) {
			case 'groups':
				$result = Yii::app()->gdi->SyncGroups();
			break;

			case 'products':
				switch ($source) {
					case "ep":
						$result = Yii::app()->gdi->SyncProducts($source);
					break;
					
					case "sonepar":
						$result = Yii::app()->gdi->SyncProducts($source);
					break;

					default:
						Yii::log('Unknown source','error','GDICommand');
						return 1;					
					break;
				}
				
			break;
				
			default:
				Yii::log('Unknown type','error','GDICommand');
				return 1;
			break;
		}

		Yii::log('Finishing Sync','info', 'GDICommand');
	}

	public function actionTransfer($type)
	{
		Yii::log("Starting Transfer: type [$type]",'info','GDICommand');
		
		switch ($type) {
			case 'wg2products':
				$result = Yii::app()->gdi->TransferWG2Products();
			break;
				
			default:
				Yii::log('Unknown type','error','GDICommand');
				return 1;
			break;
		}

		Yii::log('Finishing Transfer','info', 'GDICommand');
	}


}