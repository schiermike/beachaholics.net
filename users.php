<?php
require_once "init.php";
define('STAT_START_DATE', "2011-09-07 00:00:00");

printPage();

// ===================================================================
// ===================================================================

function printPage() {
	HP::printPageHead("Spielerübersicht", "img/top_people.png");

	if (!getUser()->isGuest()) {
		switch (HP::getParam('action')) {
			case 'userEdit':
				printUserEditForm(HP::isParamSet('userid') ? HP::getParam('userid') : User::$GUEST_ID);
				break;
			case 'userEditConfirm':
				userEditConfirm(HP::getParam('userid'));
			default:
				printPlayerTable();
		}
	}
	else
		HP::printLoginError();
		
	HP::printPageTail();
}

function userEditConfirm($userid) {
	if (!getUser()->isAdmin() && $userid != getUser()->id) {
		HP::printErrorText("Fehlende Berechtigung!");
		return;
	}
	
	$roles = 0;
	if (HP::isParamSet('roles')) {
		foreach (HP::getParam('roles') as $role)
			$roles |= $role;
	}
	
	if($_FILES['picture']['type'] != "") {
		if ($_FILES['picture']['type'] != "image/jpeg" && $_FILES['picture']['type'] != "image/pjpeg") {
			HP::printErrorText("Ungültiger Dateityp (".$_FILES['picture']['type'].")!");
			return;
		}
		$picFile = fopen($_FILES['picture']['tmp_name'], "r");
		$picture = fread($picFile, filesize($_FILES['picture']['tmp_name']));
		fclose($picFile);
		$picture = addslashes($picture);
		
	}
	
	if($userid > 0) {
		$sql = "UPDATE user SET lastname=" . esc(HP::getParam('lastname')) .
			", firstname=" . esc(HP::getParam('firstname')) .
			", nickname=" . esc(HP::getParam('nickname')) .
			", street=" . esc(HP::getParam('street')) .
			", city=" . esc(HP::getParam('city')) .
			", birthday=" . esc(HP::getParam('birthday')) .
			", email=" . esc(HP::getParam('email')) .
			", phone=" . esc(HP::getParam('phone'))."'";
		
		if (getUser()->isAdmin()) 
			$sql .= ", roles=" . esc($roles);
		
		if (isset($picture))
			$sql .= ", avatar=" . esc($picture);
		
		$sql .=  " WHERE id=" . esc($userid);
		
		getDB()->query($sql);
		if (mysql_affected_rows() != 1)
			HP::printErrorText("Konnte Spielerinformation nicht updaten, Grund: ".mysql_error());
			
		if ($userid == getUser()->id)
			getSession()->user = new User($userid);
	}
	else {			
		$sql = "INSERT INTO user (lastname, firstname, nickname, street, city, birthday, email, phone, roles, password, creation_date";
		if(isset($picture)) $sql .= ", avatar";
		$sql .=") VALUES (".
			esc(HP::getParam('lastname'))  . "," .
			esc(HP::getParam('firstname')) . "," .
			esc(HP::getParam('nickname'))  . "," .
			esc(HP::getParam('street'))    . "," .
			esc(HP::getParam('city'))      . "," .
			esc(HP::getParam('birthday'))  . "," .
			esc(HP::getParam('email'))     . "," .
			esc(HP::getParam('phone'))     . "," . 
			$roles                         . "," .
			esc(HP::getParam('password'))  . ", CURDATE()";
		if(isset($picture)) $sql .= ", '$picture'";
		$sql.= ")";
		getDB()->query($sql);
		if (mysql_affected_rows() != 1)
			HP::printErrorText("Konnte Spieler nicht erstellen, Grund: ".mysql_error());
	}
}

