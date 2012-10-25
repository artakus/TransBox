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
$order =  isset($_REQUEST['order']) ? strtoupper(trim($_REQUEST['rows'])) : "ASC";
$sort =  isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'id';


$array = array('uid'=>$_SESSION['login']['id']);
$w = " WHERE `uid` = :uid ";
if ($_SESSION['login']['level'] == 1) {
	$w = "";
	$array = array();
}

$dummy = array('total'=>0,'rows'=>array());

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
	onOk("",$dummy);
}

$sql = "SELECT `id`,`uid`,`tid`,`name`,`size`,`txed`,`rxed`,`added_date`,`stopped` FROM `torrents` {$w} ORDER BY `{$sort}` {$order} LIMIT {$skip},{$rows}";
if ($_SESSION['login']['level'] == 1) {
	$sql = "SELECT `t`.`id`,`t`.`uid`,`t`.`tid`,`t`.`name`,`t`.`size`,`t`.`txed`,`t`.`rxed`,`t`.`added_date`, `u`.`email`, `t`.`stopped` FROM `torrents` AS `t`, `users` AS `u` WHERE `t`.`uid` = `u`.`id` ORDER BY `{$sort}` {$order} LIMIT {$skip},{$rows}";
}
$sth = $db->prepare($sql);
	if (!$sth) {
		onError("DB error: Invalid SQL",$db->errorInfo(),$sql,$dummy);
}
if (!$sth->execute($array)) {
	onError("DB error: Failed to retrive torrent data",$sth->errorInfo(),$sql,$dummy);
}

$torrents = array();
$torrent_id = array();
while($row = $sth->fetch()) {
	$torrents[] = $row;
	$torrent_id[] = intval($row['tid']);
}

$torrent_id = array_unique($torrent_id);

$t = array();
$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
$fields = array("id","percentDone","status","uploadRatio","rateDownload","rateUpload","isFinished");
$torrent_list = $rpc->get($torrent_id,$fields);
if ($torrent_list->result == "success" && !empty($torrent_list->arguments)) {
	$torrents_rpc = $torrent_list->arguments->torrents;
	foreach ($torrents_rpc as $k=>$trt) {
		$tid = $trt->id;
		$status = isset($trt->status) ? $trt->status : 0;
		$ratio = isset($trt->uploadRatio) ? $trt->uploadRatio: 0;
		$percentage = isset($trt->percentDone) ? $trt->percentDone: 0;
		$up_speed = isset($trt->rateUpload) ? $trt->rateUpload : 0;
		$down_speed = isset($trt->rateDownload) ? $trt->rateDownload : 0;
		$finished = isset($trt->isFinished) ? $trt->isFinished : FALSE;
		$t[(string)$tid] = compact("status","ratio","percentage","up_speed","down_speed","finished");
	}
}
foreach ($torrents as $key => $value) {
	$torrents[$key] = $value + $t[$value['tid']];
}

$rows = array_values($torrents);
onOk("",compact("rows","total"));