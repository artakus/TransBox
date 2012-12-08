<?php
define("TRANSBOX",true); //application name here
include_once 'config.php';
if ($debug) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
}

try {
	// db will be used globally, so will be defined once.
	$db = new PDO($dbType.":".($dbType=="sqlite" ? "" : "host=").$dbHost.($dbType=="sqlite" ? "" : ";dbname=".$dbName), $dbUser, $dbPass,array(PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //change here
} catch(PDOException $e) {
	die($e->getMessage());
}
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

$sql = "UPDATE `users` SET `rx_current` = 0, `tx_current` = 0";
$sth = $db->query($sql) or die(var_export($db->errorInfo(),true));
