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

$path = decrypt($path);
if (!file_exists($path)) {
	onError("Error: File not exists".$path);
}
	
$zip = new ZipArchive();
$filename = $path.".zip";
if (file_exists($filename)) {
	onError("Error: Zip file already exists, please delete the zip file first");
}
if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
	onError("Error: Cannot create zip file");
}
set_time_limit(0);
folderToZip($path, $zip);
$zip->close();
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