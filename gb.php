<?php
	require_once "init.php";
	require_once "securimage.php";
	
	define('VISIBLE_GB_PAGES', 20);
	
	$smileyDir = "img/smiley/";
	$smileys = array
	(
		"[:D]" => array($smileyDir."biggrin.gif", "lautes Lachen"),
		"[:o]" => array($smileyDir."redface.gif", "peinlich"), 
		"[:)]" => array($smileyDir."smile.gif", "lachen"), 
		"[:(]" => array($smileyDir."frown.gif", "nicht happy"),
		"[:yes:]" => array($smileyDir."yes.gif", "yep"),
		"[:confused:]" => array($smileyDir."confused.gif", "verwirrt"),
		"[:mad:]" => array($smileyDir."mad.gif", "verärgert"),
		"[:p]" => array($smileyDir."tongue.gif", "haha!"),
		"[;)]" => array($smileyDir."wink.gif", "zwinkern"),
		"[:winken:]" => array($smileyDir."winken.gif", "winken"),
		"[:rolleyes:]" => array($smileyDir."rolleyes.gif", "sarkastisch"),
		"[:cool:]" => array($smileyDir."cool.gif", "cool"),
		"[:eek:]" => array($smileyDir."eek.gif", "erstaunt"),
		"[:vogel:]" => array($smileyDir."vogel.gif", "bescheuert"),
		"[:tired:]" => array($smileyDir."tired.gif", "pennen"),
		"[:fuckyou:]" => array($smileyDir."finger.gif", "fuck you")
	);
	$smileysBig = array
	(
		"[:baggern:]" => array($smileyDir."baggern.gif", "baggern"),
		"[:pritschen:]" => array($smileyDir."pritschen.gif", "pritschen"),
		"[:service:]" => array($smileyDir."service.gif", "Service"),
		"[:game:]" => array($smileyDir."game.gif", "Spiel"),
		"[:overnet:]" => array($smileyDir."overnet.gif", "übers Netz")
	);
	define('LINK_START_TAG', "[:link:]");
	define('LINK_END_TAG', "[:/link:]");
	
// ===================================================================

	HP::printPageHead("Pinnwand", "img/top_gb.png");
	printPage();
	HP::printPageTail();
	
