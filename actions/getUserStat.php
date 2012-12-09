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

$uid = $_SESSION['login']['id'];
$sql = "SELECT `ds_limit`, `ds_current`, `xfer_limit`, `rx_limit`, `rx_current`, `tx_limit`, `tx_current`, `rx_speed`, `tx_speed` FROM `users` WHERE `id` = :uid LIMIT 1";
$sth = $db->prepare($sql);
if (!$sth) {
	onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
}
if (!$sth->execute(compact("uid"))) {
	onError("DB error: Failed to retrive user data",$sth->errorInfo(),$sql);
}
$userstat = $sth->fetch();
if (!$userstat) {
	onError("Invalid user data");
}
onOk("",compact("userstat"));