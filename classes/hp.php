<?php
require_once "version.php";
require_once "database.php";
require_once "user.php";
require_once "session.php";
require_once "log.php";

class HP {
	private static $VISIBLE_USER_TIMEOUT = 7200; // 2 Stunden
	
	public static function printDocumentHead() {
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
		echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='de' lang='de'>\n";
	}
	
	public static function printPageHead($pageTitle, $siteLogoUrl=NULL, $stylesheet=NULL, $jsToInclude=array()) {		
		HP::printDocumentHead();
		echo "<head>\n
			<title>Beachvolleyballverein Beachaholics Kufstein</title>\n
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>\n
			<link href='style.css' rel='stylesheet' type='text/css'/>\n";
		if ($stylesheet) 
			echo "<link href='".$stylesheet."' rel='stylesheet' type='text/css'/>\n";	
		echo "<link rel='SHORTCUT ICON' href='favicon.ico'/>\n";
		echo "<script type='text/javascript' src='calendar.js'></script>\n";
		echo "<script type='text/javascript' src='main.js'></script>\n";
		foreach ($jsToInclude as $js)
			echo "<script type='text/javascript' src='".$js."'></script>\n";
			
		if (getSession()->getClientResolution() == NULL) {
			if (isset($_COOKIE['screenresolution'])) {
				$screenres = $_COOKIE['screenresolution'];
				$dim = split("x", $screenres);
				getSession()->setClientResolution($dim[0], $dim[1]);
			}
			else {
				echo "<script type='text/javascript'>
	 				function writeCookie() 
	 				{
	 					var enddate = new Date('December 31, 2060');
	 					document.cookie = 'screenresolution='+ screen.width +'x'+ screen.height + ';expires=' + enddate.toGMTString();
	 					window.location.replace('" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "');
	 				}
	 				writeCookie();
	 			</script>";
	 		}
 		}
 			
 		if (HP::displayMini())
 			echo "<style type='text/css'>body { background-color:white; }</style>";
			
		echo "</head>\n";
		echo "<body>\n";
		
		if (HP::displayMini()) {
			echo "<div style='position: absolute; top: 0px; height: 100%; left: 0px; right: 0px; padding-left: 50px'>";
			HP::divNavigation();
			return;
		}
		
		// Background Image
		echo "<div style='position: absolute; bottom: 0px; right: 0px; width: 300px; height: 307px; background-image:url(\"img/background.png\");'></div>";
				
		// Logo
		echo "<div style='position:absolute; top: 70px; left: 50%; margin-left: 400px; width: 100px; height: 112px; padding: 15px; background-color: white;'>";
		echo "<div style='position:absolute; top: -20px; left: 0px; width: 100%; height: 20px; background-image: url(\"img/trans/top.png\"); background-repeat: repeat-x;'></div>\n";
		echo "<div style='position:absolute; top: -20px; left: 100%; width: 20px; height: 20px; background-image: url(\"img/trans/top_right.png\"); background-repeat: no-repeat;'></div>\n";
		echo "<a href='index.php'><img src='img/beachaholics.png' alt=''/></a>";
		echo "<div style='position:absolute; top: 0px; left: 100%; width: 20px; height: 100%; background-image: url(\"img/trans/right.png\"); background-repeat: repeat-y;'></div>\n";
		echo "<div style='position:absolute; top: 100%; left: 0px; width: 100%; height: 20px; background-image: url(\"img/trans/bottom.png\"); background-repeat: repeat-x;'></div>\n";
		echo "<div style='position:absolute; top: 100%; left: 100%; width: 20px; height: 20px; background-image: url(\"img/trans/bottom_right.png\"); background-repeat: no-repeat;'></div>\n";
		echo "</div>";
		
		HP::divUserInfo();
		HP::divNavigation();
		
		echo "<div style='position: absolute; z-index: 1; left: 50%; margin-left: -400px; width: 800px; top: 0px; height: 130px; border-bottom-style: solid; border-bottom-width: 1px; border-bottom-color: black; background-image: url(\"img/maintop.jpg\"); background-repeat: no-repeat; font-size: 20pt; font-style: italic; color:white; text-align: left;'>";
		echo "<div style='position: absolute; top: 0px; left: -20px; width: 20px; height: 100%; background-image: url(\"img/trans/left.png\"); background-repeat: repeat-y;'></div>\n";
		echo "<div style='position: absolute; top: 0px; left: 100%; width: 20px; height: 100%; background-image: url(\"img/trans/right.png\"); background-repeat: repeat-y;'></div>\n";

		echo "<table><tr><td width='160'><img src='".$siteLogoUrl."' alt=''/></td><td>$pageTitle</td></tr></table>";
		echo "</div>\n";	
		
		echo "<div style='position: absolute; top: 130px; bottom: 165px; left: 50%; margin-left: -420px; width: 20px; background-image: url(\"img/trans/left.png\"); background-repeat: repeat-y;'></div>\n";
		echo "<div style='position: absolute; top: 130px; bottom: 165px; left: 50%; margin-left: 400px; width: 20px;  background-image: url(\"img/trans/right.png\"); background-repeat: repeat-y;'></div>\n";
		echo "<div style='position: absolute; top: 130px; bottom: 165px; overflow-y: auto; overflow-x: hidden; left: 50%; margin-left: -400px; width: 800px; background-color: white;'>\n\n";
	}
	
