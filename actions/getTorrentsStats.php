<?php
/****************
 * System name: TransBox
 * Module: Get TorrentList
 * Functional overview: This file contains variable that editable to adapt the language. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 20;


$array = array('uid'=>$_SESSION['login']['id']);
$w = " WHERE `uid` = :uid AND `stopped` = 0";
if ($_SESSION['login']['level'] == 1) {
	$w = " WHERE `stopped` = 0";
	$array = array();
}

$skip = ($page - 1) * $rows;

$sql = "SELECT COUNT(*) FROM `torrents` {$w}";
$sth = $db->prepare($sql);
if (!$sth) {
	onError("DB error: Invalid SQL",$db->errorInfo(),$sql,$dummy);
}
if (!$sth->execute($array)) {
	onError("DB error: Failed to retrive torrent data",$sth->errorInfo(),$sql,$dummy);
}
$total = intval($sth->fetchColumn(0));

if ($total == 0) {
	onOk("");
}

$sql = "SELECT `id`,`hash` FROM `torrents` {$w} LIMIT {$skip},{$rows}";
$sth = $db->prepare($sql);
	if (!$sth) {
		onError("DB error: Invalid SQL",$db->errorInfo(),$sql,$dummy);
}
if (!$sth->execute($array)) {
	onError("DB error: Failed to retrive torrent data",$sth->errorInfo(),$sql,$dummy);
}

$t = array();
$torrent_id = array();
while($row = $sth->fetch()) {
	$torrents[] = $row;
	$torrent_id[] = $row['hash'];
}
$torrent_id = array_unique($torrent_id);
$t = array();#
if (!empty($torrent_id)) {
	$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
	$fields = array("hashString","percentDone","status","uploadRatio","rateDownload","rateUpload");
	$torrent_list = $rpc->get($torrent_id,$fields);
	if ($torrent_list->result == "success" && !empty($torrent_list->arguments)) {
		if (isset($torrent_list->arguments->torrents)) {
			$torrents_rpc = $torrent_list->arguments->torrents;
			foreach ($torrents_rpc as $k=>$trt) {
				$hash = $trt->hashString;
				$status = isset($trt->status) ? $trt->status : 0;
				$ratio = isset($trt->uploadRatio) ? $trt->uploadRatio: 0;
				$percentage = isset($trt->percentDone) ? $trt->percentDone: 0;
				$up_speed = isset($trt->rateUpload) ? $trt->rateUpload : 0;
				$down_speed = isset($trt->rateDownload) ? $trt->rateDownload : 0;
				$t[$hash] = compact("status","ratio","percentage","up_speed","down_speed");
			}
		}
		
	}
	foreach ($torrents as $key=>$value) {
		$hash = $value['hash'];
		unset($value['hash']);
		$torrents[$key] = $value + $t[$hash];
	}
	$torrents = array_values($torrents);	
}
onOk("",compact("torrents"));