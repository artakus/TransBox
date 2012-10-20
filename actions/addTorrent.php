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

$url = isset($_REQUEST['url']) ? trim($_REQUEST['url']) : "";
$uid = $_SESSION['login']['id'];

if (!empty($url)) {
	$torrent = file_get_contents($url);
	if (empty($torrent)) {
		onError("Failed to fetch torrent file");
	}
	$tmp = "tmp/".rand_str(10).".torrent";
	file_put_contents($tmp, $torrent);
	$t = new Torrent($tmp);
	$name = $t->name();
	$size = $t->size();
	$metainfo = base64_encode($torrent);
	$download_path = $_SESSION['cfg']['download_path'];
	$save_path = $download_path."/".md5($_SESSION['login']['email']);
	$save_path = str_replace("//", "/", $save_path);
	$path = $save_path."/".$name;
	$bind = compact("uid","name","path","size","metainfo");
	@unlink($tmp);
	
	addTorrent($torrent,$save_path,$bind);
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array("torrent");
// max file size in bytes
$sizeLimit = 1 * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
$result = $uploader->handleUpload('tmp/');
if ($result['success']) {
	$tmp = "tmp/".$uploader->getName();
	$torrent = file_get_contents($tmp);
	if (empty($torrent)) {
		onError("Failed to read torrent file",null,null,$result);
	}
	$t = new Torrent($tmp);
	$name = $t->name();
	$size = $t->size();
	$metainfo = base64_encode($torrent);
	$download_path = $_SESSION['cfg']['download_path'];
	$save_path = $download_path."/".md5($_SESSION['login']['email']);
	$save_path = str_replace("//", "/", $save_path);
	$path = $save_path."/".$name;
	$bind = compact("uid","name","path","size","metainfo");
	@unlink($tmp);
	addTorrent($torrent,$save_path,$bind,$result);
} else {
	onError("Failed uploading torrent file",null,null,$result);
}
// to pass data through iframe you will need to encode all html tags
///echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
function addTorrent($torrentdata,$save_path,$bind,$result=array()) {
	global $db;
	if (!file_exists($save_path) || is_file($save_path)) {
		@unlink($save_path);
		@mkdir($save_path);
		@chmod($save_path, 0777);
	}
	  /**
   * Set properties on one or more torrents, available fields are:
   *   "bandwidthPriority"   | number     this torrent's bandwidth tr_priority_t
   *   "downloadLimit"       | number     maximum download speed (in K/s)
   *   "downloadLimited"     | boolean    true if "downloadLimit" is honored
   *   "files-wanted"        | array      indices of file(s) to download
   *   "files-unwanted"      | array      indices of file(s) to not download
   *   "honorsSessionLimits" | boolean    true if session upload limits are honored
   *   "ids"                 | array      torrent list, as described in 3.1
   *   "location"            | string     new location of the torrent's content
   *   "peer-limit"          | number     maximum number of peers
   *   "priority-high"       | array      indices of high-priority file(s)
   *   "priority-low"        | array      indices of low-priority file(s)
   *   "priority-normal"     | array      indices of normal-priority file(s)
   *   "seedRatioLimit"      | double     session seeding ratio
   *   "seedRatioMode"       | number     which ratio to use.  See tr_ratiolimit
   *   "uploadLimit"         | number     maximum upload speed (in K/s)
   *   "uploadLimited"       | boolean    true if "uploadLimit" is honored
   * See https://trac.transmissionbt.com/browser/trunk/doc/rpc-spec.txt for more information
   */
	$rpc = new TransmissionRPC($_SESSION['cfg']['transmission_url'],$_SESSION['cfg']['transmission_username'],$_SESSION['cfg']['transmission_password']);
	$param = array();
	if (!empty($_SESSION['cfg']['ratio_enabled']) && intval($_SESSION['cfg']['ratio']) > 0) {
		$param['seedRatioLimit'] = intval($_SESSION['cfg']['ratio']); 
	}
	if (!empty($_SESSION['login']['rx_speed'])) {
		$param['downloadLimit'] = intval($_SESSION['login']['rx_speed']);
		$param['downloadLimited'] = TRUE;
	}
	if (!empty($_SESSION['login']['tx_speed'])) {
		$param['uploadLimit'] = intval($_SESSION['login']['tx_speed']);
		$param['uploadLimited'] = TRUE;
	}
	if (!empty($param)) {
		$param['honorsSessionLimits'] = TRUE;
	}
	
	$addtorrent = $rpc->add_metainfo($torrentdata,$save_path,$param);
	if ($addtorrent->result == "success") {
		$res = $addtorrent->arguments->torrent_added;
		$tid = $res->id;
		$bind['tid'] = $tid;
		$sql = "INSERT INTO `torrents` VALUES (NULL, :uid, :tid, :name, :path, :size, NOW(), :metainfo)";
		$sth = $db->prepare($sql);
		if (!$sth) {
			onError("DB error: Invalid SQL",$db->errorInfo(),$sql);
		}
		if (!@$sth->execute($bind)) {
			onError("DB error: Failed to insert torrent data to DB",$sth->errorInfo(),$sql,$result);
		}
		onOk("",$result);
	} else {
		onError("Failed to add torrent. ".$addtorrent->result,null,null,$result);
	}
}
