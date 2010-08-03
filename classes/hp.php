<?php
	require_once "database.php";
	require_once "user.php";
	require_once "session.php";
	require_once "log.php";

	class HP
	{
		private static $VISIBLE_USER_TIMEOUT = 7200; // 2 Stunden
		private static $VERSION = "v2.41 (2010/03/23)";
		
		public static function printDocumentHead()
		{
			echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
			echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='de' lang='de'>\n";
		}
		
		public static function printPageHead($pageTitle, $siteLogoUrl=NULL, $stylesheet=NULL, $jsToInclude=array())
		{		
			HP::printDocumentHead();
			echo "<head>\n
				<title>TRAININGSSYSTEM der BEACHAHOLICS</title>\n
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>\n
				<link href='/style.css' rel='stylesheet' type='text/css'/>\n";
				if($stylesheet) 
					echo "<link href='/".$stylesheet."' rel='stylesheet' type='text/css'/>\n";	
				echo "<link rel='SHORTCUT ICON' href='favicon.ico'/>\n";
				echo "<script type='text/javascript' src='calendar.js'></script>\n";
				echo "<script type='text/javascript' src='main.js'></script>\n";
				foreach($jsToInclude as $js)
					echo "<script type='text/javascript' src='".$js."'></script>\n";
					
				
			echo "</head>\n";
				
			echo "<body>\n";
				
		HP::tableUserInfo();
		HP::tableLogo();
		HP::tableNavigation();
		echo "<center>";
				
		echo "<table id='layout' cellpadding='0' cellspacing='0'>
					<tr>
						<td id='top_left'/>
						<td id='top'/>
						<td id='top_right'/>
					</tr>
					<tr>
						<td id='left'/>
						<td>";
							HP::tableMainStart($pageTitle, $siteLogoUrl);
		}
		
		private static function tableMainStart($pageTitle, $siteLogoUrl = NULL)
		{
			echo "<table id='main' cellpadding='10'>
				<tr>
					<td id='header'>
						<table width='100%' cellspacing='0' cellpadding='0'><tr>
							<td style='padding-right:30px'>";
								echo $siteLogoUrl == NULL ? "" : "<img src='".$siteLogoUrl."' alt=''/>";
							echo "</td>
							<td style='vertical-align: top; padding-top: 40px; width: 100%; font-size: 20pt; font-style: italic; color:white; text-align: left; '>".$pageTitle."</td>
						</tr></table>
					</td>
				</tr>
				<tr><td id='content'>\n\n";
		}
		
		private static function tableLogo()
		{
			echo "<table id='logo' cellpadding='0' cellspacing='0'>";
			echo "<tr><td class='top'/><td class='top_right'/></tr>";
			echo "<tr><td class='center'>";
			
			echo "<a href='http://beachaholics.net'><img src='/img/beachaholics.png' alt=''/></a>";
			
			echo "</td><td class='right'/></tr>";
			echo "<tr><td class='bottom'/><td class='bottom_right'/></tr>";
			echo "</table>";
		}
		
		private static function tableUserInfo()
		{
			echo "<table id='userinfo' cellpadding='0' cellspacing='0'>";
			echo "<tr><td class='top_left'/><td class='top'/><td class='top_right'/></tr>";
			echo "<tr><td class='left'/><td class='center'>";
			
			echo "<img src='/img/user.png' alt='User:' title='Online Benutzer'/> ";			
			if(!getUser()->isGuest())
			{
				echo "<b>".HP::toHtml(getUser()->nickName)."</b>";
				echo "&nbsp;<a href='javascript:bookmarkLogin(".getUser()->id.", \"".getUser()->md5Pass."\")'><img src='/img/pinned.gif' alt='bookmark' title='Login bookmarken (Passwort-eintippen kann man sich damit sparen)'/></a>";
			}
			else
				echo "Gast";
				
			if(!getUser()->isGuest())
			{
						foreach(HP::getOnlineUsers() as $user)
							echo "<br/>".HP::toHtml($user);
							
			}
			
			echo "</td><td class='right'/></tr>";
			echo "<tr><td class='bottom_left'/><td class='bottom'/><td class='bottom_right'/></tr>";
			echo "</table>";
		}
		
		private static function tableNavigation()
		{
			echo "<table id='navi' cellpadding='0' cellspacing='0'>";
			echo "<tr><td class='top_left'/><td class='top'/><td class='top_right'/></tr>";
			echo "<tr><td class='left'/><td class='center'>";
			
			echo "<a href='/training.php'><img src='/img/navi/training.gif' alt='Events' title='Events'/></a>\n";
			echo "<a href='/ranking.php'><img src='/img/navi/ranking.png' alt='Platzierung' title='Platzierung'/></a>\n";
			if(!getUser()->isGuest())
			{
				echo "<a href='/people.php'><img src='/img/navi/spieler.png' alt='Spielerübersicht' title='Spielerübersicht'/></a>\n";
			}

			
			echo "&nbsp;&nbsp;";
			echo "<a href='/gb.php'><img src='/img/navi/messageboard.png' alt='Pinnwand' title='Pinnwand'/></a>\n";
			if(getUser()->isMember())
			{
				echo "<a href='/wiki.php'><img src='/img/navi/wiki.png' alt='Wiki' title='Wiki'/></a>\n";
			}
				if(getUser()->isMember())
			{
				echo "<a href='/exercises.php'><img src='/img/navi/exercises.png' alt='Übungen' title='Übungen'/></a>\n";
			}
			echo "<a href='/gallery.php'><img src='/img/navi/gallery.png' alt='Gallerie' title='Bildergallerie'/></a>\n";
			if(getUser()->isMember())
			{
				echo "<a href='/files.php'><img src='/img/navi/files.png' alt='Dateien' title='Dateien'/></a>\n";
			}
			
			
			echo "&nbsp;";
			if(getUser()->isAuthorized(User::$ROLE_INDOOR_MEN | User::$ROLE_INDOOR_WOMEN))
			{
				echo "<a href='/drivingcosts.php'><img src='/img/navi/fahrten.png' alt='Fahrtkostenabrechnung' title='Fahrtkostenabrechnung'/></a>\n";
			}
			if(getUser()->isVorstand())
			{
				echo "<a href='/account.php'><img src='/img/navi/konto.png' alt='Kontoübersicht' title='Kontoübersicht'/></a>\n";
			}
				
			
			echo "&nbsp;";
			echo "<a href='/forecast.php'><img src='/img/navi/weather.png' alt='Wetter' title='Wettervorhersage'/></a>\n";
			echo "<a href='/plan.php'><img src='/img/navi/anfahrt.gif' alt='Anfahrt' title='Anfahrt'/></a>\n";
			echo "<a href='/links.php'><img src='/img/navi/link.png' alt='Links' title='Links'/></a>\n";

			
			echo "&nbsp;";
			if(getUser()->isItMe())
			{
				if(getSession()->debug)
					echo "<a href='".$_SERVER['PHP_SELF']."?debug=off'><img src='/img/navi/debugoff.png' alt='' title='hide debug information'/></a>\n";
				else
					echo "<a href='".$_SERVER['PHP_SELF']."?debug=on'><img src='/img/navi/debugon.png' alt='' title='show debug information'/></a>\n";
			}
			
			if(!getUser()->isGuest())
			{
				echo "<a href='/changepass.php'><img src='/img/navi/passwort.gif' alt='Passwort ändern' title='Passwort ändern'/></a>\n";
			}
			if(getUser()->isGuest())
			{
				echo "<a href='/login.php'><img src='/img/navi/login.png' alt='Login' title='Login'/></a>";
			}
			else
			{
				echo "<a href='/login.php?userid=-1'><img src='/img/navi/exit.gif' alt='Logout' title='Logout'/></a>";
			}
			
			echo "</td><td class='right'/></tr>";
			echo "<tr><td class='bottom_left'/><td class='bottom'/><td class='bottom_right'/></tr>";
			echo "</table>";
		}
		
		private static function tableMainEnd()
		{
						HP::printDebugInformation();
					echo "\n\n</td>
				</tr>
				<tr>
					<td id='footer' style='background-image:url(\"".HP::getRandPicURL()."\");'/>
				</tr>
					<tr>
					<td>
						<table align='right' cellspacing='0' cellpadding='0' style='font-size:x-small;'>
							<tr>
								<td style='text-align:right'>für den Inhalt verantwortlich: Beachaholics Kufstein</td>
								<td>&nbsp;&nbsp;</td>
								<td rowspan='2'>
									<a href='http://validator.w3.org/check?uri=referer'><img src='/img/valid_xhtml.gif' alt='Valid XHTML 1.0 Transitional'/></a>
									<a href='http://jigsaw.w3.org/css-validator/'><img src='/img/valid_css.gif' alt='Valid CSS!'/></a>
									<a href='/phpinfo.php' target='_blank'><img src='/img/php.gif' alt='' title='currently running version ".phpversion()."'/></a>
								</td>
							</tr>
							<tr>
								<td style='text-align:right'>".HP::$VERSION." &copy;  by Schier Michael</td>
								<td/>
							</tr>
						</table>
					</td>
				</tr>
			</table>";
		}
		
		public static function printPageTail()
		{		 
									HP::tableMainEnd();
								echo "</td>
								<td id='right'/>
							</tr>
							<tr>
								<td id='bottom_left'/>
								<td id='bottom'/>
								<td id='bottom_right'/>
							</tr>
						</table>
					</center>
				\n</body>\n
			</html>";
		}
		
		private static function printDebugInformation()
		{
			if(!getSession()->debug)
				return;
			
			echo "<div class='errorinfo'>";
			echo "IP-ADDRESS: ".$_SERVER['REMOTE_ADDR']."<br/>";
			echo "SESSION-ID: ".session_id()."<br/>";
			echo "SESSION-DATA: ";
			echo getDB()->removePassFromString(print_r($_SESSION, true));
			echo "<br/>GET: ";
			print_r($_GET);
			echo "<br/>POST: ";
			print_r($_POST);
			
			$dbStats = explode('  ', mysql_stat(getDB()->getConnection()));
			for($i=0;$i<=7;$i++)
				echo "<br/>MySQL ".$dbStats[$i];
			$result = mysql_list_processes(getDB()->getConnection());
			while ($row = mysql_fetch_assoc($result))
	    		printf("<br/>MySQL process: %s %s %s %s %s\n", $row["Id"], $row["Host"], $row["db"], $row["Command"], $row["Time"]);
			
			echo "<br/>ERRORLOG: ";
			echo "(<a href='".$_SERVER['PHP_SELF']."?debug=clear'>clear log</a>)<br/>";
				echo "<div class='errorlog'>";
				echo Log::toHtml();
				echo "</div>";
			echo "</div>";
		}
		
		/**
		 * @return an array of strings containing username and time in minutes since last activity
		 */
		public static function getOnlineUsers()
		{
			$users = Array();
			// ask database for current users
			$sql = "SELECT Nick, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(LastTimeStamp) AS TimeDiff FROM Spieler WHERE " .
					"SpielerID!=".getUser()->id." AND SpielerID>0 AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(LastTimeStamp) < ".HP::$VISIBLE_USER_TIMEOUT." ORDER BY LastTimeStamp DESC";
			$request = getDB()->query($sql);
			
			while($row = mysql_fetch_assoc($request))
				$users[] = $row['Nick']." (".(($row['TimeDiff'] - $row['TimeDiff']%60)/60)."min)";
				
			return $users;
		}
		
		public static function printLoginError($text = 'Nicht genügend Rechte, um diese Seite anzeigen zu können!')
		{
			echo "<br/>";
			echo "<div style='text-align: center; font-size: large;'";
				echo $text."<br/><br/>";
			// 	userid is 0 in order to avoid forwarding
				echo "<a href='/login.php?userid=0'><img src='/img/navi/login.png' alt='login' title='zum Login'/></a><br/>";
			echo "</div>";
		}
		
		public static function printErrorText($text)
		{
			echo "<div style='text-align: center; font-weight: bold; color: red;'>".$text."</div>";
		}
		
		private static function getRandPicURL()
		{
			return "img/randpics/".rand(1,36).".jpg";
		}
		
		public static function toHtml($string, $nl2br = false)
		{
			$string = htmlentities($string, ENT_QUOTES, "UTF-8", false);
			if($nl2br)
				$string = nl2br($string);
			return $string;
		}
	    
		/**
		 * Gets the source of the given website
		 * @param $url of the website to grab
		 * @return plain html code
		 */
		public static function getWebsite($url)
		{
			$content = "";
			if(function_exists("curl_init"))
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				$content = curl_exec($ch);
				curl_close($ch);
			}
			else
			{
				$handle = fopen($url, "r");
				if(!$handle)
					die("Unsupported PHP functionality!");
				while (!feof($handle))
			    	$content .= fgets($handle);
				fclose($handle);
			}
			
			// ist content bereits utf8 codiert?
			if(strpos(strtolower($content), "charset=utf-8"))
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
		public static function formatDate($date, $withCurrentTime = false, $shortFormat = false)
		{
			$unixTime = strtotime($date);
			
			if($withCurrentTime && time() - $unixTime < 3*60*60)
			{
				return "vor ".(int)((time() - $unixTime)/60)." min";
			}
			
			if($shortFormat)
				return strftime("%e.%B %Y",$unixTime);
				
			return strftime("%a, %d.%m.%y - %H:%M",$unixTime);
		}
	
		/**
		 * Get the current time on the server and display it like a MySQL return value.
		 */
		public static function getPHPTime()
		{
			return date("Y-m-d H:i:s");
		}
	}
?>
