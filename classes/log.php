<?php
	require_once("database.php");

	class Log
	{
		/**
		 * Logs an error and user/session data to the error-log
		 *
		 * @param the bug message to be logged
		 */
		public static function error($bugMessage)
		{
			$sessionInfo = "PAGE: ".$_SERVER['PHP_SELF']."\n";
			$sessionInfo .= "SESSION-ID: ".session_id()."\n";
			$sessionInfo .= "REQUESTDATA=".str_replace("\n", " ", print_r($_REQUEST,true))."\n";
			$sessionInfo .= "SESSIONDATA=".str_replace("\n", " ", getDB()->removePassFromString(print_r($_SESSION,true)))."\n";
			$sessionInfo .= "GETDATA=".str_replace("\n", " ", print_r($_GET,true))."\n";
			$sessionInfo .= "POSTDATA=".str_replace("\n", " ", print_r($_POST,true));
			
			$stackTrace = "";
			$debug = debug_backtrace();
			$prefix = "__";
			foreach($debug as $dFunc)
			{
				$stackTrace .= $prefix . $dFunc['file'] . " --> " . $dFunc['function'] . "(), line " . $dFunc['line'] . "\n";
				$prefix .= "__";
			}
			
			$sessionInfo = str_replace("'", "", $sessionInfo);
			$bugMessage = str_replace("'", "", $bugMessage);
			$stackTrace = str_replace("'", "", $stackTrace);			
			
			getDB()->query("INSERT INTO Logs (Zeit, SessionInfo, Meldung, Stacktrace) VALUES (NOW(), '$sessionInfo', '$bugMessage', '$stackTrace')");
			
			return false;
		}

		public static function clear()
		{
			getDB()->query("DELETE FROM Logs");
		}
		
		public static function delete($logId)
		{
			getDB()->query("DELETE FROM Logs WHERE LogID=".$logId);
		}
		
		/**
		 * @return the content of the error log
		 */
		public static function toHtml()
		{
			$out = "";
			$result = getDB()->query("SELECT LogID, Zeit, SessionInfo, Meldung, Stacktrace FROM Logs ORDER BY Zeit DESC");
			while($row = mysql_fetch_assoc($result))
			{
				$out .= "<b>".$row['Zeit']."</b><br/>";
				$out .= HP::toHtml($row['SessionInfo'], true)."<br/>";
				$out .= "<b>".$row['Meldung']."</b><br/>";
				$out .= "<font size='1'>".HP::toHtml($row['Stacktrace'], true)."</font><br/>";
				$out .= "<a href='".$_SERVER['PHP_SELF']."?debug=delete&amp;logId=".$row['LogID']."'>clear log</a><br/><br/>";
			}
			return $out;
		}
	}
?>