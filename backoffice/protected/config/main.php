<?php
date_default_timezone_set('Europe/Berlin');

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Elektrowelt Unterhaching morphFact',

	// preloading 'log' component
	'preload'=>array('log', 'bootstrap'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'ext.directmongosuite.components.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'cool',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1','192.168.*'),
		),
	),

	'behaviors' => array(
		'edms' => array(
			'class'=>'EDMSBehavior',
			// 'connectionId' = 'mongodb' //if you work with yiimongodbsuite 
			//see the application component 'EDMSConnection' below
			// 'connectionId' = 'edms' //default;
			//'debug'=>true //for extended logging
		),
	),
	
	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'bootstrap'=>array(
			'class'=>'ext.yii-bootstrap.components.Bootstrap', // assuming you extracted bootstrap under extensions
			'responsiveCss'=>true,
		),
 
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		'db'=>array(
			'connectionString' => 'firebird:dbname=10.211.55.3/23053:C:\\GDI\\GDIBline\\Mandanten\\2_test\\GDI.GDB;charset=UTF8',
			'emulatePrepare' => true,
			'username' => 'sysdba',
			'password' => 'masterkey',
			'charset' => 'utf8',
		),

		//configure the mongodb connection
		//set the values for server and options analog to the constructor 
		//Mongo::__construct from the PHP manual
		'edms' => array(
			'class'            => 'EDMSConnection',
			'dbName'           => 'hoelz',
			'server'           => 'mongodb://localhost:27017' //default
			//'options'  => array(.....); 
        ),

		'ep'=>array(
			'class'=>'Cep',
			'domain'=>'https://de.ep-es.com',
			'user'=>'1198220905',
			'password'=>'nico12345678',
		),

		'sonepar'=>array(
			'class'=>'Csonepar',
		),

		'gdi'=>array(
			'class'=>'GDI',
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				array(
					'class'=>'EDMSLogRoute',
					'levels'=>'error, warning, info, trace',
				),
				// uncomment the following to show log messages on web pages
				array(
					'class'=>'CWebLogRoute',
					'levels'=>'error, warning, info',
				),
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);