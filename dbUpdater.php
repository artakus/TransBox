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
$max_running = isset($cfg['max_running']) ? intval($cfg['max_running']) : 0;
if (empty($max_running))
	$max_running = 4;

if (empty($cfg['download_path']) || !file_exists($cfg['download_path'])) {
	die("Download path not exists");
}

$sql = "SELECT * FROM `users`";
$sth = $db->query($sql);
$sth_u = $db->prepare("UPDATE `users` SET `ds_current` = :ds, `rx_current` = :rx, `tx_current` = :tx WHERE `id` = :uid");

$sql_t = "SELECT `id`,`hash`,`rxed`,`txed`,`percent`,`stopped` FROM `torrents` WHERE `uid` = :uid AND `stopped` = 0";
$sth_t = $db->prepare($sql_t);
if (!$sth_t) {
	die(var_export($db->errorInfo(),true));
}

$sql_x = "UPDATE `torrents` SET `rxed`= :rxed, `txed` = :txed, `percent` = :percent WHERE `id` = :id ";
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
	$ds = get_dir_size($cfg['download_path']."/".$r['id']);
	
	if (!$sth_t->execute(compact("uid"))) {
		die(var_export($sth_t->errorInfo(),true));
	}
	
	$torrents = array();
	$torrent_id = array();
	$all_torrents_id = array();
	while($row = $sth_t->fetch()) {
		$all_torrents_id[] = $row['id'];
		$torrents[$row['hash']] = $row;
		$torrent_id[] = $row['hash'];
	}
	if (!empty($torrents)) {
	$torrent_id = array_unique($torrent_id);
	$running_torrent = array();
	if (!empty($torrent_id)) {
		$fields = array("hashString","uploadedEver","downloadedEver","percentDone","status");
		$torrent_list = $rpc->get($torrent_id,$fields);
		if ($torrent_list->result == "success" && !empty($torrent_list->arguments)) {
			$arg = get_object_vars($torrent_list->arguments);
			if (!empty($arg)) {
			$torrents_rpc = $torrent_list->arguments->torrents;
			foreach ($torrents_rpc as $k=>$trt) {
				$hash = $trt->hashString;
				$id = $torrents[$hash]['id'];
				$status = isset($trt->status) ? intval($trt->status) : 0; 
				if ($status > 0 && $status < 8 && $torrents[$hash]['stopped'] == 0) {
					$running_torrent[] = $id;
				}
				$c_rxed = $torrents[$hash]['rxed'];
				$c_txed = $torrents[$hash]['txed'];
				$rxed = isset($trt->downloadedEver) ? $trt->downloadedEver : 0;
				$txed = isset($trt->uploadedEver) ? $trt->uploadedEver: 0;
				$percent = isset($trt->percentDone) ? $trt->percentDone: 0;
				$rx += ($rxed - $c_rxed > 0) ? ($rxed - $c_rxed) : 0;
				$tx += ($txed - $c_txed > 0) ? ($txed - $c_txed) : 0;
				if (!$sth_x->execute(compact("id","rxed","txed","percent"))) {
					die(var_export($sth_x->errorInfo(),true));
				}
			}
			}
		}	
	}
	$need_to_stop = array();
	if (count($running_torrent) > $max_running) {
		$need_to_stop = array_slice($running_torrent, $max_running);
	}
	
	if (($ds >= $r['ds_limit'] && $r['ds_limit'] > 0) || (($rx+$tx) >= $r['xfer_limit'] && $r['xfer_limit'] > 0)) {
		$need_to_stop = $all_torrents_id;
	}
	
	if (!empty($need_to_stop)) {
		$id = $need_to_stop;
		print_r($id);
		$param = array();
		$sql_st = "SELECT COUNT(`id`) AS `count`, `hash` FROM `torrents` WHERE `id` IN (".implode(",", $id).") AND `stopped` = 0 GROUP BY `hash`";
		$sth_st = $db->query($sql_st);
		if (!$sth_st) {
			die("DB Error: Failed to get torrent info");
		}
		$tobestopped = array();
		while ($rr= $sth_st->fetch()) {
			if ($rr['count'] == 1) {
				$tobestopped[] = $rr['hash'];
			}
		}
		$result = TRUE;
		if (!empty($tobestopped)) {
			$torrents_rpc = $rpc->stop($tobestopped);
			$result = $torrents_rpc->result == "success";
		}
		if ($result) {
			$sql_up = "UPDATE `torrents` SET `stopped` = 1 WHERE `id` IN (".implode(",", $id).")";
			if (!$db->query($sql_up)) {
				die("DB Error: Failed to update torrent status");
			}
		}
	}
	}
	$bind = compact("uid","ds","rx","tx");
        if (!$sth_u->execute($bind)) {
                die(var_export($sth_u->errorInfo(),true));
        }
        print_r($bind);

}

$sql = "SELECT 
			`t`.`path` AS `pathfrom`, 
			`tt`.`path` AS `pathto`, 
			`tt`.`id` AS `id` 
		FROM 
			`torrents` AS `t`, 
			`torrents` AS `tt` 
		WHERE 
			`tt`.`duplicate` = 1 AND 
			`tt`.`hash` = `t`.`hash` AND 
			`t`.`duplicate` = 0 AND 
			`t`.`percent` >= 1";
$sql_u = "UPDATE `torrents` SET `duplicate` = :dup WHERE `id` = :id";
$sth = $db->query($sql) or die(var_export($db->errorInfo(),true));
$sth_u = $db->prepare($sql_u) or die(var_export($db->errorInfo(),true));
while($r = $sth->fetch()) {
	if (file_exists($r['pathfrom'])) {
		$dup = 2;
		if (!$sth_u->execute(compact("id","dup"))) {
			die(var_export($sth_u->errorInfo(),true));
		}
		if(@copy($r['pathfrom'], $r['pathto'])) {
			$id = $r['id'];
			$dup = 3;
			if (!$sth_u->execute(compact("id","dup"))) {
				die(var_export($sth_u->errorInfo(),true));
			}
		}
	}
}


function get_dir_size($dir_name){
	if (!file_exists($dir_name))
		return 0;
	$ite=new RecursiveDirectoryIterator($dir_name);
	$bytestotal=0;
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
	    $bytestotal+=$cur->getSize();
	}
return $bytestotal;
}
?>
