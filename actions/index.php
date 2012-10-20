<?php
/****************
 * System name: TransBox
 * Module: Index action
 * Functional overview: This file code for action index. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
$obj = array();
$obj['name'] = $_SESSION['login']['email'];
$sql = "SELECT * FROM `config`";
$sth = $db->query($sql) or onError();
$_SESSION['cfg'] = $sth->fetchAll(PDO::FETCH_KEY_PAIR);
viewHTML($obj);