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

$uid = $_SESSION['login']['id'];
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "";
$password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "";
$ds_limit = isset($_REQUEST['ds_limit']) ? intval($_REQUEST['ds_limit'])*(1024*1024) : 0;
$xfer_limit = isset($_REQUEST['xfer_limit']) ? intval($_REQUEST['xfer_limit'])*(1024*1024) : 0;
$rx_limit = isset($_REQUEST['rx_limit']) ? intval($_REQUEST['rx_limit'])*(1024*1024) : 0;
$tx_limit = isset($_REQUEST['tx_limit']) ? intval($_REQUEST['tx_limit'])*(1024*1024) : 0;
$ratio = isset($_REQUEST['ratio']) ? floatval($_REQUEST['ratio']) : 1;
$oper = isset($_REQUEST['oper']) ? strtolower(trim($_REQUEST['oper'])) : "";

if (empty($email)) {
	onError("Insuffient data");
}

if ($id > 0) {
	if (empty($oper)) {
		onError("Insuffient data");
	}
	switch ($oper) {
		case 'reset':
			$sql = "UPDATE `users` SET `rx_current` = 0, `tx_current` = 0 WHERE `id` = :id";
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!@$sth->execute(compact("id"))) {
				onError("DB error: Failed to update user data to DB",$sth->errorInfo(),$sql);
			}
			break;
		case 'edit':
			if (!empty($password)) {
				$sql = "UPDATE `users` SET `email` = :email, `password` = MD5(:password), `ds_limit` = :ds_limit, `xfer_limit` = :xfer_limit, `rx_limit` = :rx_lmimit ,`tx_limit` = :tx_limit, `rx_speed` = :rx_speed, `tx_speed` = :tx_speed, `ratio` = :ratio WHERE `id` = :id";
			} else {
				$sql = "UPDATE `users` SET `email` = :email, `ds_limit` = :ds_limit, `xfer_limit` = :xfer_limit, `rx_limit` = :rx_lmimit ,`tx_limit` = :tx_limit, `rx_speed` = :rx_speed, `tx_speed` = :tx_speed, `ratio` = :ratio WHERE `id` = :id";
			}
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!@$sth->execute(compact("email","password","ds_limit","xfer_limit","tx_limit","rx_limit","ratio","id"))) {
				onError("DB error: Failed to update user data to DB",$sth->errorInfo(),$sql);
			}
			break;
		case 'delete':
			if ($id == $uid) {
				onError("Error: You can't delete yourself");
			}
			$download_path = $_SESSION['cfg']['download_path'];
			$path = $download_path."/".$id;
			$db->beginTransaction();
			$sql = "SELECT COUNT(`t`.`id`) AS `c`, `t`.`hash` AS `h` FROM `torrents` AS `t`, `torrents` AS `tt` WHERE `t`.`hash` = `tt`.`hash` AND `tt`.`uid` = :id GROUP BY `t`.`hash`";
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!@$sth->execute(compact("id"))) {
				onError("DB error: Failed to get user data from DB",$sth->errorInfo(),$sql);
			}
			$tobedeleted = array();
			while($r = $sth->fetch()) {
				if($r['c'] == 1) {
					$tobedeleted[] = $r['h'];
				}
			}
			
			$sql = "DELETE FROM `torrents` WHERE `uid` = :id";
			$sth = $db->prepare($sql);
			if (!$sth) {
				$db->rollBack();
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!@$sth->execute(compact("id"))) {
				$db->rollBack();
				onError("DB error: Failed to remove user torrent to DB",$sth->errorInfo(),$sql);
			}
			$sql = "DELETE FROM `torrents` WHERE `uid` = :id";
			$sth = $db->prepare($sql);
			if (!$sth) {
				$db->rollBack();
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!@$sth->execute(compact("id"))) {
				$db->rollBack();
				onError("DB error: Failed to remove user torrent from DB",$sth->errorInfo(),$sql);
			}
			$sql = "DELETE FROM `users` WHERE `id` = :id";
			$sth = $db->prepare($sql);
			if (!$sth) {
				$db->rollBack();
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!@$sth->execute(compact("id"))) {
				$db->rollBack();
				onError("DB error: Failed to remove user from DB",$sth->errorInfo(),$sql);
			}
			$result = TRUE;
			if (!empty($tobedeleted)) {
				$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
				$torrents_rpc = $rpc->remove($tobedeleted,TRUE);
				$result = $torrents_rpc->result == "success";	
			}
			$deleted = TRUE;
			if (file_exists($path)) {
				$deleted = @unlink($path);	
			}
			$msg = (!$result? "Failed to remove user torrent from Transmission. ": "").(!$deleted ? "Failed to delete user's data from the disk.": "");
			$db->commit();
			onOk($msg);
			break;
		default:
			onError("Error: Invalid operation");
			break;
	}
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
