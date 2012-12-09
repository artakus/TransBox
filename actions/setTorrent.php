<?php
/****************
 * System name: TransBox
 * Module: No operation action
 * Functional overview: This file contains nothing. Useful to maintain/check session 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}

$id = isset($_REQUEST['id']) ? json_decode($_REQUEST['id']) : NULL;
$hash = isset($_REQUEST['hash']) ? json_decode($_REQUEST['hash']) : NULL;
$uid = $_SESSION['login']['id'];
$oper = isset($_REQUEST['oper']) ? strtolower(trim($_REQUEST['oper'])) : "";
$download_path = $_SESSION['cfg']['download_path'];
$save_path = $download_path."/".$_SESSION['login']['id'];

if (empty($id) || empty($hash) || empty($oper)) {
	onError("Error: ".$lang['insufData'],NULL,compact("id","hash","oper"));
}
$hash = array_unique($hash);
$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
switch ($oper) {
	case 'download':
		$sql = "SELECT `path` FROM `torrents` WHERE `id` = :id AND `hash` = :hash LIMIT 1";
		$sth = $db->prepare($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info".$db->errorInfo());
		}
		$id = $id[0];
		$hash = $hash[0];
		if (!$sth->execute(compact("id","hash"))) {
			onError("DB Error: Failed to get torrent info from DB",$sth->errorInfo());
		}
		$path = $sth->fetchColumn(0);
		if (!file_exists($path)) {
			onError("Error: File not exists");
		}
		if (is_dir($path)) {
			onError("Error: Multiple files torrent detected. Please download the file individually from the Files tab");
		}
		
		$url = "";
		if ($_SESSION['cfg']['use_lighttpd_secdownload']) {
			$url = lighttpdSecDownload($path);			
		} elseif($_SESSION['cfg']['use_apache_authtoken']) {
			$url = authTokenDownload($path);
		} elseif($_SESSION['cfg']['use_xsendfile']) {
			$a = array('time'=> time(),'url'=>$path);
			$url = "?action=download&path=".urlencode(encrypt(json_encode($a)));
		} elseif($_SESSION['cfg']['use_symlink']) {
			$md5 = md5($uid.time().$path);
			$dirname = dirname(__FILE__).$_SESSION['cfg']['dl_prefix'].$md5;
			if (!@mkdir($dirname)) {
				onError("Failed to make temporary URL");
			}
			if (!@symlink($path, $dirname."/".basename($path))) {
				onError("Failed to make temporary URL");
			}
			$url = $_SESSION['cfg']['dl_prefix'].$md5."/".basename($path);
			$a = array('time'=> time(),'url'=>$url);
			$url = "?action=download&path=".urlencode(encrypt(json_encode($a)));
		} else {
			$a = array('time'=> time(),'url'=>$path);
			$url = "?action=download&path=".urlencode(encrypt(json_encode($a)));
		}
		onOk("",compact("url"));
		break;
	case 'stop':
		$param = array();
		$sql = "SELECT COUNT(`id`) AS `count`, `hash` FROM `torrents` WHERE `id` IN (".implode(",", $id).") AND `stopped` = 0 GROUP BY `hash`";
		$sth = $db->query($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info");
		}
		$tobestopped = array();
		while ($r= $sth->fetch()) {
			if ($r['count'] == 1) {
				$tobestopped[] = $r['hash'];
			}
		}
		$result = TRUE;
		if (!empty($tobestopped)) {
			$torrents_rpc = $rpc->stop($tobestopped);
			$result = $torrents_rpc->result == "success";
			if (!$result) {
				onError("Transmission error: ".$torrents_rpc->result);
			}
		}
		
		if ($result) {
			$sql = "UPDATE `torrents` SET `stopped` = 1 WHERE `id` IN (".implode(",", $id).")";
			if (!$db->query($sql)) {
				onError("DB Error: Failed to update torrent status");
			}
		}		$param = array();
		$sql = "SELECT COUNT(`id`) AS `count`, `hash` FROM `torrents` WHERE `id` IN (".implode(",", $id).") AND `stopped` = 0 GROUP BY `hash`";
		$sth = $db->query($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info");
		}
		$tobestopped = array();
		while ($r= $sth->fetch()) {
			if ($r['count'] == 1) {
				$tobestopped[] = $r['hash'];
			}
		}
		$result = TRUE;
		if (!empty($tobestopped)) {
			$torrents_rpc = $rpc->stop($tobestopped);
			$result = $torrents_rpc->result == "success";
			/*
			if (!$result) {
				onError("Transmission error: ".$torrents_rpc->result);
			}*/
		}
		
		if ($result) {
			$sql = "UPDATE `torrents` SET `stopped` = 1 WHERE `id` IN (".implode(",", $id).")";
			if (!$db->query($sql)) {
				onError("DB Error: Failed to update torrent status");
			}
		}
		break;
	case 'start':
		$param = array();
		$torrents_rpc = $rpc->start($hash);
		if ($torrents_rpc->result == "success") {
			$sql = "UPDATE `torrents` SET `stopped` = 0 WHERE `id` IN (".implode(",", $id).")";
			if (!$db->query($sql)) {
				onError("DB Error: Failed to update torrent status");
			}
		}
		break;
	case 'delete':
		$param = array();
		$sql = "SELECT DISTINCT `hash` FROM `torrents` WHERE `duplicate` > 0 AND `hash` IN ('".implode("','", $hash)."')";
		$sth = $db->query($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info",$db->errorInfo(),$sql);
		}
		$duplicated = $sth->fetchAll(PDO::FETCH_COLUMN,0);
		$del_id = array_diff($hash, $duplicated);
		$result = TRUE;
		if (!empty($del_id)) {
			$torrents_rpc = $rpc->remove($del_id,TRUE);
			$result = $torrents_rpc->result == "success";	
		}
		$del_file = array_diff($hash, $del_id);
		$w = "AND `uid` = {$uid}";
		if ($_SESSION['login']['level'] == 1) {
			$w = "";
		}
		$sql = "SELECT DISTINCT `path` FROM `torrents` WHERE `id` IN (".implode(",", $id).") AND `hash` IN ('".implode("','", $del_file)."') ".$w;
		$sth = $db->query($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info",$db->errorInfo(),$sql);
		}
		$failed_to_delete = array();
		while ($r = $sth->fetch()) {
			if (@unlink($r['path'])) {
				$failed_to_delete[] = basename($r['path']);
			}
		}
		if ($result) {
			$sql = "SELECT DISTINCT `id`, `path`,`hash` FROM `torrents` WHERE `hash` IN ('".implode(",", $hash)."') AND `duplicate` > 0 GROUP BY `hash` ORDER BY `id`";
			$sth = $db->query($sql);
			if (!$sth) {
				onError("DB Error: Failed to get torrent info",$db->errorInfo(),$sql);
			}
			$needtoupdate = array();
			while($r = $sth->fetch()) {
				$hashes = $r['hash'];
				$torrents_rpc = $rpc->set($hashes,array('location'=>dirname($r['path'])));
				if ($torrents_rpc->result != "success") {
					onError("Failed to delete torrent");
				}
				$needtoupdate[] = $r['id'];
			}
			
			$sql = "DELETE FROM `torrents` WHERE `hash` IN ('".implode("','", $hash)."') AND `duplicate` = 0 AND `id` IN (".implode(",", $id).")";
			if (!$db->query($sql)) {
				onError("DB Error: Failed to delete torrent from DB",$db->errorInfo(),$sql);
			}
			if (!empty($needtoupdate)) {
				$sql = "UPDATE `torrents` SET `duplicate` = 0 WHERE `id` IN (".implode(",", $needtoupdate).") AND `duplicate` > 0";
				$sth = $db->query($sql);
				if (!$sth) {
					onError("DB Error: Invalid SQL",$db->errorInfo());
				}
			}
		}
		$msg = (!empty($failed_to_delete) ? "Failed to delete following files: ".implode("\n", $failed_to_delete) : "");
		onOk($msg);
		break;
	
	default:
		onError("Error: Invalid operation");
		break;
}
onOk();