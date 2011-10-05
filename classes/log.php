<?php
require_once("database.php");

class Log {
	/**
	 * Logs an error and user/session data to the error-log
	 *
	 * @param the bug message to be logged
	 */
	public static function error($bugMessage) {
		$sessionInfo = "PAGE: ".$_SERVER['PHP_SELF']."\n";
		$sessionInfo .= "SESSION-ID: ".session_id()."\n";
		$sessionInfo .= "REQUESTDATA=".str_replace("\n", " ", print_r($_REQUEST,true))."\n";
		$sessionInfo .= "SESSIONDATA=".str_replace("\n", " ", getDB()->removePassFromString(print_r($_SESSION,true)))."\n";
		$sessionInfo .= "GETDATA=".str_replace("\n", " ", print_r($_GET,true))."\n";
		$sessionInfo .= "POSTDATA=".str_replace("\n", " ", print_r($_POST,true));
		
		$stackTrace = "";
		$debug = debug_backtrace();
		$prefix = "__";
		foreach ($debug as $dFunc) {
			$stackTrace .= $prefix . $dFunc['file'] . " --> " . $dFunc['function'] . "(), line " . $dFunc['line'] . "\n";
			$prefix .= "__";
		}
			
		$sql = "INSERT INTO log (time, session, message, stacktrace) VALUES (NOW(), " . 
			esc($sessionInfo) . ", " . esc($bugMessage) . ", " . esc($stackTrace) . ")";
		getDB()->query($sql);
		
		return false;
	}
	
	public static function fatal($bugMessage) {
		Log::error($bugMessage);
		exit(-1);	
	}
}
?>