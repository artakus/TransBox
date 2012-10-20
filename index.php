<?php
/****************
 * System name: TransBox
 * Module: Index file
 * Functional overview: This file is the main door for the system 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

define("TRANSBOX",true); //application name heresession_start();
require_once 'config.php';
if ($debug) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
}

try {
	// db will be used globally, so will be defined once.
	$db = new PDO($dbType.":".($dbType=="sqlite" ? "" : "host=").$dbHost.($dbType=="sqlite" ? "" : ";dbname=".$dbName), $dbUser, $dbPass,array(PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //change here
} catch(PDOException $e) {
	die($e->getMessage());
}
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

require_once 'functions.php';

$agent = "pc";
if (preg_match("/android/i",$_SERVER['HTTP_USER_AGENT']))
	$agent = "tablet";
if (preg_match("/mobile/i",$_SERVER['HTTP_USER_AGENT']) || preg_match("/iphone|ipod/i",$_SERVER['HTTP_USER_AGENT']))
	$agent = "mobile";
if (preg_match("/ipad/i",$_SERVER['HTTP_USER_AGENT']))
	$agent = "tablet";

// if language not set, use setting from browser
if (!isset($_SESSION['lang'])) {
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) == "ja")
		$_SESSION['lang'] = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
	else 
		$_SESSION['lang'] = "en";
}

// include language file based on setting
$lang = array();
require_once("lang/lang.en.php"); // load english by default
if (file_exists("lang/lang.".$_SESSION['lang'].".php")) {
	require_once("lang/lang.".$_SESSION['lang'].".php");
}

$action = "index";
if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
}

if((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_REQUEST['_json'])) {
	define('AJAX', TRUE);
} else {
	define('AJAX', FALSE);
}


if ((!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != TRUE) && $action != "login") {
	// check if action is no need to be logon 
	if (in_array($action, $loginLessAction)) {
		require_once 'actions/'.$action.".php";
		die();
	}
	$action = "login";
	if (AJAX) {
		onError($lang['sessTimeOut'],null,null,array('code'=>"01"));
	}
}

if (!file_exists("actions/".$action.".php")){
	if (AJAX) {
		onError("Action file not exists");
	}
	die("Action file not exists");
}

if ($_POST) {
	$_SESSION['last_action'] = time();
	//if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == TRUE && $_SESSION['login']['status'] > 1) {
	//	setLog($action,$_POST);
	//}
}
spl_autoload_register(function ($class) {
	$dir = dirname(__FILE__);
    require_once $dir.'/lib/'.strtr($class, '\\', '/') . '.php';
});
require_once 'actions/'.$action.".php";