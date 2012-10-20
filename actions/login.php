<?php
/****************
 * System name: TransBox
 * Module: Login action
 * Functional overview: This file contains code to handle login request depend on request method 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	

if ($_POST) {
	if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == TRUE) {
		if (AJAX)
			onOk("",array('loggedin'=>true));
		else
			header("Location: index.php");
	} else {
		if (isset($_REQUEST['email']) && isset($_REQUEST['password'])) {
			$email = $_REQUEST['email'];
			$password = md5($_REQUEST['password']);
			
			$sql = "SELECT COUNT(`id`) FROM `users` WHERE `level` = 1";
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!$sth->execute()) {
				onError("DB error: Failed to retrive user data",$sth->errorInfo(),$sql);
			}
			$count = $sth->fetchColumn(0);
			
			if (!$count) {
				$sql = "INSERT INTO `users` VALUES (NULL, :email,:password,1,0,0,0,0,0,0,0)";
				$sth = $db->prepare($sql);
				if (!$sth) {
					onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
				}
				if (!$sth->execute(compact("email","password"))) {
					onError("DB error: Failed to insert initial user data",$sth->errorInfo(),$sql);
				}
			}
			
			$sql = "SELECT * FROM `users` WHERE `email` = :email LIMIT 1";
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
			}
			if (!$sth->execute(compact("email"))) {
				onError("DB error: Failed to retrive user data",$sth->errorInfo(),$sql);
			}
			$login = $sth->fetch();
			if ($login) {
				if ($login['password'] != $password) {
					onError($lang['badpassword']);
				}
				$_SESSION['loggedin'] = true;
				$_SESSION['login'] = $login;
				onOk("",array('loggedin'=>true));
			} else {
				onError($lang['nouser']);
			}
		} else {
			onError("Insufficient data");
		}
	}
} else {
	viewHTML();	
}