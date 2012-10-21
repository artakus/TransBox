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

$sql = "SELECT * FROM `config`";
$sth = $db->query($sql) or die(var_export($db->errorInfo(),true));
$cfg = $sth->fetchAll(PDO::FETCH_KEY_PAIR);

$sql = "SELECT * FROM `users`";
$sth = $db->query($sql);
$sth_u = $db->prepare("UPDATE `users` SET `ds_current` = :ds, `rx_current` = :rx, `tx_current` = :tx WHERE `id` = :uid");

$sql_t = "SELECT * FROM `torrents` WHERE `uid` = :uid ";
$sth_t = $db->prepare($sql_t);
if (!$sth_t) {
	die(var_export($db->errorInfo(),true));
}

$sql_x = "UPDATE `torrents` SET `rxed`= :rxed, `txed` = :txed WHERE `id` = :id ";
$sth_x = $db->prepare($sql_x);
if (!$sth_x) {
	die(var_export($db->errorInfo(),true));
}
require_once 'lib/TransmissionRPC.php';
$rpc = new TransmissionRPC($cfg['transmission_url'],$cfg['transmission_username'],$cfg['transmission_password']);
while($r = $sth->fetch()) {
	$uid = $r['id'];
	$rx = $r['rx_current'];
	$tx = $r['tx_current'];
	$ds = get_dir_size($cfg['download_path']."/".md5($r['email']));
	
	if (!$sth_t->execute(compact("uid"))) {
		die(var_export($sth_t->errorInfo(),true));
	}
	
	$torrents = array();
	$torrent_id = array();
	while($row = $sth_t->fetch()) {
		$torrents["_".$row['tid']] = $row;
		$torrent_id[] = intval($row['tid']);
	}
	$fields = array("id","uploadedEver","downloadedEver");
	$torrent_list = $rpc->get($torrent_id,$fields);
	if ($torrent_list->result == "success" && !empty($torrent_list->arguments)) {
		$torrents_rpc = $torrent_list->arguments->torrents;
		foreach ($torrents_rpc as $k=>$trt) {
			var_dump($trt);
			$tid = $trt->id;
			$c_rxed = $torrents["_".$tid]['rxed'];
			$c_txed = $torrents["_".$tid]['txed'];
			$rxed = isset($trt->downloadedEver) ? $trt->downloadedEver : 0;
			$txed = isset($trt->uploadedEver) ? $trt->uploadedEver: 0;
			$rx += ($rxed - $c_rxed > 0) ? ($rxed - $c_rxed) : 0;
			$tx += ($txed - $c_txed > 0) ? ($txed - $c_txed) : 0;
			$id = $torrents["_".$tid]['id'];
			if (!$sth_x->execute(compact("id","rxed","txed"))) {
				die(var_export($sth_x->errorInfo(),true));
			}
		}
	}
	$bind = compact("uid","ds","rx","tx");
	var_dump($bind);
	if (!$sth_u->execute($bind)) {
		die(var_export($sth_u->errorInfo(),true));
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