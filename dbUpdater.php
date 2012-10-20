<?php
define("TRANSBOX",true); //application name here
if ($debug) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
}

include_once 'config.php';
try {
	// db will be used globally, so will be defined once.
	$db = new PDO($dbType.":".($dbType=="sqlite" ? "" : "host=").$dbHost.($dbType=="sqlite" ? "" : ";dbname=".$dbName), $dbUser, $dbPass,array(PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //change here
} catch(PDOException $e) {
	die($e->getMessage());
}
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

$sql = "SELECT * FROM `config`";
$sth = $db->query($sql) or onError();
$cfg = $sth->fetchAll(PDO::FETCH_KEY_PAIR);

$sql = "SELECT * FROM `users`";
$sth = $db->query($sql);
$sth_u = $db->prepare("UPDATE `users` SET `ds_current` = :ds, `rx_current` = :rx, `tx_current` = :tx WHERE `id` = :id");
while($r = $sth->fetch()) {
	$id = $r['id'];
	$ds = get_dir_size($cfg['download_path']."/".md5($r['email']));
	var_dump($ds);
	$rx = 0;
	$tx = 0;
	if (!$sth_u->execute(compact("id","ds","rx","tx"))) {
		var_dump($sth_u->errorInfo());
	}
} 


function get_dir_size($dir_name){
	$ite=new RecursiveDirectoryIterator($dir_name);
	$bytestotal=0;
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
	    $bytestotal+=$cur->getSize();
	}
return $bytestotal;
}
?>