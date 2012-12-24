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

$w = "";
$array = array();
if ($_SESSION['login']['level'] != 1) {
	$w = "WHERE `uid` = :uid";
	$array['uid'] = $_SESSION['login']['id'];
}

$skip = ($page - 1) * $rows;

$sql = "SELECT `id`,`hash`,`stopped` FROM `torrents` {$w} LIMIT {$skip},{$rows}";
$sth = $db->prepare($sql);
	if (!$sth) {
		onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
}
if (!$sth->execute($array)) {
	onError("DB error: Failed to retrive torrent data",$sth->errorInfo(),$sql);
}

$torrents = array();
$torrent_id = array();
while($row = $sth->fetch()) {
	if (intval($row['stopped']) > 0)
		continue;
	$torrents[] = $row;
	$torrent_id[] = $row['hash'];
}
$torrent_id = array_unique($torrent_id);
$t = array();
if (!empty($torrent_id)) {
	$sql = "UPDATE `torrents` SET `stopped` = 1 WHERE `id` = :id";
	$sth = $db->prepare($sql);
        if (!$sth) {
                onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
	}
	$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
	$fields = array("hashString","percentDone","status","uploadRatio","rateDownload","rateUpload");
	$torrent_list = $rpc->get($torrent_id,$fields);
	if ($torrent_list->result == "success" && !empty($torrent_list->arguments)) {
		$arg = get_object_vars ($torrent_list->arguments);
		if (!empty($arg)) {
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
	}
	$a = array("status"=>0,"ratio"=>0,"percentage"=>0,"up_speed"=>0,"down_speed"=>0);
	foreach ($torrents as $key=>$value) {
		$hash = $value['hash'];
		unset($value['hash']);
		if (isset($t[$hash])) {
			$torrents[$key] = array_merge($value,$t[$hash]);
			if ($t[$hash]['status'] == 0){
				if (!$sth->execute(array('id'=>$value['id']))) {
				        onError("DB error: Failed to update torrent data",$sth->errorInfo(),$sql);
				}
			}
		} else {
			$torrents[$key] = array_merge($value,$a);
		}
	}
	$torrents = array_values($torrents);	
}
onOk("",compact("torrents"));