	private static function divUserInfo() {
		echo "<div style='position:absolute; top: 250px; left: 50%; margin-left: 435px; width: 85px; padding: 5px; background-color: white; font-size: 8pt; text-align: left;'>";

		echo "<img src='img/user.png' alt='User:' title='Online Benutzer'/> ";			
		if (!getUser()->isGuest())
			echo "<b>".HP::toHtml(getUser()->nickName)."</b>";
		else
			echo "Gast";
			
		if (!getUser()->isGuest()) {
			foreach(HP::getOnlineUsers() as $user)
				echo "<br/>".HP::toHtml($user);			
		}

		echo "<div style='position: absolute; top: -20px; left: -20px; width: 20px; height: 20px; background-image: url(\"img/trans/top_left.png\"); background-repeat: no-repeat;'></div>\n";
		echo "<div style='position: absolute; top: -20px; left: 0px; width: 100%; height: 20px; background-image: url(\"img/trans/top.png\"); background-repeat: repeat-x;'></div>\n";
		echo "<div style='position: absolute; top: -20px; left: 100%; width: 20px; height: 20px; background-image: url(\"img/trans/top_right.png\"); background-repeat: no-repeat;'></div>\n";
		echo "<div style='position: absolute; top: 0px; left: -20px; width: 20px; height: 100%; background-image: url(\"img/trans/left.png\"); background-repeat: repeat-y;'></div>\n";		
		echo "<div style='position:absolute; top: 0px; left: 100%; width: 20px; height: 100%; background-image: url(\"img/trans/right.png\"); background-repeat: repeat-y;'></div>\n";
		echo "<div style='position:absolute; top: 100%; left: -20px; width: 20px; height: 20px; background-image: url(\"img/trans/bottom_left.png\"); background-repeat: no-repeat;'></div>\n";
		echo "<div style='position:absolute; top: 100%; left: 0px; width: 100%; height: 20px; background-image: url(\"img/trans/bottom.png\"); background-repeat: repeat-x;'></div>\n";
		echo "<div style='position:absolute; top: 100%; left: 100%; width: 20px; height: 20px; background-image: url(\"img/trans/bottom_right.png\"); background-repeat: no-repeat;'></div>\n";
			
		echo "</div>\n";
	}
	
