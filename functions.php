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
 * Genarate HTML output for action.
 * @param array $obj [optional] Associate array which contains key/value.
 * @return 
 */
function viewHTML($obj = NULL) {
	global $action,$agent,$var,$lang;
	
	if (!file_exists("view/".$agent."/".$action.".html"))
		die("Invalid view");
	//ob_start();
	$newHtml = file_get_contents("view/".$agent."/".$action.".html");
//	$newHtml = ob_get_clean();
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
	$ob_level = ob_get_level ();
	if (!empty($ob_level)) {
		echo $newHtml;
		header("Content-Length: ".ob_get_length());
		ob_end_flush();
	} else {
		$len = mb_strlen($newHtml);
		header("Content-Length: ".$len);
		die($newHtml);
	}
}

 /***
  * Escape character use in JSON
  */
function _escape($str)
{
    return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
};

/***
 * Encode array into human readable JSON
 * @param array $in
 * @param int $indent [optional]
 * @param bool $from_array [optional]
 * @return string Readable JSON
 */
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

/**
 * Encrypt a string
 * @param string $string String to be encrypted
 * @return string Encrypted string
 */
function encrypt($string) {
	$key = session_id().$_SERVER['REMOTE_ADDR']."gakki";
	return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), gzcompress($string,9), MCRYPT_MODE_CBC, md5(md5($key))));
}

/**
 * Decrypt the encrypted string
 * @param string $string Encrypted string
 * @return string Original string
 */
function decrypt($string){
	$key = session_id().$_SERVER['REMOTE_ADDR']."gakki";
	return gzuncompress(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), pack("H*", $string), MCRYPT_MODE_CBC, md5(md5($key))), "\0"));
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
		if (isset($a['error'])) {
			$a['error'] = print_r($a['error'],TRUE);
		}
		viewHTML($a);
	}
	die();
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
	$json = json_encode($a);
	$ob_level = ob_get_level ();
	header("Content-Type: application/json");
		if (!empty($ob_level)) {
                echo $json;
//                header("Content-Length: ".ob_get_length());
				ob_end_flush();
		} else {
				$len = strlen($json);
				header("Content-Length: ".$len);
				echo $json;
		}
	die();
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
 * Process path into URL to be downloaded using Lighttpd Secure download module
 * @param string $path Fullpath of the file
 * @return string URL to be downloaded
 */
function lighttpdSecDownload($path) {
	$root =  $_SESSION['cfg']['download_path'];
	if ($_SESSION['login']['level'] > 1) {
		$root = $root."/".$_SESSION['login']['id'];	
	}
	if (!preg_match("/^".preg_quote($root,"/")."/", $path)) {
		onError("Error: Invalid path");
	}
	if (file_exists($path)) {
		// size
		$secret = $_SESSION['cfg']['dl_secret'];
	  	$uri_prefix = $_SESSION['cfg']['dl_prefix'];
	  	
		$f = preg_replace("/^".preg_quote(rtrim($_SESSION['cfg']['download_path'],"/ "),"/")."/", "", $path);
		# current timestamp
	  	$t = time();
		$t_hex = sprintf("%08x", $t);
	 	$m = md5($secret.$f.$t_hex);
	  	# generate link
	  	$url = $uri_prefix.$m."/".$t_hex.$f;
		return $url;
	} else {
		onError("File Not found for download");
	}
}

/**
 * Process path into URL to be downloaded using Apache2 Auth token module
 * @param string $path Fullpath of the file
 * @return string URL to be downloaded
 */
function authTokenDownload($path) {
	if (!function_exists("apache_get_modules") ||  !in_array("mod_auth_token", apache_get_modules())) {
		onError("Auth token module no available");
	}
	
	$root =  $_SESSION['cfg']['download_path'];
	if ($_SESSION['login']['level'] > 1) {
		$root = $root."/".$_SESSION['login']['id'];	
	}
	if (!preg_match("/^".preg_quote($root,"/")."/", $path)) {
		onError("Error: Invalid path");
	}
	if (file_exists($path)) {
		// size
		$secret = $_SESSION['cfg']['dl_secret'];
	  	
		$f = preg_replace("/^".preg_quote(rtrim($_SESSION['cfg']['download_path'],"/ "),"/")."/", "", $path);
		$protectedPath = $_SESSION['cfg']['dl_prefix'];        // Same as AuthTokenPrefix
		$ipLimitation =  $_SESSION['cfg']['use_ip_limitation'];                 // Same as AuthTokenLimitByIp
		$hexTime = dechex(time());             // Time in Hexadecimal
		$fileName = preg_replace("/^".preg_quote(rtrim($_SESSION['cfg']['download_path'],"/ "),"/")."/", "", $path);;    // The file to access
		
		// Let's generate the token depending if we set AuthTokenLimitByIp
		if ($ipLimitation) {
		  $token = md5($secret . $fileName . $hexTime . $_SERVER['REMOTE_ADDR']);
		}
		else {
		  $token = md5($secret . $fileName. $hexTime);
		}
		
		// We build the url
		$url = $protectedPath . $token. "/" . $hexTime . $fileName;
		return $url;
	} else {
		onError("File Not found for download");
	}
}

/**
 * Start download using Apache X-SendFile function
 * @param string $path Fullpath of the file
 */
