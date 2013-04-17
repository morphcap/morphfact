<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;

$connection=Yii::app()->db;

#$command=$connection->createCommand("SET NAMES UTF8");
#$dbr = $command->query();

$sql = "SELECT * FROM artikel";
$command=$connection->createCommand($sql);
$dbr = $command->query();
$rows = $dbr->readAll();

$t = $rows[0]['arttext'];

#$t = mb_convert_encoding($t, 'utf8', 'Windows-1252');

echo $t;

#print_r(mb_list_encodings());

?>