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
$tid = isset($_REQUEST['tid']) ? json_decode($_REQUEST['tid']) : NULL;
$uid = $_SESSION['login']['id'];
$oper = isset($_REQUEST['oper']) ? strtolower(trim($_REQUEST['oper'])) : "";
$download_path = $_SESSION['cfg']['download_path'];
$save_path = $download_path."/".$_SESSION['login']['id'];

if (empty($id) || empty($tid) || empty($oper)) {
	onError("Error: Insifficient data");
}
$tid = array_unique($tid);
$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
switch ($oper) {
	case 'stop':
		$param = array();
		$sql = "SELECT COUNT(`id`) AS `count`, `tid` FROM `torrents` WHERE `id` IN (".implode(",", $id).") AND `stopped` = 0 GROUP BY `tid`";
		$sth = $db->query($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info");
		}
		$tobestopped = array();
		while ($r= $sth->fetch()) {
			if ($r['count'] == 1) {
				$tobestopped[] = intval($r['tid']);
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
		}
		break;
	case 'start':
		$param = array();
		$torrents_rpc = $rpc->start($tid);
		if ($torrents_rpc->result == "success") {
			$sql = "UPDATE `torrents` SET `stopped` = 0 WHERE `id` IN (".implode(",", $id).")";
			if (!$db->query($sql)) {
				onError("DB Error: Failed to update torrent status");
			}
		}
		break;
	case 'delete':
		$param = array();
		$sql = "SELECT `tid` FROM `torrents` WHERE `duplicate` > 0 AND `tid` IN (".implode(",", $tid).")";
		$sth = $db->query($sql);
		if (!$sth) {
			onError("DB Error: Failed to get torrent info");
		}
		$duplicated = $sth->fetchAll(PDO::FETCH_COLUMN,0);
		$del_id = array_diff($tid, $duplicated);
		$result = TRUE;
		if (!empty($del_id)) {
			$torrents_rpc = $rpc->remove($del_id,TRUE);
			$result = $torrents_rpc->result == "success";	
		}
		if ($result) {
			$sql = "DELETE FROM `torrents` WHERE `tid` IN (".implode(",", $tid).") AND `duplicate` = 0 AND `id` IN (".implode(",", $id).")";
			if (!$db->query($sql)) {
				onError("DB Error: Failed to delete torrent from DB");
			} 
			$sql = "UPDATE `torrents` SET `duplicate` = 0 WHERE `tid` = :tid AND `duplicate` > 0 LIMIT 1";
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB Error: Invalid SQL");
			}
			foreach ($del_id as $tid) {
				if (!@$sth->execute(compact("tid"))) {
					onError("DB Error: Failed to delete torrent from DB");
				}
			}
		}

		break;
	
	default:
		onError("Error: Invalid operation");
		break;
}

onOk();