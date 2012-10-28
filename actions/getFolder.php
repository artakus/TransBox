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

$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : "";
$folders = array();
$path = "";
if (!empty($id)) {
	$path = decrypt($id);
} else {
	$folders[] = array(
				'id' => encrypt("/"),
				'text' => "/",
				'state'=>"closed"
			);
}
$root =  $_SESSION['cfg']['download_path'];
if ($_SESSION['login']['level'] > 1) {
	$root = $root."/".$_SESSION['login']['id'];	
}
$realPath = str_replace("//", "/", $root."/".$path);

if (file_exists($realPath)) {
	$di = new DirectoryIterator($realPath);
	foreach ($di as $name => $fileinfo) {
		if ($fileinfo->isDot()) {
			continue;
		}
		if ($fileinfo->isDir()) {
			$fullpath = str_replace($root, "", $fileinfo->getPathname());
			$f = array(
				'id' => encrypt($fullpath),
				'text' => $fileinfo->getFilename(),
				'state'=>"closed"
			);
			if (empty($id)) {
				$folders[0]['children'] = $f;
			} else {
				$folders[] = $f;
			}
		}
	}
}
onOk("",compact("folders"));