	private static function divNavigation() {
		if (HP::displayMini())
			echo "<div style='position:absolute; z-index: 1; top: 0px; left: 0px; width: 24px; padding: 10px; background-color: white;'>";
		else
			echo "<div style='position:absolute; z-index: 1; top: 70px; width: 24px; left: 50%; margin-left: -444px; padding: 10px; background-color: white;'>";
		
		echo "<a href='event.php'><img src='img/navi/event.gif' alt='Events' title='Events'/></a>\n";
		echo "<a href='ranking.php'><img src='img/navi/ranking.png' alt='Platzierung' title='Platzierung'/></a>\n";
		if (!getUser()->isGuest())
			echo "<a href='users.php'><img src='img/navi/spieler.png' alt='Spielerübersicht' title='Spielerübersicht'/></a>\n";
		echo "<a href='gb.php'><img src='img/navi/messageboard.png' alt='Pinnwand' title='Pinnwand'/></a>\n";
		if (getUser()->isMember())
			echo "<a href='wiki.php'><img src='img/navi/wiki.png' alt='Wiki' title='Wiki'/></a>\n";
		if (getUser()->isMember())
			echo "<a href='exercises.php'><img src='img/navi/exercises.png' alt='Übungen' title='Übungen'/></a>\n";
		echo "<a href='gallery.php'><img src='img/navi/gallery.png' alt='Gallerie' title='Bildergallerie'/></a>\n";
		if (getUser()->isMember())
			echo "<a href='files.php'><img src='img/navi/files.png' alt='Dateien' title='Dateien'/></a>\n";
		if (getUser()->isAuthorized(User::$ROLE_INDOOR_MEN | User::$ROLE_INDOOR_WOMEN))
			echo "<a href='drivingcosts.php'><img src='img/navi/fahrten.png' alt='Fahrtkostenabrechnung' title='Fahrtkostenabrechnung'/></a>\n";
		if (getUser()->isVorstand())
			echo "<a href='account.php'><img src='img/navi/konto.png' alt='Kontoübersicht' title='Kontoübersicht'/></a>\n";	
		echo "<a href='forecast.php'><img src='img/navi/weather.png' alt='Wetter' title='Wettervorhersage'/></a>\n";
		echo "<a href='plan.php'><img src='img/navi/anfahrt.gif' alt='Anfahrt' title='Anfahrt'/></a>\n";
		echo "<a href='links.php'><img src='img/navi/link.png' alt='Links' title='Links'/></a>\n";
		if (getUser()->isItMe())
			echo "<a href='debug.php' target='_blank'><img src='img/navi/debug.png' alt='' title='show debug information'/></a>\n";
		if (!getUser()->isGuest())
			echo "<a href='changepass.php'><img src='img/navi/passwort.gif' alt='Passwort ändern' title='Passwort ändern'/></a>\n";
		if (getUser()->isGuest())
			echo "<a href='login.php'><img src='img/navi/login.png' alt='Login' title='Login'/></a>";
		else
			echo "<a href='login.php'><img src='img/navi/exit.gif' alt='Logout' title='Logout'/></a>";
		
		if (!HP::displayMini()) {
			echo "<div style='position: absolute; top: -20px; left: -20px; width: 20px; height: 20px; background-image: url(\"img/trans/top_left.png\"); background-repeat: no-repeat;'></div>\n";
			echo "<div style='position: absolute; top: -20px; left: 0px; width: 100%; height: 20px; background-image: url(\"img/trans/top.png\"); background-repeat: repeat-x;'></div>\n";
			echo "<div style='position: absolute; top: 0px; left: -20px; width: 20px; height: 100%; background-image: url(\"img/trans/left.png\"); background-repeat: repeat-y;'></div>\n";
			echo "<div style='position:absolute; top: 100%; left: -20px; width: 20px; height: 20px; background-image: url(\"img/trans/bottom_left.png\"); background-repeat: no-repeat;'></div>\n";
			echo "<div style='position:absolute; top: 100%; left: 0px; width: 100%; height: 20px; background-image: url(\"img/trans/bottom.png\"); background-repeat: repeat-x;'></div>\n";
		}
			
		echo "</div>\n";
	}
	
	private static function displayMini() {
		$dim = getSession()->getClientResolution();
		return $dim != NULL && $dim[1] <= 800;
	}
	
	public static function printPageTail() {
		echo "</div>\n";
		if (!HP::displayMini())
			echo "<div style='position: absolute; bottom: 0px; left: 50%; margin-left: -400px; width: 800px; height: 165px;  border-top-style: solid; border-top-width: 1px; border-top-color: black; background-color: white'>
					<div style='width: 800px; height: 120px; background-image:url(\"".HP::getRandPicURL()."\");'></div>
					<table align='right' cellspacing='4' cellpadding='0' style='font-size:x-small; width:100%'>
						<tr>
							<td rowspan='2' width='100%'>
								<a href='munin' target='_blank'><img src='img/network_stats.png' alt='Server statistics'/></a>
							</td>
							<td style='text-align:right;white-space:nowrap'>für den Inhalt verantwortlich: Beachaholics Kufstein</td>
							<td rowspan='2'>&nbsp;&nbsp;</td>
							<td rowspan='2' style='white-space:nowrap'>
								<a href='http://validator.w3.org/check?uri=referer' target='_blank'><img src='img/valid_xhtml.gif' alt='Valid XHTML 1.0 Transitional'/></a>
								<a href='http://jigsaw.w3.org/css-validator/' target='_blank'><img src='img/valid_css.gif' alt='Valid CSS!'/></a>
								<a href='phpinfo.php' target='_blank'><img src='img/php.gif' alt='' title='currently running version ".phpversion()."'/></a>
							</td>
						</tr>
						<tr>
							<td style='text-align:right'>".HP_VERSION." &copy;  by Schier Michael</td>
							<td/>
						</tr>
					</table>
					<div style='position: absolute; top: 0px; left: -20px; width: 20px; height: 100%; background-image: url(\"img/trans/left.png\"); background-repeat: repeat-y;'></div>\n
					<div style='position:absolute; top: 0px; left: 100%; width: 20px; height: 100%; background-image: url(\"img/trans/right.png\"); background-repeat: repeat-y;'></div>\n
					</div>";
		echo "</body>\n";
		echo "</html>";
	}
	
