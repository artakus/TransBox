<?php
define("TRANSBOX",true); //application name here
require_once 'config.php';
if ($debug) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
}
$dbUser = $dbPass = null;
try {
	// db will be used globally, so will be defined once.
	$dsn = $dbType.":".($dbType=="sqlite" ? "" : "host=").$dbHost.($dbType=="sqlite" ? "" : ";dbname=".$dbName);
	var_dump($dsn);
	$db = new PDO($dsn,$dbUser, $dbPass,array(PDO::ATTR_PERSISTENT => $dbPersist,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //change here
} catch(PDOException $e) {
	//$re = $db->errorInfo();
	//var_dump($re);
	die($e->getMessage());
}
