<?php
/****************
 * System name: TransBox
 * Module: Get TorrentList
 * Functional overview: This file contains variable that editable to adapt the language. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 20;
$order =  isset($_REQUEST['order']) ? strtoupper(trim($_REQUEST['order'])) : "ASC";
$sort =  isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'name';
$path =  isset($_REQUEST['path']) ? trim($_REQUEST['path']) : '';

$dummy = array('total'=>0,'rows'=>array());

$skip = ($page - 1) * $rows;

$root =  $_SESSION['cfg']['download_path'];
if ($_SESSION['login']['level'] > 1) {
	$root = $root."/".$_SESSION['login']['id'];	
}
$folders = array();
if (!empty($path)) {
	$path = decrypt($path);
}
$realPath = str_replace("//", "/", $root."/".$path);
$files = array();
$name = array();
$size = array();
$type = array();

if (file_exists($realPath)) {
	if (!is_dir($realPath)) {
		onError($lang['invPath']);
	}
	
	$di = new DirectoryIterator($realPath);
	foreach ($di as $ns => $fileinfo) {
		if ($fileinfo->isDot()) {
			continue;
		}
		$n = $fileinfo->getFilename();
		$fullpath = str_replace($root, "", $fileinfo->getPathname());
		if ($fileinfo->isDir()) {
			$type[] = "<DIR>";
			$size[] = 0;
			$name[] = $n; 
			$files[] = array(
				'fullpath' => encrypt($fullpath),
				'name' => $n,
				'size' => 0,
				'type' => "&lt;DIR&gt;",
				'icon' => "folder"
			);
		}
		if ($fileinfo->isFile()) {
			$t = strtoupper($fileinfo->getExtension());
			if ($t == "PART") {
				continue;
			}
			$s = $fileinfo->getSize();
			$type[] = $t;
			$size[] = $s;
			$name[] = $n;
			$icon = "page_white";
			if (preg_match("/JPG|JPEG|GIF|TIF|TIFF|PNG|BMP|PCX|RAW/i", $t))
				$icon = "page_white_picture";
			if (preg_match("/AVI|MPG|MPEG|MP4|MKV|OGV|DIVX|MTS|M2TS|TS|M4V/i", $t))
				$icon = "film";
			if (preg_match("/MP3|WAV|OGG|AAC|FLAC|MPA|M4A/i", $t))
				$icon = "sound";
			if (preg_match("/ZIP|RAR|ARC|ARJ|LZH|CAB|LHA|7Z|R[0-9]{2}|GZ|GZIP|TGZ|TAR/i", $t))
				$icon = "page_white_compressed";
			if (preg_match("/ISO|NGR|IMG/i", $t))
				$icon = "page_white_cd";
			$files[] = array(
				'fullpath' => encrypt($fullpath),
				'name' => $n,
				'size' => $s,
				'type' => $t,
				'icon' => $icon
			);
		}
	}
	$o = ($order == "ASC") ? SORT_ASC : SORT_DESC;
	switch ($sort) {
		case 'name':
			array_multisort($name,$o, SORT_STRING,$type,SORT_ASC, $files);
			break;
		case 'size':
			array_multisort($size,$o,SORT_NUMERIC,$type,$o, $files);
			break;
		case 'type':
			array_multisort($type,$o,SORT_STRING,$name,$o,SORT_STRING, $files);
			break;
		default:
			break;
	}
	$total = count($files);
	$rows = array_splice($files,$skip,$rows);
	onOk("",compact("rows","total"));
}
onError("Path not exists",NULL,NULL,$dummy);