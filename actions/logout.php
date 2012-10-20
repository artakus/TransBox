<?php
/****************
 * System name: TransBox
 * Module: Logout action
 * Functional overview: This file contains code to logout user and delete all session 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	

//setLog("LOGOUT");
session_unset();
session_destroy();
unset($_SESSION);
onOk();