// ===================================================================

	function printPage()
	{
		global $action;
		global $messageId;
		global $datetime;
		global $message;
		global $visibility;
		global $captcha_code;
		global $sendAsEmail;
		global $emailRecipients;
		global $gbEntriesPerPage;
		global $searchString;
		
		if(isset($gbEntriesPerPage))
			getUser()->setGbEntriesPerPage($gbEntriesPerPage);

		$withEntryLink = true;
		switch($action)
		{
			case 'makeEntry':
				if(makeGBEntry(getUser()->id, $datetime, $message, $visibility, $captcha_code, $messageId))
				{
					if(isset($sendAsEmail))
						printEmailTargetsSelection(getUser()->id, $message, $visibility);
					break;
				}
				
			case 'searchEntry':
				searchEntry($searchString);
				return;
					
			case 'printEntryField':
				printEntryField($messageId);
				break;
				
			case 'printSearchField':
				printSearchField();
				break;

			case 'sendMail':
				broadcastMessageViaMail(getUser()->id, $emailRecipients, $message);
				break;
			
			case 'deleteEntry':
				getDB()->query("DELETE FROM Gaestebuch WHERE SpielerID=".getUser()->id." AND NachrichtID=".$messageId);
				break;
			
			default:
				break;
		}
			
		printGuestbook();	
	}
	
	function printEmailTargetsSelection($userid, $message, $visibility)
	{
		echo "<b>Empfänger der Email auswählen:</b><br/><br/>";
		echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>\n";
		echo "<input type='hidden' name='message' value='$message'/>\n";
		echo "<input type='hidden' name='action' value='sendMail'/>\n";
		
		$sql= "SELECT SpielerID, Nick, Rights & $visibility AS inGroup FROM Spieler WHERE SpielerID != ".User::getGuestId()." AND SpielerID!=".$userid;
		$request = getDB()->query($sql);
		echo "<table width='100%'><tr>";
		$count = 0;
		while($row = mysql_fetch_assoc($request))
		{
			echo "<td>";
			echo "<input type='checkbox' name='emailRecipients[]' value='".$row['SpielerID']."' ";
			if($row['inGroup'] > 0)
				echo "checked='checked'";
			echo "/>".$row['Nick'];
			echo "</td>";
			
			if(++$count % 6 == 0)
				echo "</tr><tr>";
		}
		echo "</tr></table>";
		
		echo "<br/><br/><center><input type='submit' value='Email abschicken'/></center>";
		echo "</form>";
		echo "<br/><br/>";
	}
	
	function broadcastMessageViaMail($senderid, $emailRecipients, $message)
	{
		$sql= "SELECT Vorname, Nachname, Email FROM Spieler WHERE SpielerID=".$senderid;
		$request = getDB()->query($sql);
		if(!($row = mysql_fetch_assoc($request)))
		{
			HP::printErrorText("Could not broadcast message to receivers!");
			return;
		}
		
		$header = "From: Beachaholics-Guestbook <no-reply@beachaholics.net>\r\n";
		$header .= "Reply-To: " . $row['Vorname'] . " " . $row['Nachname'] . " <" . $row['Email'] . ">\r\n";
		$subject = "Nachricht von beachaholics.net";
		
		$recipient = "";
		
		$sql= "SELECT Vorname, Nachname, Email FROM Spieler WHERE";
		foreach($emailRecipients as $receiverid)
			$sql .= " SpielerID = ".$receiverid." OR";
		$sql = substr($sql, 0, strlen($sql)-3);
			
		$request = getDB()->query($sql);
		while($row = mysql_fetch_assoc($request))
		{
			$recipient .= $row['Vorname'] . " " . $row['Nachname'] . " <" . $row['Email'] . ">, ";
		}
		$recipient = substr($recipient, 0, strlen($recipient)-2);

		$message = iconv("UTF-8","ISO-8859-1", $message);
		$header = iconv("UTF-8","ISO-8859-1", $header);
		
		if(!mail($recipient, $subject, $message, $header))
			HP::printErrorText("An error occurred while trying to submit the message to the local smtp server");
	}
	
	function printNavigationField($currentPageIndex, $numRows)
	{
		global $action;		
		
		echo "<div style='text-align:right'>";
		if($action != "printSearchField")
			echo "<a href='".$_SERVER['PHP_SELF']."?action=printSearchField'>suchen <img src='img/search.png' title='Eintrag suchen' alt=''/></a>";
		if($action != "printEntryField")
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?action=printEntryField'>eintragen <img src='img/add_gb_entry.png' title='Eintrag hinzufügen' alt=''/></a>";
		echo "</div>\n";			
		
		echo "<table width='100%' cellpadding='8'><tr><td style='text-align:left'>";
		
		$minVisiblePage = $currentPageIndex - VISIBLE_GB_PAGES/2 + 1;
		$minPageReached = false;
		if($minVisiblePage <= 0)
		{
			$minVisiblePage = 0;
			$minPageReached = true;
		}
		$maxVisiblePage = $minVisiblePage + VISIBLE_GB_PAGES;
		$maxPageReached = false;

		if($maxVisiblePage*getUser()->getGbEntriesPerPage() >= $numRows)
		{
			$maxVisiblePage = $numRows/getUser()->getGbEntriesPerPage();
			$maxPageReached = true;
		}
		
		if(!$minPageReached)
			echo "...";
		
		for($page=$minVisiblePage;$page<$maxVisiblePage;$page++)
		{
			echo "<a href='".$_SERVER['PHP_SELF']."?msg_offset=".$page."'>";
			if($currentPageIndex == $page)
				echo "<b>".($page+1)."</b>";
			else
				echo $page+1;
			echo "</a>\n ";
		}
		if(!$maxPageReached)
			echo "...";
			
		echo "</td><td style='text-align:right'>";
		
		echo "Einträge pro Seite: <select name='gbEntriesPerPage' onchange='updateSiteParam(this)'>";
		foreach(array(3, 5, 10, 15, 20, 50) as $numEntries)
		{
			echo "<option value='".$numEntries."'";
			if(getUser()->getGbEntriesPerPage() == $numEntries)
				echo " selected='selected'";
			echo ">".$numEntries."</option>";
		}
		
		echo "</select>";
		echo "</td></tr></table>";	
	}
	
	// -----------------------------------------------------------------------------------------
	
	function makeGBEntry($userid, $datetime, $message, $visibility, $captcha_code, $messageId=NULL)
	{
		if(!getUser()->isAuthorized($visibility))
		{
			HP::printErrorText("Keine Berechtigung zum Eintragen einer solchen Nachricht!");
			return false;
		}
		
		if(getUser()->isGuest() && $_SESSION['securimage_code_value'] != $captcha_code)
		{
			HP::printErrorText("Falscher Captcha-Code - versuch es nochmals!");
			return false;
		}
		$_SESSION['securimage_code_value'] = '';
		
		if($messageId == NULL)
		{
			// check whether no "refresh" has been performed to avoid duplicate entries
			$sql= "SELECT Nachricht FROM Gaestebuch WHERE SpielerID=".$userid." AND Datum='".$datetime."'";
			$request = getDB()->query($sql);
			if($row = mysql_fetch_assoc($request))
				return true;
		}
		
		if($message=="")
		{
			HP::printErrorText("Leere Nachrichten sind nicht erlaubt!");
			return false;
		}
				
		$trans = array("'" => "\"");
		$message = strtr($message, $trans);
		$sql="";
		if($messageId == NULL)
			$sql = "INSERT INTO Gaestebuch (Datum, SpielerID, Nachricht, Sichtbarkeit) VALUES ('".$datetime."',".$userid.",'".$message."', ".$visibility.")";
		else
			$sql = "UPDATE Gaestebuch SET Nachricht='".$message."', Sichtbarkeit=".$visibility." WHERE SpielerID=".$userid." AND NachrichtID=".$messageId;
			
		$request = getDB()->query($sql);
		
		return true;
	}
	
	// -----------------------------------------------------------------------------------------
	
	function printSearchField($searchString = NULL)
	{
		echo "<div style='text-align:right'>";
			echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
			echo "<b>Suchbegriff:</b>";
			echo "<input type='hidden' name='action' value='searchEntry'/>\n";
			echo "<input type='text' name='searchString' value='" . ($searchString == NULL ? "" : $searchString) . "'/>\n";
			echo "<input type='submit' value='suchen'/>\n";
			echo "</form>";
		echo "</div>";
	}
	
	// -----------------------------------------------------------------------------------------
	
	function searchEntry($searchString)
	{
		printSearchField($searchString);
		
		if(strlen($searchString) <= 3)
		{
			HP::printErrorText("Der Suchbegriff ist zu kurz!");
			return;
		}
		
		$searchString = str_replace("'", "\"", $searchString);
		
		$request = getDB()->query("SELECT NachrichtID, Nick, Datum, Nachricht, SpielerID, Sichtbarkeit, Skype
			FROM Gaestebuch JOIN Spieler USING(SpielerID)
			WHERE ( Sichtbarkeit = 0 OR (Sichtbarkeit & ".getUser()->roles.") > 0 )
			AND Nachricht LIKE '%".$searchString."%' 
			ORDER BY Datum DESC");
			
		echo "\n<table id='guestbook' cellspacing='0' cellpadding='0'>";
		$rowc=0;
		while($row = mysql_fetch_assoc($request))
		{
			echo "<tr class='rowColor2'>";
				echo "<td class='name'>".HP::toHtml($row['Nick'])."</td>\n";
	
				echo "<td class='action'>";
					echo "<img src='img/groupkey.png' alt='' title='Sichtbarkeit der Nachricht'/> ".User::roleToString($row['Sichtbarkeit']);
					
					if($row['Skype'] != NULL)
						echo "&nbsp;<img src='http://mystatus.skype.com/smallicon/".$row['Skype']."' alt='' title='Skype'/>";
					
				echo "</td>";
				echo "<td class='date'>";
					echo HP::formatDate($row['Datum'], true)." <img src='img/clock.png' alt='' title='Zeitpunkt des Eintrags'/> - #".$row['NachrichtID'];
				echo "</td>\n";
			echo "</tr>";
			
			echo "<tr class='rowColor0'>";
				echo "<td class='picture'><img src='userpic.php?id=".$row['SpielerID']."' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt=''/></td>\n";
				$message = $row['Nachricht'];
				$message = convertKeywords(HP::toHtml($message, true));
				$message = str_ireplace($searchString, "<font color='red'>".$searchString."</font>", $message);
				echo "<td colspan='2' class='message'>".$message."</td>\n";
			echo "</tr>\n";
			
			echo "<tr class='empty'><td colspan='2'/></tr>";
			$rowc++;
		}
		echo "</table>";
	}
	
	// -----------------------------------------------------------------------------------------
	
	function printEntryField($messageId = NULL)
	{
		$messageText="";
		$visibility = User::$ROLE_MEMBER;
		$buttonLabel="Eintragen";
		if($messageId != NULL)
		{
			$sql= "SELECT Nachricht, Sichtbarkeit FROM Gaestebuch WHERE NachrichtID='".$messageId."'";
			$request = getDB()->query($sql);
			if($row = mysql_fetch_assoc($request))
			{
				$messageText = $row['Nachricht'];
				$visibility = $row['Sichtbarkeit'];
			}
			$buttonLabel="Bestätigen";
		}
		
		echo "<script type='text/javascript'>";
		echo "function appendText(text)";
		echo "{ document.getElementById('messageTextArea').value += text; } ";
		echo "function insertUrl()";
		echo "{ var link = prompt('Link hier eingeben:', 'http://...'); if(link != null) appendText('".LINK_START_TAG."' + link + '".LINK_END_TAG."'); }";
		echo "</script>";
		
		echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
			echo "<b>Nachricht:</b>";
			echo "<br/><center><textarea id='messageTextArea' name='message' style='width:80%' cols='1' rows='6'>".HP::toHtml($messageText)."</textarea></center>";
			echo "<input type='hidden' name='action' value='makeEntry'/>\n";
			echo "<input type='hidden' name='datetime' value='".HP::getPHPTime()."'/>\n";
			echo "<input type='hidden' name='messageId' value='".$messageId."'/>\n";
			echo "<script language='javascript'>
				function showBigSmileys() 
				{ 
					div_style = document.getElementById('bigSmiley').style;
					if(div_style.height == '')
					{ 
						div_style.visibility='hidden'; 
						div_style.height='0px';
					}
					else
					{
						div_style.visibility='visible'; 
						div_style.height='';
					}
				}
				</script>";
			echo "<p style='text-align:center'>";
			global $smileys, $smileysBig;
			foreach($smileys as $smText => $smIconName)
			{
				echo "<a href='javascript:appendText(\"".$smText."\")'><img src='".$smIconName[0]."' alt='' title='".$smIconName[1]."'/></a> \n";
			}
			
			echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:showBigSmileys();'>*</a>";
			echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:insertUrl()'><img src='img/link.gif' alt='' title='link einfügen'/></a>\n";
			echo "</p>\n";
			
			echo "<p id='bigSmiley' style='text-align:center; visibility:hidden; height:0px;'>";
			foreach($smileysBig as $smText => $smIconName)
			{
				echo "<a href='javascript:appendText(\"".$smText."\")'><img src='".$smIconName[0]."' alt='' title='".$smIconName[1]."'/></a> \n";
			}
			echo "</p>\n";
			
			if(getUser()->isGuest())
			{
				echo "<p style='text-align:center'>";
					echo "<img src='securimage.php?captchaImage' alt=''/><br/>";
					echo "<input type='text' name='captcha_code' value=''/>\n";
				echo "</p>\n";	
			}
			echo "<p style='text-align:right'>";
				if(getUser()->isVorstand())
				{
					echo "<input type='checkbox' name='sendAsEmail'/> zusätzlich als Email versenden&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				echo "Sichtbarkeit: <img src='img/groupkey.png' alt=''/> ";
				echo "<select name='visibility'>";
				foreach(User::getRoles() as $role)
				{
					if(!getUser()->isAuthorized($role))
						continue;
					echo "<option value='".$role."' ".($role == $visibility ? "selected='selected'" : "").">".User::roleToString($role)."</option>";
				}
				echo "</select>\n";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$buttonLabel."'/>";
			echo "</p>\n";
		echo "</form>";
	}
	
	// -----------------------------------------------------------------------------------------
	
	function convertKeywords($string)
	{
		global $smileys, $smileysBig;
		foreach($smileys as $smText => $smIconName)
		{
			$string = str_replace($smText, "<img src='".$smIconName[0]."' alt=''/>", $string);
		}
		foreach($smileysBig as $smText => $smIconName)
		{
			$string = str_replace($smText, "<img src='".$smIconName[0]."' alt=''/>", $string);
		}
		
		while(true)
		{
			$start = strpos($string, LINK_START_TAG);
			if($start === false) break;
			$end = strpos($string, LINK_END_TAG, $start);
			if($end === false) break;
			
			$url = substr($string, $start + strlen(LINK_START_TAG), $end - $start - strlen(LINK_START_TAG));
			$string = substr($string, 0, $start) . "<a href='" . $url . "'>" . $url . "</a>" . substr($string, $end + strlen(LINK_END_TAG));
		}
		
		return $string;
	}
	
	// -----------------------------------------------------------------------------------------
	
	function printGuestbook()
	{
		global $msg_offset, $action;

		// Anzahl der Eintraege erfragen
		$sql = "SELECT COUNT(*) FROM Gaestebuch WHERE (Sichtbarkeit & ".getUser()->roles.") > 0 OR Sichtbarkeit=0";
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		$num_rows = $row['COUNT(*)'];
		
		if($msg_offset=="")
			$msg_offset=0;
		
		printNavigationField($msg_offset, $num_rows);
		
		if(!isset($action))
			echo "<script type='text/javascript'>periodicPageReload()</script>\n";
		
		$sql = "SELECT NachrichtID, Nick, Datum, Nachricht, SpielerID, Sichtbarkeit, Skype
			FROM Gaestebuch JOIN Spieler USING(SpielerID)
			WHERE Sichtbarkeit = 0 OR (Sichtbarkeit & ".getUser()->roles.") > 0 
			ORDER BY Datum DESC LIMIT ".$msg_offset*getUser()->getGbEntriesPerPage()." , ".getUser()->getGbEntriesPerPage();
		
		$request = getDB()->query($sql);
		
		echo "\n<table id='guestbook' cellspacing='0' cellpadding='0'>";
		$rowc=0;
		while($row = mysql_fetch_assoc($request))
		{
			echo "<tr class='rowColor2'>";
				echo "<td class='name'>".HP::toHtml($row['Nick'])."</td>\n";
	
				echo "<td class='action'>";
					echo "<img src='img/groupkey.png' alt='' title='Sichtbarkeit der Nachricht'/> ".User::roleToString($row['Sichtbarkeit']);
					
					if($row['SpielerID'] == getUser()->id && getUser()->id != User::getGuestId())
					{
						$url = $_SERVER['PHP_SELF']."?action=printEntryField&messageId=".$row['NachrichtID'];
						echo "&nbsp;<a href='".$url."'><img src='img/edit_gb_entry.png' alt='editieren' title='Eintrag editieren'/></a>";
						$url = $_SERVER['PHP_SELF']."?action=deleteEntry&messageId=".$row['NachrichtID'];
						echo "&nbsp;<a href='".$url."'><img src='img/delete_gb_entry.png' alt='löschen' title='Eintrag löschen'/></a>";
					}
					
					if($row['Skype'] != NULL)
						echo "&nbsp;<img src='http://mystatus.skype.com/smallicon/".$row['Skype']."' alt='' title='Skype'/>";
					
				echo "</td>";
				echo "<td class='date'>";
					echo HP::formatDate($row['Datum'], true)." <img src='img/clock.png' alt='' title='Zeitpunkt des Eintrags'/> - #".$row['NachrichtID'];
				echo "</td>\n";
			echo "</tr>";
			
			echo "<tr class='rowColor0'>";
				echo "<td class='picture'><img src='userpic.php?id=".$row['SpielerID']."' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt=''/></td>\n";
				echo "<td colspan='2' class='message'>".convertKeywords(HP::toHtml($row['Nachricht'], true))."</td>\n";
			echo "</tr>\n";
			
			echo "<tr class='empty'><td colspan='2'/></tr>";
			$rowc++;
		}
		echo "</table>";
		
		printNavigationField($msg_offset, $num_rows);
	}
?>