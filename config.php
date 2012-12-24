<?php
/****************
 * System name: TransBox
 * Module: Config file
 * Functional overview: This file contains variable that editable to adapt the envirionment. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	

$debug = TRUE; // set debug mode, this will apply on php and javascript. set it false on production mode.

$version = "0.1"; // version number

// action that not need to be logon to access
$loginLessAction = explode(",", "login,setLanguage,lang.js");

// action that need to be access using https connection
$httpsAction = explode(",", "setup,login,setPassword");

$dbType = "mysql"; // sqlite also possible
$dbHost = "localhost"; //  db host or file path for sqlite
$dbUser = "transbox"; //  db user - for mysql, null for sqlite
$dbPass = "b6mFWwM3a9svQth3"; //  db pass - for mysql, null for sqlite
$dbName = "transbox"; // for mysql
$dbPersist = true; 
