<?php
	require_once "init.php";
	define('STAT_START_DATE', "2010-09-07 00:00:00");
	
	HP::printPageHead("Spielerübersicht", "img/top_people.png");
	
	global $cmd;
	if( !getUser()->isGuest())
	{
		switch($cmd)
		{
			case 'userEdit':
				printUserEditForm($userid);
				break;
			case 'userEditConfirm':
				userEditConfirm($userid);
			default:
				printPlayerTable();
		}
	}
	else
		HP::printLoginError();
		
	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function userEditConfirm($userid)
	{
		if(!getUser()->isAdmin() && $userid != getUser()->id)
		{
			HP::printErrorText("Fehlende Berechtigung!");
			return;
		}
		
		global $nachname, $vorname, $nickname, $strasse, $ort, $geburtstag, $email, $telefon, $rights, $passwd, $skype;
		
		$rechte = 0;
		if(isset($rights))
		{
			foreach($rights as $right)
				$rechte |= $right;
		}
		
		if($_FILES['picture']['type'] != "")
		{
			if($_FILES['picture']['type'] != "image/jpeg" && $_FILES['picture']['type'] != "image/pjpeg")
			{
				HP::printErrorText("Ungültiger Dateityp (".$_FILES['picture']['type'].")!");
				return;
			}
			$picFile = fopen($_FILES['picture']['tmp_name'], "r");
			$picture = fread($picFile, filesize($_FILES['picture']['tmp_name']));
			fclose($picFile);
			$picture = addslashes($picture);
			
		}
		
		if($userid > 0)
		{
			$sql = "UPDATE Spieler SET Nachname='$nachname', Vorname='$vorname', Nick='$nickname', Strasse='$strasse', Ort='$ort', GebDatum='$geburtstag', Email='$email', Telefon='$telefon', Skype='$skype'";
			
			if(getUser()->isAdmin()) $sql .= ", Rights=$rechte";
			
			if(isset($picture))
			{
				$sql .= ", Bild='$picture'";
				getSession()->refreshUserPic($userid);
			}
			
			$sql .=  " WHERE SpielerID=$userid";
			
			getDB()->query($sql);
			if(mysql_affected_rows() != 1)
				HP::printErrorText("Konnte Spielerinformation nicht updaten, Grund: ".mysql_error());
				
			if($userid == getUser()->id)
				getSession()->user = new User($userid);
		}
		else
		{			
			$sql = "INSERT INTO Spieler (Nachname, Vorname, Nick, Strasse, Ort, GebDatum, Email, Telefon, Rights, Password, CreationDate, Skype";
			if(isset($picture)) $sql .= ", Bild";
			$sql .=") VALUES ('$nachname', '$vorname', '$nickname', '$strasse', '$ort', '$geburtstag', '$email', '$telefon', $rechte, '$passwd', CURDATE(), '$skype'";
			if(isset($picture)) $sql .= ", '$picture'";
			$sql.= ")";
			getDB()->query($sql);
			if(mysql_affected_rows() != 1)
				HP::printErrorText("Konnte Spieler nicht erstellen, Grund: ".mysql_error());
		}
		
		
	}

	function printUserEditForm($userid)
	{
		if(!getUser()->isAdmin() && $userid != getUser()->id)
		{
			HP::printErrorText("Fehlende Berechtigung!");
			return;
		}
		
		if(!isset($userid))
			$userid = User::getGuestId();
		
		if($userid != User::getGuestId())
		{
			$sql = "SELECT Nachname, Vorname, Nick, Strasse, Ort, Email, Telefon, GebDatum, Rights, Skype FROM Spieler WHERE SpielerID=$userid";
			$result = getDB()->query($sql);
		
			if(mysql_num_rows($result) != 1 || !($row = mysql_fetch_assoc($result)))
			{
				HP::printErrorText("Could not obtain data for this userid!");
				return;
			}
			echo "<h3>Bestehenden Benutzer editieren</h3>";
		}
		else
		{
			echo "<h3>Neuen Benutzer anlegen</h3>";
			$row['Rights'] = 1;
			$row['GebDatum'] = "0000-00-00";
			$row['Telefon'] = "+43...";
		}
		
		
		echo "<form method='post' enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."'>\n";
		echo "<input type='hidden' name='cmd' value='userEditConfirm'/>\n";
		if($userid != User::getGuestId())
			echo "<input type='hidden' name='userid' value='$userid'/>\n";
		echo "<b>Nachname:</b> <input type='text' name='nachname' size='15' value='".$row['Nachname']."'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<b>Vorname:</b> <input type='text' name='vorname' size='15' value='".$row['Vorname']."'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<b>Nickname:</b> <input type='text' name='nickname' size='10' value='".$row['Nick']."'/><br/><br/>\n";
		echo "<b>Straße:</b> <input type='text' name='strasse' size='20' value='".$row['Strasse']."'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<b>Ort:</b> <input type='text' name='ort' size='15' value='".$row['Ort']."'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<b>Geburtstag:</b> <input type='text' name='geburtstag' size='10' value='".$row['GebDatum']."'/><br/><br/>\n";
		echo "<b>Email:</b> <input type='text' name='email' size='25' value='".$row['Email']."'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<b>Telefon:</b> <input type='text' name='telefon' size='15' value='".$row['Telefon']."'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "<b>Skype:</b> <input type='text' name='skype' size='25' value='".$row['Skype']."'/><br/><br/>\n";
		
		if(getUser()->isAdmin())
		{
			echo "<b>Rechte: </b>";
			foreach(User::getRoles() as $role)
			{
				if($role == 0) continue;
				echo "<input type='checkbox' name='rights[]' value='$role' ";
				if(User::authorized($role, $row['Rights']))
					echo "checked='checked'";
				echo "/>".User::roleToString($role)."&nbsp;&nbsp;&nbsp;";
			}
			echo "<br/><br/>";
		}
		
		if($userid == User::getGuestId())
			echo "<b>Initiales Passwort:</b> <input type='text' name='passwd'/><br/><br/>";
			
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='200000'>";
		echo "<b>Benutzerbild:</b> <input type='file' name='picture'  size='40'> (".User::$PIC_WIDTH."x".User::$PIC_HEIGHT." Pixel, JPEG)<br/>";
		echo "<img src='userpic.php?id=$userid' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt=''/>";
		
		echo "<div style='text-align:right'><input type='submit' value='Ok'/></div>";
		
		echo "</form>\n";
	}

	function printPlayerTable()
	{
		$sql = "SELECT SpielerID, Nachname, Vorname, Nick, Strasse, Ort, Email, Telefon, LastTimeStamp, Rights, CreationDate, Skype".
				" FROM Spieler".
				" WHERE SpielerID != ".User::getGuestId().
				" ORDER BY LastTimeStamp DESC";
		$request = getDB()->query($sql);

		echo "<table cellspacing='0' cellpadding='0' >\n";
		
		if(getUser()->isAdmin())
		{
			echo "<tr><td colspan='3' style='text-align:right'>";
				echo "<a href='".$_SERVER['PHP_SELF']."?cmd=userEdit'><img src='img/user_add.png' alt='' title='neuen Benutzer hinzufügen'/></a>";
			echo "</td></tr>";
		}
		
		$rowc=0;
		while($row = mysql_fetch_assoc($request))
		{
			$trdef = "<tr class='rowColor".($rowc++%2)."'>";
				
			echo $trdef;
				echo "<td rowspan='3' style='padding-right:20px; width=".User::$PIC_WIDTH."px;'>";
					echo "<img src='userpic.php?id=".$row['SpielerID']."' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt='' title='".$row['Nick']."'/>";
				echo "</td>";
				
				echo "<td width='100%'>";
					echo "<b>".HP::toHtml($row['Nachname']." ".$row['Vorname'])."</b>";
					if($row['Skype'] != NULL) 
						echo "&nbsp;<img src='http://mystatus.skype.com/smallicon/".$row['Skype']."' alt='' title='Skype'/>";
				echo "</td>";
				
				echo "<td style='text-align:right; font-size: x-small;'>";
					if(getUser()->isAdmin() || $row['SpielerID'] == getUser()->id)
						echo "<a href='".$_SERVER['PHP_SELF']."?cmd=userEdit&amp;userid=".$row['SpielerID']."'><img src='img/user_edit.png' alt='' title='diesen Benutzer editieren'/></a>";
				echo "</td>";
					
			echo "</tr>\n";
			echo $trdef;
				echo "<td colspan='2' style='font-size: x-small;'>";
					echo "Registriert seit: ".HP::formatDate($row['CreationDate'], false, true)."<br/>";
					echo "zuletzt online am ".HP::formatDate($row['LastTimeStamp'])."<br/>";
					echo "Rollen: ";
					$isFirst = true;
					foreach(User::getRoles() as $role)
					{
						if($role == 0 || !User::authorized($role, $row['Rights']))
							continue;
						if(!$isFirst)
							echo ", ";
						else 
							$isFirst=false;
						echo User::roleToString($role);
					}
				echo "</td>";

			echo "</tr>\n";
			echo $trdef;
				echo "<td>";
					echo HP::toHtml($row['Ort'])."<br/>";
					echo HP::toHtml($row['Strasse'])."<br/>";
					echo $row['Telefon']."<br/>";
					echo $row['Email'];
				echo "</td>";
				echo "<td style='text-align: left; font-size: x-small;' nowrap='nowrap'>";
					printStatistics($row['SpielerID'], $row['CreationDate'], $row['Rights']);
				echo "</td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
		
		echo "<br/><div align='right' style='font-size:x-small;'><table>\n";
		echo "<tr><td>Trainingsbeteiligung Mittwoch und Freitag</td><td><img src='img/chartgreen.gif' border='0' height='12' width='20' alt=''/></td></tr>\n";
		echo "<tr><td>Trainingsbeteiligung generell</td><td><img src='img/chartblue.gif' border='0' height='12' width='20' alt=''/></td></tr>\n";
		echo "<tr><td>Spielbeteiligung</td><td><img src='img/chartred.gif' border='0' height='12' width='20' alt=''/></td></tr>\n";
		echo "<tr><td style='text-align:right' colspan='2'>gemessen seit ".STAT_START_DATE."</td></tr>\n";
		echo "</table></div>\n";
	}
	
	function printStatistics($playerId, $creationDate, $privileges)
	{
		// Achtung: Manche Herren haben beide Rollen, aber keine Frau hat eine Herrenhallenrolle, darum geht diese Lösung!
		$isMale = true;
		if(User::authorized(User::$ROLE_INDOOR_MEN, $privileges))
			$isMale = true;
		else if(User::authorized(User::$ROLE_INDOOR_WOMEN, $privileges))
			$isMale = false;
		else
		{
			echo "<center>Kein Hallenspieler</center>";
			return;
		}
		// Wieviele Trainings gab es für den Spieler Montag, Dienstag, Mittwoch und
		// Donnerstag, Freitag, Samstag, Sonntag
		// und Spiele
		$sql = "SELECT".
				" SUM(WEEKDAY(Zeit) BETWEEN 0 AND 2 AND Typ = ".($isMale ? Event::$INDOOR_MEN : Event::$INDOOR_WOMEN).") AS Training1,".
				" SUM(WEEKDAY(Zeit) BETWEEN 3 AND 6 AND Typ = ".($isMale ? Event::$INDOOR_MEN : Event::$INDOOR_WOMEN).") AS Training2,".
				" SUM(Typ = ".($isMale ? Event::$GAME_MEN : Event::$GAME_WOMEN).") AS Spiel".
				" FROM Events".
				" WHERE Zeit > '".$creationDate."'".
				" AND Zeit < NOW()".
				" AND Zeit > '".STAT_START_DATE."'";
		$row = mysql_fetch_assoc(getDB()->query($sql));
		$percTraining1 = $row['Training1'];
		$percTraining2 = $row['Training2'];
		$percSpiel = $row['Spiel'];
		
		// Wieviele Trainings fehlte der Spieler Montag, Dienstag, Mittwoch und
		// Donnerstag, Freitag, Samstag, Sonntag
		// und bei Spielen
		$sql = "SELECT".
				" SUM(WEEKDAY(Zeit) BETWEEN 0 AND 2 AND Typ = ".($isMale ? Event::$INDOOR_MEN : Event::$INDOOR_WOMEN).") AS Training1,".
				" SUM(WEEKDAY(Zeit) BETWEEN 3 AND 6 AND Typ = ".($isMale ? Event::$INDOOR_MEN : Event::$INDOOR_WOMEN).") AS Training2,".
				" SUM(Typ = ".($isMale ? Event::$GAME_MEN : Event::$GAME_WOMEN).") AS Spiel".
				" FROM Events JOIN Abmeldung USING(EventID)".
				" WHERE Zeit > '".$creationDate."'".
				" AND Zeit < NOW()".
				" AND Zeit > '".STAT_START_DATE."'".
				" AND SpielerID = ".$playerId;
		$row = mysql_fetch_assoc(getDB()->query($sql));
		$percTraining = $percTraining1 + $percTraining2 == 0 ? 0 : 1 - ($row['Training1'] + $row['Training2']) / ($percTraining1 + $percTraining2);
		$percTraining1 = $percTraining1 == 0 ? 0 : 1 - $row['Training1'] / $percTraining1;
		$percTraining2 = $percTraining2 == 0 ? 0 : 1 - $row['Training2'] / $percTraining2;
		$percSpiel = $percSpiel == 0 ? 0 : 1 - $row['Spiel'] / $percSpiel;
		
		printChartBar("img/chartgreen.gif", "img/chartgreen2.gif", $percTraining1, "Mittwoch");
		printChartBar("img/chartgreen.gif", "img/chartgreen2.gif", $percTraining2, "Freitag");
		printChartBar("img/chartblue.gif", "img/chartblue2.gif", $percTraining, "Training");
		printChartBar("img/chartred.gif", "img/chartred2.gif", $percSpiel, "Spiel");						
	}
	
	function printChartBar($imgBar1, $imgBar2, $percentage, $title)
	{
		$chartlengthFactor=3.5;
		$height=12;
		
		$percentage *= 100;
		$title .= ": ".number_format($percentage, 1, '.', '')." %";
		$length1 = $chartlengthFactor*$percentage;
		$length2 = $chartlengthFactor*(100-$percentage);
		
		echo "<div>";
			echo "<img src='".$imgBar1."' height='".$height."' width='".$length1."' alt='' title='".$title."'/>";
			echo "<img src='".$imgBar2."' height='".$height."' width='".$length2."' alt='' title='".$title."'/>";
			echo "&nbsp;".number_format($percentage, 1, '.', '')." %<br/>\n";
		echo "</div>";
	}
?> 
