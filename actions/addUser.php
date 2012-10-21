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
if ($_SESSION['login']['level'] > 1) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "";
$password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "";
$ds_limit = isset($_REQUEST['ds_limit']) ? intval($_REQUEST['ds_limit'])*(1024*1024) : 0;
$xfer_limit = isset($_REQUEST['xfer_limit']) ? intval($_REQUEST['xfer_limit'])*(1024*1024) : 0;
$rx_limit = isset($_REQUEST['rx_limit']) ? intval($_REQUEST['rx_limit'])*(1024*1024) : 0;
$tx_limit = isset($_REQUEST['tx_limit']) ? intval($_REQUEST['tx_limit'])*(1024*1024) : 0;

if (empty($email)) {
	onError("Insuffient data");
}

if ($id > 0) {
	
} else {
	// add new user
	$sql = "INSERT INTO `users` VALUES (NULL, :email, MD5(:password), 2, :ds_limit, 0, :xfer_limit, :rx_limit, 0, :tx_limit, 0, 0, 0)";
	$sth = $db->prepare($sql);
	if (!$sth) {
		onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
	}
	if (!@$sth->execute(compact("email","password","ds_limit","xfer_limit","tx_limit","rx_limit"))) {
		onError("DB error: Failed to insert user data to DB",$sth->errorInfo(),$sql);
	}
	onOk();
}
