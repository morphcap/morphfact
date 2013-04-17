<?php

// Import Main Config
$main=require('main.php');

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'morphFact Util',

	// preloading 'log' component
	'preload'=>array('log','ep'),

	// autoloading model and component classes
	'import'=>$main['import'],
	
	'behaviors'=>$main['behaviors'],
	
	// application components
	'components'=>array(

		'db'=>$main['components']['db'],
		'edms'=>$main['components']['edms'],
		
		'ep'=>$main['components']['ep'],
		'sonepar'=>$main['components']['sonepar'],
		'gdi'=>$main['components']['gdi'],

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, info',
				),
				array(
					'class'=>'EDMSLogRoute',
					'levels'=>'error, warning, info, trace',
				),
				array(
					'class'=>'CStdOutRoute',
					'levels'=>'error, warning, info, trace',
				),

			),
		),

	),
);