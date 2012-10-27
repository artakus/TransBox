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

$path = (isset($_REQUEST['path']) && trim($_REQUEST['path']) != "") ? trim($_REQUEST['path']) : "";
if (empty($path)) {
	onError("Error: Invalid path");
}

if ($_POST) {
	$path = decrypt($path);
	if (!file_exists($path)) {
		onError("Error: File not exists");
	}
	if (is_dir($path)) {
		onError("Error: Multiple files torrent detected. PLease download the file individually from the Files tab");
	}
	
	$url = "";
	if ($_SESSION['cfg']['use_lighttpd_secdownload']) {
		$url = lighttpdSecDownload($path);			
	} elseif($_SESSION['cfg']['use_apache_authtoken']) {
		$url = authTokenDownload($path);
	} elseif($_SESSION['cfg']['use_xsendfile']) {
		$a = array('time'=> time(),'url'=>$path);
		$url = "?action=download&path=".urlencode(encrypt(json_encode($a)));
	} elseif($_SESSION['cfg']['use_symlink']) {
		$md5 = md5($uid.time().$path);
		$dirname = dirname(__FILE__).$_SESSION['cfg']['dl_prefix'].$md5;
		if (!@mkdir($dirname)) {
			onError("Failed to make temporary URL");
		}
		if (!@symlink($path, $dirname."/".basename($path))) {
			onError("Failed to make temporary URL");
		}
		$url = $_SESSION['cfg']['dl_prefix'].$md5."/".basename($path);
	} else {
		$a = array('time'=> time(),'url'=>$path);
		$url = "?action=download&path=".urlencode(encrypt(json_encode($a)));
	}
	onOk("",compact("url"));
}

$json = decrypt($path);
$a = json_decode($json,TRUE);
if (empty($a)) {
	onError("Error: Invalid path specified");
}
if (!isset($a['time']) || !isset($a['url'])) {
	onError("Error: Invalid parameters",$a);
}
if (time() - $a['time'] > $_SESSION['cfg']['dl_timeout']) {
	onError("Error: URL expired");
}

$realPath = $a['url'];

if (!file_exists($realPath)) {
	onError("Error: Path not exists");
}

if($_SESSION['cfg']['use_xsendfile'] && function_exists("apache_get_modules") && in_array("mod_xsendfile", apache_get_modules())) {
	xSendFileDownload($realPath);
} else {
	phpDownloadFile($realPath);
}