	/**
	 * @return an array of strings containing username and time in minutes since last activity
	 */
	public static function getOnlineUsers() {
		$users = Array();
		// ask database for current users
		$sql = "SELECT nickname, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_contact)) DIV 60 AS time_diff FROM user WHERE " .
				"id!=" . esc(getUser()->id) . " AND id>0 AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_contact) < " . 
				esc(HP::$VISIBLE_USER_TIMEOUT) . " ORDER BY last_contact DESC";
		$request = getDB()->query($sql);
		
		while ($row = mysql_fetch_assoc($request))
			$users[] = $row['nickname']." (". $row['time_diff'] . "min)";
			
		return $users;
	}
	
	public static function printLoginError($text = 'Nicht genügend Rechte, um diese Seite anzeigen zu können!') {
		echo "<br/>";
		echo "<p style='text-align: center; color: #ff0000;'>";
		echo $text."<br/><br/>";
		echo "<a href='login.php'><img src='img/navi/login.png' alt='login' title='zum Login'/></a><br/>";
		echo "</p>";
	}
	
	public static function printErrorText($text) {
		echo "<div style='text-align: center; font-weight: bold; color: red;'>".$text."</div>";
	}
	
	private static function getRandPicURL() {
		return "img/randpics/".rand(1,36).".jpg";
	}
	
	public static function toHtml($string, $nl2br = false) {
		$string = htmlentities($string, ENT_QUOTES, "UTF-8", false);
		if ($nl2br)
			$string = nl2br($string);
		return $string;
	}
    
	/**
	 * Gets the source of the given website
	 * @param $url of the website to grab
	 * @return plain html code
	 */
	public static function getWebsite($url) {
		$content = "";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$handle = fopen($url, "r");
			if (!$handle)
				die("Unsupported PHP functionality!");
			while (!feof($handle))
		    	$content .= fgets($handle);
			fclose($handle);
		}
		
		// ist content bereits utf8 codiert?
		if (strpos(strtolower($content), "charset=utf-8"))
			return $content;
		else
			return utf8_encode($content);
	}
	
	/**
	 * Formats the date
	 *
	 * @param unix-timestamp
	 * @param withCurrentTime - if true, display also times like "vor 3 Minuten"
	 * @return date having the form of day.month.year - hour:minute (Day)
	 */
	public static function formatDate($date, $withCurrentTime = false, $shortFormat = false) {
		$unixTime = strtotime($date);
		
		if ($withCurrentTime && time() - $unixTime < 3*60*60)
			return "vor ".(int)((time() - $unixTime)/60)." min";
		
		if ($shortFormat)
			return strftime("%e.%B %Y",$unixTime);
			
		return strftime("%a, %d.%m.%y - %H:%M",$unixTime);
	}

	/**
	 * Get the current time on the server and display it like a MySQL return value.
	 */
	public static function getPHPTime() {
		return date("Y-m-d H:i:s");
	}
	
	/**
	 * Checks whether the parameter with the given name is set either in the $_GET or the $_POST array
	 */
	public static function isParamSet($name) {
		return isset($_GET[$name]) || isset($_POST[$name]);
	}

	/**
	 * Checks whether the param is set and numeric
	 */
	public static function isParamNumeric($name) {
		if (!HP::isParamSet($name))
			return false;
		return is_numeric(HP::getParam($name));
	}

	/**
	 * Returns the parameter with the given name from the $_GET or the $_POST array.
	 * If no such parameter exists, return NULL
	 */
	public static function getParam($name) {
		$parameter = NULL;
		if (isset($_GET[$name]))
			$parameter = $_GET[$name];
		if (isset($_POST[$name]))
			$parameter = $_POST[$name];
		return $parameter;
	}
}
?>
