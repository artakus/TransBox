<?php
/****************
 * System name: TransBox
 * Module: Functions file
 * Functional overview: This file contains various function to be used in the system. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	
/***
 * Error handler function. For handle any php error/notice/warning
 * @param object $errno
 * @param object $errstr
 * @param object $errfile
 * @param object $errline
 * @param object $errcontext
 * @return 
 */
function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
	
	if (php_sapi_name() == 'cli') {
    	$dir = dirname(__FILE__);
		file_put_contents($dir."/../tmp/php_err.log", date("c")."\t{$errstr}\r\n", FILE_APPEND );
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('handleError');

/***
 * Genarate HTML output for tab.
 * @param array $obj [optional] Associate array which contains key/value.
 * @return string HTML for the module
 */
function loadTab($module,$obj = NULL) {
	global $vailableModules, $enabledModuleForAdmin,$enabledModule,$lang;
	global $alisaDb, $gaspardDb, $smartDb;
	if (file_exists("modules/".$module.".php")) {
		require "modules/".$module.".php";
		$html = ""; 
		if (file_exists("modules/view/".$module."-tab.html")) {
			$html = file_get_contents("modules/view/".$module."-tab.html");
		}
		$newHtml = $html;
		if ($obj != NULL && is_array($obj)) {
			$pattern = array();
			$replacement = array();
			foreach ($obj as $k=>$v) {
				$v = (string)$v;
				if (is_string($v)) {
					$pattern[$k] = "/<:{$k}:>/";
					$replacement[$k] = $v;
				}
			}
			$newHtml = preg_replace($pattern, $replacement, $html);
		}
		$newHtml = preg_replace("/<:\w:>/", "", $newHtml);
		return $newHtml;
	} else {
		return "Failed to load tab for ".$module;
	}
}

/***
 * Genarate HTML output for dialog.
 * @param array $obj [optional] Associate array which contains key/value.
 * @return string HTML for the module
 */
function loadDialog($module,$obj = NULL) {
	global $vailableModules, $enabledModuleForAdmin,$enabledModule,$lang;
	global $alisaDb, $gaspardDb, $smartDb;
	if (file_exists("modules/".$module.".php")) {
		require "modules/".$module.".php";
		$html = ""; 
		if (file_exists("modules/view/".$module."-dialog.html")) {
			$html = file_get_contents("modules/view/".$module."-dialog.html");
		}
		$newHtml = $html;
		if ($obj != NULL && is_array($obj)) {
			$pattern = array();
			$replacement = array();
			foreach ($obj as $k=>$v) {
				$v = (string)$v;
				if (is_string($v)) {
					$pattern[$k] = "/<:{$k}:>/";
					$replacement[$k] = $v;
				}
			}
			$newHtml = preg_replace($pattern, $replacement, $html);
		}
		$newHtml = preg_replace("/<:\w:>/", "", $newHtml);
		return $newHtml;
	} else {
		return "Failed to load dialog for ".$module;
	}
}

/***
 * Genarate HTML output for action.
 * @param array $obj [optional] Associate array which contains key/value.
 * @return 
 */
function viewHTML($obj = NULL) {
	global $action,$agent,$var,$lang;
	
	if (!file_exists("view/".$agent."/".$action.".html"))
		die("Invalid view");
	ob_start();
	require_once 'view/'.$agent."/".$action.".html";
	$newHtml = ob_get_clean();
	$pattern = array();
	$replacement = array();
	
	if ($obj != NULL && is_array($obj)) {
		$pattern = array();
		$replacement = array();
		foreach ($obj as $k=>$v) {
			$v = (string)$v;
			if (is_string($v)) {
				$pattern[$k] = "/<:".preg_quote($k,"/").":>/";
				$replacement[$k] = $v;
			}
		}
		$newHtml = preg_replace($pattern, $replacement, $newHtml);
	}
	foreach ($lang as $k=>$v) {
		if (is_string($v)) {
			$pattern[$k] = "/<!".preg_quote($k,"/")."!>/";
			$replacement[$k] = $v;
		}
	}
	$newHtml = preg_replace($pattern, $replacement, $newHtml);
	
	die($newHtml);
}

/***
 * Process search result from SOAP response, and combine with annotation set from annotation server.
 * @param array $sceneBit
 * @param string $field
 * @return 
 */
function processResult($sceneBit,$field) {
	$e = array();
	$f = explode(",", $field);
	$j = 0;
	foreach($sceneBit as $b) {
		$c = $b->eventset;
		$dd = array();
		$dd['mid'] = $b->movieid;
		$dd['annotationset'] = $b->annotationset;
		$dd['starttime'] = $b->starttime;
		$dd['endtime'] = $b->endtime;
		$d = array();
		$d['_data'] = $dd;
		for ($i = 0; $i < count($c); $i = $i + 2) {
			if (!in_array($c[$i], $f)) continue;
			$d[$c[$i]] = $c[$i+1];
		}
		$e[] = $d;
		$j++;
	}
	return $e;
}

/***
 * 
 * @param object $in
 * @param object $indent [optional]
 * @param object $from_array [optional]
 * @return 
 */

function _escape($str)
{
    return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
};

function json_readable_encode($in, $indent = 0, $from_array = false, $idt = "\t")
{
    $_myself = __FUNCTION__;

    $out = '';
    foreach ($in as $key=>$value)
    {
        $out .= str_repeat($idt, $indent + 1);
        $out .= "\"".$_escape((string)$key)."\": ";
        if (is_object($value) || is_array($value))
        {
            $out .= "\n";
            $out .= $_myself($value, $indent + 1);
        }
        elseif (is_bool($value))
        {
            $out .= $value ? 'true' : 'false';
        }
        elseif (is_null($value))
        {
            $out .= 'null';
        }
        elseif (is_string($value))
        {
            $out .= "\"" . _escape($value) ."\"";
        }
        else
        {
            $out .= $value;
        }

        $out .= ",\n";
    }
    if (!empty($out))
    {
        $out = substr($out, 0, -2);
    }
    $out = str_repeat($idt, $indent) . "{\n" . $out;
    $out .= "\n" . str_repeat($idt, $indent) . "}";
    return $out;
}

/***
 * Generate random string based on given length and character
 * @param int $length [optional]
 * @param string $chars [optional]
 * @return string Random string
 */
function rand_str($length = 5, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    // Length of character list
    $chars_length = (strlen($chars) - 1);
    // Start our string
    $string = $chars{rand(0, $chars_length)};
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // Grab a random character from our list
        $r = $chars{rand(0, $chars_length)};
        // Make sure the same two characters don't appear next to each other
        if ($r != $string{$i - 1}) $string .=  $r;
    }
    // Return the string
    return $string;
}

