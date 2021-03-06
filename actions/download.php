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
	onError("Error: Invalid path",$_REQUEST);
}

$json = decrypt($path);
$a = json_decode($json,TRUE);
if (empty($a)) {
	onError("Error: Invalid path specified");
}
if (!isset($a['time']) || !isset($a['url'])) {
	onError("Error: Invalid parameters",$a);
}
/*
if (time() - $a['time'] > $_SESSION['cfg']['dl_timeout']) {
	onError("Error: URL expired");
}
 * comment this for a while, too much problem. will reconsider.. in future
 */

$realPath = $a['url'];

if (!file_exists($realPath)) {
	onError("Error: Path not exists");
}

if ($_SESSION['cfg']['use_xsendfile'] && 
		(
			preg_match("/lighttpd/i",$_SERVER['SERVER_SOFTWARE']) || 
			(
				preg_match("/apache/i",$_SERVER['SERVER_SOFTWARE']) && function_exists("apache_get_modules") && in_array("mod_xsendfile", apache_get_modules())
			)
		)
	) {
	if (isset($_SERVER['HTTP_RANGE']) && preg_match("/apache/i",$_SERVER['SERVER_SOFTWARE'])) {
		phpDownloadFile($realPath);
	} else {
		xSendFileDownload($realPath);
	}
} else {
	phpDownloadFile($realPath);
}