function xSendFileDownload($path) {
	if (preg_match("/apache/i",$_SERVER['SERVER_SOFTWARE']) && function_exists("apache_get_modules") && !in_array("mod_xsendfile", apache_get_modules())) {
		onError("X-Sendfile module no available");
	}
	$root = $_SESSION['cfg']['download_path'];
	if ($_SESSION['login']['level'] > 1) {
		$root = $root."/".$_SESSION['login']['id'];	
	}
	if (!preg_match("/^".preg_quote($root,"/")."/", $path)) {
		onError("Error: Invalid path");
	}
	if (file_exists($path)) {
		// size
		$filesize = sprintf("%.0f",filesize($path));
		$pi = pathinfo($path);
		$file = $pi['filename'];
		$fileExt = strtolower($pi['extension']);
		$headerName = (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) ? str_replace(".", "%2e", $file).".".$fileExt	: $pi['basename'];
		@header("Content-length: " . $filesize . "\n");
		$is_image = preg_match("/jpg|gif|png/",$fileExt);
		if (!$is_image) {
			@header("Content-type: application/octet-stream\n");
			@header("Content-disposition: attachment; filename=\"".$headerName."\"\n");
			@header("Accept-Ranges: bytes\n");
		} else {
			@header("Content-type: image/$fileExt\n");
		}
		// write the session to close so you can continue to browse on the site.
		@session_write_close();
		if (isset($_SERVER['HTTP_RANGE']) && preg_match("/^bytes=(\\d+)-(\\d*)$/D", $_SERVER['HTTP_RANGE'], $matches) && preg_match("/lighttpd/i",$_SERVER['SERVER_SOFTWARE'])) {
			$from = $matches[1];
			$to = isset($matches[2]) ? $matches[2]: 0;
			if (empty($to))
				$to = $filesize - 1;
			$content_size = $to - $from + 1;
			@header("HTTP/1.1 206 Partial Content");
			@header("Content-Range: bytes {$from}-{$to}/{$filesize}");
			@header("Content-Length: {$content_size}");
		    // The X-Sendfile2 with resume support should be like "/path/to/file 2375680-"
		    $path = str_replace(',', '%2c', urlencode($path)); 
		    @header("X-Sendfile2: ".$path." ".$from."-".$to);
		} else {
			@header("Content-length: " . $filesize . "\n");
			@header("X-Sendfile: ".$path);
		} 
		exit();
	} else {
		onError("File Not found for download");
	}
}


/**
 * Start download using PHP fopen function
 * @param string $path Fullpath of the file
 */
function phpDownloadFile($path) {
	$ob_level = ob_get_level();
	if (!empty($ob_level)) {
		ob_end_clean();
	}
	$root = $_SESSION['cfg']['download_path'];
	if ($_SESSION['login']['level'] > 1) {
		$root = $root."/".$_SESSION['login']['id'];	
	}
	// we need to strip slashes twice in some circumstances
	// Ex.	If we are trying to download test/tester's file/test.txt
	// $down will be "test/tester\\\'s file/test.txt"
	// one strip will give us "test/tester\'s file/test.txt
	// the second strip will give us the correct
	//	"test/tester's file/test.txt"
	if (!preg_match("/^".preg_quote($root,"/")."/", $path)) {
		onError("Error: Invalid path");
	}

	if (file_exists($path)) {
		// size
		$filesize = sprintf("%.0f",filesize($path));
		$pi = pathinfo($path);
		$file = $pi['filename'];
		$fileExt = strtolower($pi['extension']);
		// filenames in IE containing dots will screw up the filename
		$headerName = (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) ? str_replace(".", "%2e", $file).".".$fileExt	: $pi['basename'];
		// partial or full ?
		$bufsize = 32768;
		@session_write_close();
		@set_time_limit(0);
		if (isset($_SERVER['HTTP_RANGE'])) {
			// Partial download
			if (preg_match("/^bytes=(\\d+)-(\\d*)$/D", $_SERVER['HTTP_RANGE'], $matches)) {
				$from = $matches[1];
				$to = isset($matches[2]) ? $matches[2]: 0;
				if (empty($to))
					$to = $filesize - 1;
				$content_size = $to - $from + 1;
				@header("HTTP/1.1 206 Partial Content");
				@header("Content-Range: bytes {$from}-{$to}/{$filesize}");
				@header("Content-Length: {$content_size}");
				@header("Content-Type: application/octet-stream");
				@header("Content-Disposition: attachment; filename=\"".$headerName."\"");
				// write the session to close so you can continue to browse on the site.
				$fh = fopen($path, "rb");
				fseek($fh, $from);
				$cur_pos = ftell($fh);
				while ($cur_pos !== FALSE && ftell($fh) + $bufsize < $to + 1) {
					$buffer = fread($fh, $bufsize);
					echo $buffer;
					$cur_pos = ftell($fh);
					@ob_flush();
					@flush();
				}
				$buffer = fread($fh, $to + 1 - $cur_pos);
				echo $buffer;
				fclose($fh);
				@ob_flush();
				@flush();
			} else {
				@header("HTTP/1.1 500 Internal Server Error");
				onError("Donwload error");				
			}
		} else {
			// standard download
			@header("Content-length: " . $filesize . "\n");
			$is_image = preg_match("/jpg|gif|png/",$fileExt);
			if (!$is_image) {
				@header("Content-type: application/octet-stream\n");
				@header("Content-disposition: attachment; filename=\"".$headerName."\"\n");
				@header("Accept-Ranges: bytes\n");
			} else {
				@header("Content-type: image/{$fileExt}\n");
			}
			// write the session to close so you can continue to browse on the site.
			$file = @fopen($path,"rb");
			while(!feof($file))
			{
				echo (@fread($file, $bufsize));
				@ob_flush();
				@flush();
			}
			@fclose($file);
		}
		die();
	} else {
		onError("File Not found for download");
	}
}