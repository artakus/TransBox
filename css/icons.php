<?php
/****************
 * System name: TransBox
 * Module: Famfam icon css
 * Functional overview: This file contains code to convert list of famfam icon file to css usable in easyui framework 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

header('Content-type: text/css');
foreach (new DirectoryIterator('famfam') as $fileInfo) {
    if(!$fileInfo->isFile()) continue;
    if(strtolower(substr($fileInfo->getBasename(),-3)) != "png") continue;
    echo ".ff-".str_replace("_","-",$fileInfo->getBasename('.png'))."{\n	background:url('famfam/".$fileInfo->getBasename()."') no-repeat;\n}\n";
}