/***
 * Update user online status. Must be call for every action.
 * @return void
 */
function lastOnline(){
	if (!isset($_SESSION['username']))
		return;
	global $alisaDb;
	$sql = "INSERT INTO `onlineusers` VALUES ('{$_SESSION['username']}','".implode(",",$_SESSION['groups'])."',".time().")
				ON DUPLICATE KEY
			UPDATE `lastaccess` = ".time();
	$alisaDb->exec($sql);
}


function encrypt($string) {
	
}

function decrypt($string){
	
}



/***
 * Set log function
 * @param string $cmd Command
 * @param string $arg Stringyfied JSON
 * @return bool TRUE if insert into DB properly
 */
function setLog($cmd, $arg = "") {
	if (is_object($arg))
		$arg = json_encode($arg);
	if (is_array($arg))
		$arg = json_encode($arg);
		
	if (isset($_SESSION['username']))
		$username = $_SESSION['username'];
	else if (isset($_POST['username']))
		$username = $_POST['username'];
	else
		$username = "not available";	
		
	if (isset($_SESSION['myguid']))
		$myguid = $_SESSION['myguid'];
	else if (isset($_POST['myguid']))
		$myguid = $_POST['myguid'];
	else
		$myguid = "not available";	
		 
	return;
	global $alisaDb;
	$ip = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
	if (strpos($ip,"unknown") !== false) {
		$ip = $_SERVER["REMOTE_ADDR"];
	}
	
	$sql = "INSERT INTO `log_manager` VALUES (NULL,:myguid,:username,NOW(),:sessid,:cmd,:ip,:uagent,:arg)";
	$sth = $alisaDb->prepare($sql);
	$arr = array();
	$arr['myguid'] = $myguid;
	$arr['username'] = $username;
	$arr['sessid'] = session_id();
	$arr['cmd'] = $cmd;
	$arr['ip'] = $ip;
	$arr['uagent'] = $_SERVER["HTTP_USER_AGENT"];
	$arr['arg'] = $arg;
	$res = @$sth->execute($arr);
	return $res;
}

/***
 * Create and array of YYYY-MM date string based on given date range
 * @param int $fromMonth Start month
 * @param int $fromYear Start year
 * @param int $toMonth End month
 * @param int $toYear End year
 * @return array Array contains YYYY-MM
 */
function createMonthRangeArray($fromMonth,$fromYear,$toMonth,$toYear) {
	$y = $toYear - $fromYear;
	$m = ($toMonth - $fromMonth)+1;
	$t = ($y * 12) + $m;
	
	$a = array();
	$year = $fromYear;
	$month = $fromMonth;
	for ($i=0; $i < $t; $i++) { 
		$a[] = $year."-".str_pad($month, 2,"0",STR_PAD_LEFT) ;
		$month++;
		if ($month > 12) {
			$year++;
			$month = 1;
		}
	}
	return $a;
}


