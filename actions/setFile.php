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

$uid = $_SESSION['login']['id'];
$oper = isset($_REQUEST['oper']) ? strtolower(trim($_REQUEST['oper'])) : "";
$path = (isset($_REQUEST['path']) && trim($_REQUEST['path']) != "") ? trim($_REQUEST['path']) : "";
if (empty($path) || empty($oper)) {
	onError("Error: Insufficient data");
}

$root =  $_SESSION['cfg']['download_path'];
if ($_SESSION['login']['level'] > 1) {
	$root = $root."/".$_SESSION['login']['id'];	
}
$path = decrypt($path);
$path = str_replace("//", "/", $root."/".$path);
if (!file_exists($path)) {
	onError("Error: File not exists".$path);
}

switch ($oper) {
	case 'download':
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
		break;
	case 'zip':
		$zip = new ZipArchive();
		$filename = $path.".zip";
		if (file_exists($filename)) {
			onError("Error: Zip file already exists, please delete the zip file first");
		}
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			onError("Error: Cannot create zip file");
		}
		@session_write_close();
		set_time_limit(0);
		folderToZip($path, $zip);
		$zip->close();
		break;
	case 'delete':
		if ($_SESSION['login']['level'] > 1) {
			$sql = "SELECT COUNT(*) FROM `torrents` WHERE `path` = :path AND `uid` = :uid";
			$sth = $db->prepare($sql);
			if (!$sth) {
				onError("DB error: Invalid SQL",$db->errorInfo(),$sql,$result);
			}
			if (!$sth->execute(compact("path","uid"))) {
				onError("DB error: Failed to retrive torrent data",$sth->errorInfo(),$sql,$result);
			}
			if (intval($sth->fetchColumn(0)) > 0) {
				onError("Error: ".basename($path)." is related to torrent file. Please delete as Torrents tab instead");
			}
			if (!@unlink($path)) {
				onError("Error: Failed to delete ".basename($path).". Please verify if it related to any torrent");
			}
		} else {
			if (!@unlink($path)) {
				onError("Error: Failed to delete ".basename($path).". Please verify if it related to any torrent");
			}
		}
		break;
	
	default:
		onError("Error: Invalid operation");
		break;
}
onOk();



function folderToZip($folder, &$zipFile, $subfolder = null) {
    if ($zipFile == null) {
        // no resource given, exit
        return false;
    }
    // we check if $folder has a slash at its end, if not, we append one
    $sp = str_split($folder);
    $folder .= end($sp) == "/" ? "" : "/";
	$spb=str_split($subfolder);
    $subfolder .= end($spb) == "/" ? "" : "/";
    // we start by going through all files in $folder
    $handle = opendir($folder);
    while ($f = readdir($handle)) {
        if ($f != "." && $f != "..") {
            if (is_file($folder . $f)) {
                // if we find a file, store it
                // if we have a subfolder, store it there
                if ($subfolder != null)
                    $zipFile->addFile($folder . $f, $subfolder . $f);
                else
                    $zipFile->addFile($folder . $f);
            } elseif (is_dir($folder . $f)) {
                // if we find a folder, create a folder in the zip
                $zipFile->addEmptyDir($f);
                // and call the function again
                folderToZip($folder . $f, $zipFile, $f);
            }
        }
    }
}