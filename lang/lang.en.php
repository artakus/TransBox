<?php
/****************
 * System name: TransBox
 * Module: Config file
 * Functional overview: This file contains variable that editable to adapt the language. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	
// language setting

$lang['verno'] = $version;
$lang['pageTitle'] = "TransBox";
$lang['logout'] = "Logout";
$lang['error'] = "Error";
$lang['record'] = "Record";
$lang['logout'] = "Logout";
$lang['login'] = "Login";
$lang['username'] = "Username";
$lang['password'] = "Password";
$lang['email'] = "Email";
$lang['nouser'] = "Invalid user";
$lang['badpassword'] = "Wrong password";
$lang['stayLoggedIn'] = "Stay logged in";
$lang['ok'] = "OK";
$lang['cancel'] = "Cancel";
$lang['date'] = "Date";
$lang['setting'] = "Setting";
$lang['control'] = "Control";
$lang['filePath'] = "File path";
$lang['mail'] = "Mail";
$lang['add'] = "Add";
$lang['remove'] = "Remove";
$lang['duration'] = "Duration";

$lang['torrent'] = "Torrent";
$lang['files'] = "Files";
$lang['users'] = "Users";
$lang['info'] = "Info";
$lang['name'] = "Name";
$lang['size'] = "Size";
$lang['addeddate'] = "Added date";
$lang['status'] = "Status";
$lang['up_speed'] = "Upload speed";
$lang['down_speed'] = "Download speed";
$lang['ratio'] = "Ratio";
$lang['addtorrent'] = "Add torrent";
$lang['add'] = "Add";
$lang['url'] = "URL";
$lang['torrentfile'] = "Torent file";
$lang['percentage'] = "Percentage";
$lang['autoreload'] = "Auto reload (every 5 seconds)";
$lang['usedSpace'] = "Used space";
$lang['usedBandwidth'] = "Used bandwidth";
$lang['adduser'] = "Add user";
$lang['deluser'] = "Delete user";
$lang['edituser'] = "Edit user";
$lang['resetuser'] = "Reset user stats";
//$lang[''] = "";

$lang['xferlimit'] = "Bandwidth limit";
$lang['xfercurrent'] = "Bandwidth usage";
$lang['rxlimit'] = "Download limit";
$lang['rxcurrent'] = "Download usage";
$lang['txlimit'] = "Upload limit";
$lang['txcurrent'] = "Upload usage";
$lang['dslimit'] = "Disk space limit";
$lang['dscurrent'] = "Disk usage";
$lang['folder'] = "Folder";
$lang['type'] = "Type";

$lang['tstatus'] = array(
"Pause","Check pending","Checking","","Downloading","","Seeding","","Stopped"
);

$lang['insufData'] = "Insufficient data";
$lang['passIsEmpty'] = "Password is empty. Please insert password";
$lang['sessTimeOut'] = "Session timed out. Please re-login";

$lang['weekDays'] = array("Su","Mo","Tu","We","Th","Fr","Sa");
$lang['monthName'] = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");