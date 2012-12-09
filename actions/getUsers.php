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

if ($_SESSION['login']['level'] > 1) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 20;
$order =  isset($_REQUEST['order']) ? strtoupper(trim($_REQUEST['rows'])) : "ASC";
$sort =  isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'id';

$dummy = array('total'=>0,'rows'=>array());

$skip = ($page - 1) * $rows;

$sql = "SELECT COUNT(*) FROM `users`";
$sth = $db->prepare($sql);
if (!$sth) {
	onError("DB error: Invalid SQL",$db->errorInfo(),$sql,$dummy);
}
if (!$sth->execute()) {
	onError("DB error: Failed to retrive users data",$sth->errorInfo(),$sql,$dummy);
}
$total = intval($sth->fetchColumn(0));

if ($total == 0) {
	onOk("",$dummy);
}

$sql = "SELECT `id`, `email`, `level`, `ds_limit`, `ds_current`, `xfer_limit`, `rx_limit`, `rx_current`, `tx_limit`, `tx_current`, `rx_speed`, `tx_speed`, `ratio`
 		FROM 
 			`users` 
 		ORDER BY `{$sort}` {$order} LIMIT {$skip},{$rows}";
$sth = $db->prepare($sql);
	if (!$sth) {
		onError("DB error: Invalid SQL",$db->errorInfo(),$sql,$dummy);
}
if (!$sth->execute()) {
	onError("DB error: Failed to retrive user data",$sth->errorInfo(),$sql,$dummy);
}

$rows = $sth->fetchAll();
onOk("",compact("rows","total"));