function printUserEditForm($userid) {
	if (!getUser()->isAdmin() && $userid != getUser()->id) {
		HP::printErrorText("Fehlende Berechtigung!");
		return;
	}
	
	if($userid != User::$GUEST_ID) {
		$sql = "SELECT lastname, firstname, nickname, street, city, email, phone, birthday, roles FROM user WHERE id=" . esc($userid);
		$result = getDB()->query($sql);
	
		if (mysql_num_rows($result) != 1 || !($row = mysql_fetch_assoc($result))) {
			HP::printErrorText("Could not obtain data for this userid!");
			return;
		}
		echo "<h3>Bestehenden Benutzer editieren</h3>";
	}
	else {
		echo "<h3>Neuen Benutzer anlegen</h3>";
		$row['roles'] = 1;
		$row['birthday'] = "0000-00-00";
		$row['phone'] = "+43...";
	}
	
	
	echo "<form method='post' enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."'>\n";
	echo "<input type='hidden' name='action' value='userEditConfirm'/>\n";
	if ($userid != User::$GUEST_ID)
		echo "<input type='hidden' name='userid' value='$userid'/>\n";
	echo "<b>Nachname:</b> <input type='text' name='lastname' size='15' value='";
	echo isset($row['lastname']) ? $row['lastname'] : "";
	echo "'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";

	echo "<b>Vorname:</b> <input type='text' name='firstname' size='15' value='";
	echo isset($row['firstname']) ? $row['firstname'] : "";
	echo "'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";

	echo "<b>Nickname:</b> <input type='text' name='nickname' size='10' value='";
	echo isset($row['nickname']) ? $row['nickname'] : "";
	echo "'/><br/><br/>\n";

	echo "<b>Straße:</b> <input type='text' name='street' size='20' value='";
	echo isset($row['street']) ? $row['street'] : "";
	echo "'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";

	echo "<b>Ort:</b> <input type='text' name='city' size='15' value='";
	echo isset($row['city']) ? $row['city'] : "";
	echo "'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";

	echo "<b>Geburtstag:</b> <input type='text' name='birthday' size='10' value='";
	echo isset($row['birthday']) ? $row['birthday'] : "";
	echo "'/><br/><br/>\n";

	echo "<b>Email:</b> <input type='text' name='email' size='25' value='";
	echo isset($row['email']) ? $row['email'] : "";
	echo "'/>&nbsp;&nbsp;&nbsp;&nbsp;\n";

	echo "<b>Telefon:</b> <input type='text' name='phone' size='15' value='";
	echo isset($row['phone']) ? $row['phone'] : "";
	echo "'/>\n";
	
	echo "<table><tr><td width='200'>";
	
	if (getUser()->isAdmin()) {
		echo "<b>Rollen:</b><br/>";
		foreach (User::getRoles() as $role) {
			if ($role == 0)
				continue;
			echo "<input type='checkbox' name='roles[]' value='$role' ";
			if (User::authorized($role, $row['roles']))
				echo "checked='checked'";
			echo "/>".User::roleToString($role)."<br/>";
		}
	}
	
	echo "</td><td>";

	echo "<input type='hidden' name='MAX_FILE_SIZE' value='200000'>";
	echo "<b>Benutzerbild:</b>";
	echo "<table><tr><td>";
	echo "<img src='userpic.php?id=$userid' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt=''/>";
	echo "</td><td>";
	echo "<input type='file' name='picture'  size='25'><br/>(".User::$PIC_WIDTH."x".User::$PIC_HEIGHT." Pixel, JPEG)<br/>";
	echo "</td></tr></table>";
	
	
	echo "</td></tr></table>";
	
	if ($userid == User::$GUEST_ID)
		echo "<b>Initiales Passwort:</b> <input type='text' name='password'/><br/><br/>";
	echo "<div style='text-align:right'><input type='submit' value='Ok'/></div>";
	
	echo "</form>\n";
}