/***
 * Create and array of YYYY-MM-DD date string based on given date range
 * @param string $strDateFrom Start date (in parsable format)
 * @param string $strDateTo End date (in parsable format)
 * @return array Array contains YYYY-MM-DD
 */
function createDateRangeArray($strDateFrom,$strDateTo) {
  // takes two dates formatted as YYYY-MM-DD and creates an
  // inclusive array of the dates between the from and to dates.

  // could test validity of dates here but I'm already doing
  // that in the main script

  $aryRange=array();

  $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
  $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

  if ($iDateTo>=$iDateFrom) {
    array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry

    while ($iDateFrom<$iDateTo) {
      $iDateFrom+=86400; // add 24 hours
      array_push($aryRange,date('Y-m-d',$iDateFrom));
    }
  }
  return $aryRange;
}


/***
 * Function to be called on any fatal error. It will die with JSON response.
 * @param string $msg Message to be shown to user. [optional]
 * @param mixed $error Error details. In available type. [optional]
 * @param string $sql SQL string if any. [optional]
 * @param array $array Predefined return array. [optional]
 * @return void
 */
function onError($msg="",$error=array(),$sql="",$array=array()){
	$a = $array;
	$a['res'] = "KO";
	$a['msg'] = $msg;
	if (!empty($error))
		$a['error'] = $error;
	if (!empty($sql))
		$a['sql'] = $sql;
	if (AJAX) {
		
		die(json_encode($a));
	} else {
		$obj = $a;
		global $action,$agent,$var,$lang;
		$action = "error";
		if (isset($a['error']))
			$a['error'] = print_r($a['error'],TRUE);
		viewHTML($a);
	}
}

/**
 * Function to be called at the end of action if it returns JSON data.
 * @param string $msg Message to be shown to user. [optional]
 * @param array $array Predefined return array. [optional]
 * @return void
 * @author Syahir
 */
function onOk($msg="",$array=array()) {
	$a = $array;
	$a['res'] = "OK";
	$a['msg'] = $msg;
	die(json_encode($a));
}

/**
 * Function to strip tag and remove event attribute from HTML tag.
 * @param string $sSource HTML source
 * @param string $aAllowedTags Allowed tags ie. <a><br>. [optional]
 * @return string Stripped HTML
 * @author Syahir
 */
function strip_tags_event_attr($sSource, $aAllowedTags ="") {	
	return preg_replace('/\s{1}on\w+="[^"]+"[\s|>]{1}/'," ",strip_tags($sSource, $aAllowedTags));
}

/**
 * Function to validate email address.
 * @param string $email Email address
 * @return bool TRUE if email address is valid
 */
function checkMailAddr($email)
{ 
  $regex = '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' 
  ; 
  if (preg_match($regex, trim($email), $matches)) { 
    return array($matches[1], $matches[2]); 
  } else { 
    return false; 
  } 
}

/**
 * Function to create tcp client for CerdasCore
 * @param string Command to be sent to CerdasCore
 * @param array Data to be sent as with the command to CerdasCore
 * @return array Response from CerdasCore
 */
function coreClient($host,$command, $data) {
	global $cerdasCorePort;
	
	try {
		$fp = @fsockopen("tcp://".$host, $cerdasCorePort, $errno, $errstr, 3);
	} catch(exception $ex) {
		return array('error'=>$ex->getMessage());
	}
	if (!$fp) {
		if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
			return array('error'=>"Failed to create socket","etc"=>array('str'=> $errstr, 'no'=>$errno));
		} else {
			onError("Failed to create socket",array('str'=> $errstr, 'no'=>$errno));
		}
	} else {
		$array = array(
			'cmd'=>$command,
			'token' => $_SESSION['token'],
			'data' => $data
		);
		if (is_null($data)) {
			unset($array['data']);
		}
		stream_set_timeout($fp,3);
		$json = json_encode($array);
	    fwrite($fp, $json);
		$res = "";
		stream_set_timeout($fp,3);
		try {
			while ($out = fgets($fp)) {
				$res .= $out;
				if (strlen(trim($out)) == 0) {
					break;
				}
				if (feof($fp)) {
					break;
				}
		    }
		} catch (exception $ex) {
			onError("Error connection",array('msg'=>$ex->getMessage()));
		}
	    fclose($fp);
		$json = json_decode($res,TRUE);
		if (is_null($json)) {
			return array('error'=>"unknown",'ret'=>$res);
		}
		return $json;
	}
}

/**
 * Function to create async tcp client for CerdasCore
 * @param string Command to be sent to CerdasCore
 * @param array Data to be sent as with the command to CerdasCore
 */
function coreClientAsync($host,$command, $data) {
	$dir = dirname(__FILE__);
	$_SESSION['async'] = array('cmd'=> $command, 'data'=> $data);
	$command = "/usr/bin/php -f ".$dir."/actions/asyncCoreClient.php ".$host." ".session_id();
	exec("$command > /dev/null &");
}