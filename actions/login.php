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
		if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
			$username = $_REQUEST['username'];
			$password = $_REQUEST['password'];
			
			$sql = "SELECT * FROM `movie_master`.`user` WHERE `username` = :username AND `status` > 0 LIMIT 1";
			$sth = $dbS->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$dbS->errorInfo(),$sql);
			}
			if (!$sth->execute(compact("username"))) {
				onError("DB error: Invalid SQL",$sth->errorInfo(),$sql);
			}
			$login = $sth->fetch(PDO::FETCH_ASSOC);
			
			if (empty($login)) {
				onError($lang['badlogin']);
			}
			
			if ($password != $login['password']) {
				onError($lang['badpassword']);
			}
			
			$uid = $login['uid'];
			
			$sql = "SELECT `g`.`gid`, `g`.`groupname`,`gp`.`wm_root` FROM `movie_master`.`groups` AS `g`, `movie_master`.`affiliation` AS `a`, `gaspard`.`group` AS `gp` WHERE `a`.`uid` = :uid AND `a`.`rid` = 2 AND `a`.`gid` = `g`.`gid` AND `gp`.`gid` = `g`.`gid`";
			
			$sql2 = "SELECT * FROM `groups` AS `g`  WHERE `id` = :gid";
			
			$sth = $dbS->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$dbS->errorInfo(),$sql);
			}
			if (!$sth->execute(compact("uid"))) {
				onError("DB error: Failed to get affiliation data",$sth->errorInfo(),$sql);
			}
			
			$sth2 = $dbC->prepare($sql2);
			if (!$sth2) {
				onError("DB error: Invalid SQL",$dbC->errorInfo(),$sql2);
			}
			
			$cgroup = null;
			$glogin = NULL;
			while ($glogin = $sth->fetch()) {
				if (empty($glogin)) {
					onError("Invalid group");
				}
				
				$gid = $glogin['gid'];
				
				if (!$sth2->execute(compact("gid"))) {
					onError("DB error: Invalid SQL",$sth2->errorInfo(),$sql2);
				}
				$cgroup = $sth2->fetch();
				if (empty($cgroup))
					continue;
				
				break;
			}
			if (empty($cgroup)) {
				onError("Group not exists, contact system administrator",null,null,$glogin);
			}			
			
			$li = array();
			$li['uid'] = intval($uid);
			$li['username'] = $username;
			$li['name'] = $login['f_name']." ".$login['g_name'];
			
			$li['gid'] = intval($gid);
			$li['groupname'] = $glogin['groupname'];
			$li['root'] = $glogin['wm_root'];
			
			$_SESSION['loggedin'] = TRUE;
			$_SESSION['login'] = $li;
			$_SESSION['token'] = '';
			$_SESSION['encoderConnected'] = FALSE;
			$_SESSION['last_action'] = time();
			if (AJAX)
				onOk("",array('loggedin'=>true));
			else
				header("Location: index.php");
		} else {
			onError($lang['nodata']);
		}
	}
} else {
	viewHTML();	
}