function printPlayerTable() {
	$sql = "SELECT id, lastname, firstname, nickname, street, city, email, phone, last_contact, roles, creation_date".
			" FROM user".
			" WHERE id != " . esc(User::$GUEST_ID) .
			" ORDER BY last_contact DESC";
	$request = getDB()->query($sql);

	echo "<table cellspacing='0' cellpadding='0' >\n";
	
	if (getUser()->isAdmin()) {
		echo "<tr><td colspan='3' style='text-align:right'>";
		echo "<a href='".$_SERVER['PHP_SELF']."?action=userEdit'><img src='img/user_add.png' alt='' title='neuen Benutzer hinzufügen'/></a>";
		echo "</td></tr>";
	}
	
	$rowc=0;
	while ($row = mysql_fetch_assoc($request)) {
		$trdef = "<tr class='rowColor".($rowc++%2)."'>";
			
		echo $trdef;
		echo "<td rowspan='3' style='padding-right:20px; width=".User::$PIC_WIDTH."px;'>";
		echo "<img src='userpic.php?id=".$row['id']."' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt='' title='".$row['nickname']."'/>";
		echo "</td>";
			
		echo "<td width='100%'>";
		echo "<b>".HP::toHtml($row['lastname']." ".$row['firstname'])."</b>";
		echo "</td>";
			
		echo "<td style='text-align:right; font-size: x-small;'>";
		if (getUser()->isAdmin() || $row['id'] == getUser()->id)
			echo "<a href='".$_SERVER['PHP_SELF']."?action=userEdit&amp;userid=".$row['id']."'><img src='img/user_edit.png' alt='' title='diesen Benutzer editieren'/></a>";
		echo "</td>";
				
		echo "</tr>\n";
		echo $trdef;
		echo "<td colspan='2' style='font-size: x-small;'>";
		echo "Registriert seit: ".HP::formatDate($row['creation_date'], false, true)."<br/>";
		echo "zuletzt online am ".HP::formatDate($row['last_contact'])."<br/>";
		echo "Rollen: ";
		$isFirst = true;
		foreach (User::getRoles() as $role) {
			if ($role == 0 || !User::authorized($role, $row['roles']))
				continue;
			if (!$isFirst)
				echo ", ";
			else 
				$isFirst=false;
			echo User::roleToString($role);
		}
		echo "</td>";

		echo "</tr>\n";
		echo $trdef;
		echo "<td>";
		echo HP::toHtml($row['street'])."<br/>";
		echo HP::toHtml($row['city'])."<br/>";
		echo $row['phone']."<br/>";
		echo $row['email'];
		echo "</td>";
		echo "<td style='text-align: left; font-size: x-small;' nowrap='nowrap'>";
//		printStatistics($row['id'], $row['creation_date'], $row['roles']);
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
/*	
	echo "<br/><div align='right' style='font-size:x-small;'><table>\n";
	echo "<tr><td>Trainingsbeteiligung Mittwoch und Freitag</td><td><img src='img/chartgreen.gif' border='0' height='12' width='20' alt=''/></td></tr>\n";
	echo "<tr><td>Trainingsbeteiligung generell</td><td><img src='img/chartblue.gif' border='0' height='12' width='20' alt=''/></td></tr>\n";
	echo "<tr><td>Spielbeteiligung</td><td><img src='img/chartred.gif' border='0' height='12' width='20' alt=''/></td></tr>\n";
	echo "<tr><td style='text-align:right' colspan='2'>gemessen seit ".STAT_START_DATE."</td></tr>\n";
	echo "</table></div>\n";
*/
}

function printStatistics($playerId, $creationDate, $privileges) {
	// Achtung: Manche Herren haben beide Rollen, aber keine Frau hat eine Herrenhallenrolle, darum geht diese Lösung!
	$isMale = true;
	if (User::authorized(User::$ROLE_INDOOR_MEN, $privileges))
		$isMale = true;
	else if(User::authorized(User::$ROLE_INDOOR_WOMEN, $privileges))
		$isMale = false;
	else {
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
			" WHERE Zeit > " . esc($creationDate) . 
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
			" WHERE Zeit > " . esc($creationDate) .
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

function printChartBar($imgBar1, $imgBar2, $percentage, $title) {
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
