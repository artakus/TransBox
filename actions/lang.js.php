<?php
/****************
 * System name: TransBox
 * Module: Lang for JS
 * Functional overview: This file contains code to convert PHP var to JS var 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	
header("Content-type: text/javascript");
echo "var lang = ".json_encode($lang).";";
?>
