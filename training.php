<?php

	require_once "init.php";
	define('SHOW_OLD_TRAININGS_UNTIL', 60*60*24*7*2);
	
	HP::printPageHead("Events", "img/top_training.png");

	printPage();

	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{
		global $type;
		global $trainingid;
		global $playerid;
		global $grund;
		global $event_type;
		global $datetime;
		global $enddatetime;
		global $location;
		global $comment;
		global $link;
		global $start_date_day;
		global $start_date_hour;
		global $start_date_minute;
		global $set_end_date;
		global $end_date_day;
		global $end_date_hour;
		global $end_date_minute;
		
		$start_date = NULL;
		if(isset($start_date_day) && isset($start_date_hour) && isset($start_date_minute))
			$start_date = $start_date_day." ".$start_date_hour.":".$start_date_minute.":00";

		$end_date=NULL;
		if(isset($set_end_date) && isset($end_date_day) && isset($end_date_hour) && isset($end_date_minute))
			$end_date = $end_date_day." ".$end_date_hour.":".$end_date_minute.":00";
		
		if(!isset($playerid))
			$playerid = getUser()->id;
			
		switch($type)
		{
			case 'unsubscribe_step1':
				printUnsubscribeForm($trainingid, $playerid);
				break;
			case 'unsubscribe_step2':
				unsubscribe($trainingid, $grund, $playerid);
				break;
			case 'subscribe':
				resubscribe($playerid,$trainingid);
				break;
			case 'show_details':	
				printDetailsTable($trainingid);
				break;
			case 'delete_event':
				printDeleteConfirmation($trainingid);
				break;
			case 'delete_event_confirmed':
				deleteTrainingEvent($trainingid);
				break;
			case 'addmodify_event':
				printAddModifyForm($trainingid, $event_type, $datetime, $enddatetime, $location, $comment);
				break;
			case 'addmodify_event_confirmed':
				addModifyTrainingEvent($trainingid, $start_date, $end_date, $location, $comment, $link, $event_type);
				break;
			default: 
				printTrainingTable();
				break;
		}
	}
	
	function addModifyTrainingEvent($trainingid, $startDate, $endDate, $location, $comment, $link, $eventType)
	{
		if(!Event::userCanModify($eventType) )
			return;
			
		if($startDate == "" || $location == "")
		{
			echo "<br/>";
			HP::printErrorText("Fehlerhafte Eingabedaten - bitte korrigieren!");
			echo "<br/>";
			printAddModifyForm($trainingid, $eventType, $startDate, $endDate, $location, $comment, $link);
			return;
		}
		
		$endDate = $endDate == NULL ? "NULL" : "'$endDate'";
		$link = $link == NULL ? "NULL" : "'$link'";
		
		if($trainingid==NULL)
		{
			$result = getDB()->query("INSERT IGNORE INTO Events (Zeit, EndZeit, Ort, Bemerkung, Typ, Link) VALUES ('$startDate', $endDate, '$location', '$comment', $eventType, $link)");
			$trainingid = mysql_insert_id();
		}
		else
		{
			/**
			 * Typ kann nicht geändert werden, da sonst Chaos
			 */
			getDB()->query("UPDATE Events SET Zeit='$startDate', EndZeit=$endDate, Ort='$location', Bemerkung='$comment', Link=$link WHERE EventID=$trainingid");
		}
			
		if(mysql_affected_rows() == 1 && $eventType == Event::$BEACH)
		{
			getDB()->query("INSERT INTO Abmeldung(EventID, SpielerID, Zeitpunkt, Grund) 
				SELECT EventID, s.SpielerID, NULL, '' FROM Spieler s JOIN Events t 
				WHERE t.EventID=".$trainingid." AND s.Rights & ".User::$ROLE_BEACHAHOLIC." AND s.SpielerID!=".getUser()->id);
		}
		
		if(mysql_affected_rows() == 1 && $eventType == Event::$OTHER)
		{
			getDB()->query("INSERT INTO Abmeldung(EventID, SpielerID, Zeitpunkt, Grund) 
				SELECT t.EventID, s.SpielerID, NULL, '' FROM Spieler s JOIN Events t 
				WHERE t.EventID=".$trainingid." AND s.Rights & ".User::$ROLE_MEMBER);
		}
		
		printTrainingTable();
	}
	
	function printAddModifyForm($id=NULL, $type=NULL, $datetime=NULL, $enddatetime=NULL, $location=NULL, $comment=NULL, $link=NULL)
	{
		if($id!==NULL && $type===NULL)
		{
			$sql = "SELECT Ort, Zeit, Bemerkung, Typ, EndZeit, Link FROM Events WHERE EventID=".$id;
			$request = getDB()->query($sql);
			$row = mysql_fetch_assoc($request);
			$location=$row['Ort'];
			$datetime=strtotime($row['Zeit']);
			$enddatetime=strtotime($row['EndZeit']);
			$link=$row['Link'];
			$comment=$row['Bemerkung'];
			$type=$row['Typ'];
		}
		
		if($id!==NULL && !Event::userCanModify($type) )
			return;
		
		echo "<p style='text-align:center'><b>";
		echo $id === NULL ? "Event hinzufügen" : "Event ändern";
		echo "</b></p>";
		
		echo "<form name='EventForm' method='get' action='".$_SERVER['PHP_SELF']."'>\n";
		echo "<table width='100%' style='text-align:center'>\n";
		
		echo "<tr><td style='text-align:right'>Eventtyp:</td>";
		echo "<td width='60%'>\n";
		
		echo "<select name='event_type' id='select' ".($id != NULL ? "disabled='disabled'" : "").">";
		foreach(Event::getEvents() as $event)
		{
			// nur admins können andere events als beach-events setzen
			if(!getUser()->isAdmin() && $event != Event::$BEACH)
				continue;
				
			echo "<option value='".$event."' ".($event == $type ? "selected='selected'" : "").">".Event::toString($event)."</option>";
		}
		echo "</select>\n";
		echo "</td></tr>\n";
		
		echo "<tr><td style='text-align:right'>Startzeit:</td>";
		echo "<td>";
		$dateParsed= $datetime == NULL ? getdate() : getdate($datetime);
		echo "<input type='text' readonly='readonly' name='start_date_day' size='10' value='".$dateParsed['year']."-".$dateParsed['mon']."-".$dateParsed['mday']."'/><a href='javascript:cal1.popup();'><img src='img/cal.gif' alt=''/></a>";
		echo "&nbsp;&nbsp;";
		echo "<select name='start_date_hour' id='start_date_hour'>";
		for($i=0;$i<24;$i++)
			echo "<option value='".$i."' ".($i == $dateParsed['hours'] ? "selected='selected'" : "").">".$i."</option>";
		echo "</select> ";
		echo ":";
		echo "<select name='start_date_minute' id='start_date_minute'>";
		for($i=0;$i<60;$i+=5)
			echo "<option value='".$i."' ".($i == $dateParsed['minutes'] ? "selected='selected'" : "").">".$i."</option>";
		echo "</select> ";
		echo "</td></tr>\n";
		
		echo "<tr><td style='vertical-align: top; text-align:right'>Endzeit:</td>";
		echo "<td>";
		echo "<input type='checkbox' name='set_end_date' value='Endzeit setzen' ".($enddatetime == NULL ? "" : "checked='checked'")."/>";
		echo "<br/>";
		$dateParsed= $enddatetime == NULL ? getdate() : getdate($enddatetime);
		echo "<input type='text' readonly='readonly' name='end_date_day' size='10' value='".$dateParsed['year']."-".$dateParsed['mon']."-".$dateParsed['mday']."'/><a href='javascript:cal2.popup();'><img src='img/cal.gif' alt=''/></a>";
		echo "&nbsp;&nbsp;";
		echo "<select name='end_date_hour' id='end_date_hour'>";
		for($i=0;$i<24;$i++)
			echo "<option value='".$i."' ".($i == $dateParsed['hours'] ? "selected='selected'" : "").">".$i."</option>";
		echo "</select> ";
		echo ":";
		echo "<select name='end_date_minute' id='end_date_minute'>";
		for($i=0;$i<60;$i+=5)
			echo "<option value='".$i."' ".($i == $dateParsed['minutes'] ? "selected='selected'" : "").">".$i."</option>";
		echo "</select> ";
		echo "</td></tr>\n";
		
		echo "<tr><td style='text-align:right'>Örtlichkeit:</td>";
		echo "<td><input type='text' name='location' size='25' value='".HP::toHtml($location)."'/>";
		echo "</td></tr>\n";
		
		echo "<tr><td style='text-align:right'>Bemerkung:</td>";
		echo "<td><textarea name='comment' rows='3' cols='60'>".HP::toHtml($comment)."</textarea>";
		echo "</td></tr>\n";
		
		echo "<tr><td style='text-align:right'>Link:</td>";
		echo "<td><input type='text' name='link' size='40' value='$link'/>";
		echo "</td></tr>\n";
		 
		echo "</table>";
		if($id != NULL)
			echo "<input type='hidden' name='trainingid' value='".$id."'/>";
			
		echo "<input type='hidden' name='type' value='addmodify_event_confirmed'/>";
		echo "<br/>";
		echo "<p style='text-align:center'>";
		echo "<input type='submit' name='Submit' value='Bestätigen'/></p>";
		echo "</form>\n";
		echo "<br/><br/>";
		
		echo "<script type='text/javascript'>";
		echo "var cal1 = new calendar3(document.forms['EventForm'].elements['start_date_day']);";
		echo "cal1.year_scroll = true;";
		echo "cal1.time_comp = false;";
		echo "</script>";
		
		echo "<script type='text/javascript'>";
		echo "var cal2 = new calendar3(document.forms['EventForm'].elements['end_date_day']);";
		echo "cal2.year_scroll = true;";
		echo "cal2.time_comp = false;";
		echo "</script>";
	}
	
	function printDeleteConfirmation($trainingid)
	{
		$sql = "SELECT Zeit, Ort, Bemerkung, Typ FROM Events WHERE EventID=".$trainingid;
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		$zeit = HP::formatDate($row['Zeit']);
		
		if(!Event::userCanModify($row['Typ']) )
			return;
		
		echo "<p style='text-align:center'><b>Löschen bestätigen</b></p>";
		echo "<p style='text-align:center'>Event am <b>".$zeit."</b> in <b>".$row['Ort']."</b><br/>".$row['Bemerkung']."</p>";
		echo "<br/><br/>";
		echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
			echo "<p style='text-align:center'>";
			echo "<font size='1'>Alle existierenden Abmeldungen werden mitgelöscht!</font>";
			echo "<br/>";
			echo "<input type='submit' name='Submit' value='Wirklich löschen'>";
			echo "<input type='hidden' name='trainingid' value='".$trainingid."'>";
			echo "<input type='hidden' name='type' value='delete_event_confirmed'>";
		echo "</form>"; 
		echo "</p>";
		echo "<br/><br/><br/>";
	}
	
	function deleteTrainingEvent($trainingid)
	{
		$result = getDB()->query("SELECT Typ FROM Events WHERE EventID=".$trainingid);
		list($eventType) = mysql_fetch_row($result);
		if(!Event::userCanModify($eventType) )
			return;
			
		$sql = "DELETE FROM Abmeldung WHERE EventID=".$trainingid;
		$request = getDB()->query($sql);
		$sql = "DELETE FROM Events WHERE EventID=".$trainingid;
		$request = getDB()->query($sql);
		
		printTrainingTable();
	}
	
	function printUnsubscribeForm($trainingid, $playerid)
	{
		if($playerid != getUser()->id && !getUser()->isAdmin())
			return;
			
			
		$sql = "SELECT Nachname, Vorname, Typ FROM Spieler JOIN Events WHERE SpielerID=".$playerid." AND EventID=".$trainingid;
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		
		if(!Event::userCanJoin($row['Typ']) )
			return;

		echo "<p style='text-align:center'>".HP::toHtml($row['Nachname']." ".$row['Vorname'])."</p>";
		echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
		echo "<p style='text-align:center'><textarea name='grund' cols='80' rows='4'>Bitte hier Begründung anführen</textarea></p>";
		echo "<p style='text-align:center'>";
		echo "<input type='submit' name='Submit' value='Abmeldung abschicken'/>";
		echo "<input type='hidden' name='trainingid' value='".$trainingid."'/>";
		echo "<input type='hidden' name='playerid' value='".$playerid."'/>";
		echo "<input type='hidden' name='type' value='unsubscribe_step2'/>";
		echo "</p></form>";
	}
	
	function unsubscribe($trainingid, $grund, $playerid)
	{
		$result = getDB()->query("SELECT Typ FROM Events WHERE EventID=".$trainingid);
		list($eventType) = mysql_fetch_row($result);
		if(!Event::userCanJoin($eventType) )
			return;
			
		$sql = "INSERT IGNORE INTO Abmeldung (EventID, SpielerID, Zeitpunkt, Grund) VALUES (".$trainingid.",".$playerid.",'".HP::getPHPTime()."','".$grund."')";
		$request = getDB()->query($sql);
		
		if($playerid == getUser()->id)
			printTrainingTable();
		else
			printDetailsTable($trainingid);
	}

	function resubscribe($playerid, $trainingid)
	{
		$result = getDB()->query("SELECT Typ FROM Events WHERE EventID=".$trainingid);
		list($eventType) = mysql_fetch_row($result);
		if(!Event::userCanJoin($eventType) )
			return;
			
		$sql = "DELETE FROM Abmeldung WHERE EventID=".$trainingid." AND SpielerID=".$playerid;
		$request = getDB()->query($sql);

		if($playerid == getUser()->id)
			printTrainingTable();
		else
			printDetailsTable($trainingid);
	}
	
	function printTrainingTable()
	{
		echo "<table width='100%' cellspacing='0' cellpadding='0' id='training'>";
		echo "<tr>\n";
		echo "<th nowrap='nowrap' style='text-align:left'>Datum / Uhrzeit</th>\n";
		echo "<th nowrap='nowrap' style='text-align:left'>Halle / Ort</th>\n";
		echo "<th width='100%'>Bemerkung</th>\n";
		echo "<th/>\n";
		echo "<th><img src='img/spieler.png' title='Angemeldete Spieleranzahl' alt=''/></th>\n";
		
		if(getUser()->isAdmin())
		{	
			echo "<th><img src='img/anmeldung_header.png' alt='' title='An/Abmeldung'/></th>\n";
		}
		if(getUser()->isAuthorized(User::$ROLE_BEACHAHOLIC | User::$ROLE_ADMIN))
			echo "<th colspan='2' style='text-align:center'><a href='".$_SERVER['PHP_SELF']."?type=addmodify_event'><img src='img/calendar_add.png' alt='' title='Event hinzufügen'/></a></th>\n";

		echo "</tr>\n";
		
		$spielerID = getUser()->id == NULL ? 0 : getUser()->id;
		$sql = "SELECT EventID, Ort, Zeit, Bemerkung, Typ, EndZeit, Link, SUM(SpielerAll) AS AnzahlSpieler, SUM(SpielerOff) AS AnzahlAbgemeldet, SUM(isSpielerOff) AS binAbgemeldet FROM ".
			"( ".
				"(SELECT EventID, t.Ort, Zeit, Bemerkung, Typ, EndZeit, Link, s.SpielerID, 1 AS SpielerAll, 0 AS SpielerOff, 0 AS isSpielerOff ".
				"FROM Events t JOIN Spieler s ".
				"WHERE s.CreationDate < t.Zeit AND (". 
				"s.Rights & ".User::$ROLE_INDOOR_MEN." AND (t.Typ=".Event::$GAME_MEN." OR t.Typ=".Event::$INDOOR_MEN.") OR ".
				"s.Rights & ".User::$ROLE_INDOOR_WOMEN." AND (t.Typ=".Event::$GAME_WOMEN." OR t.Typ=".Event::$INDOOR_WOMEN.") OR ".
				"s.Rights & ".User::$ROLE_BEACHAHOLIC." AND t.Typ=".Event::$BEACH." OR ".
				"s.Rights > 0 AND t.Typ=".Event::$OTHER.") OR t.Typ=".Event::$EXTERN.
				" ) UNION ( ".
				"SELECT t.EventID, t.Ort, Zeit, Bemerkung, Typ, EndZeit, Link, s.SpielerID, 0 AS SpielerAll, 1 AS SpielerOff ,s.SpielerID=".$spielerID." AS isSpielerOff ".
				"FROM Events t JOIN Abmeldung a ON t.EventID=a.EventID JOIN Spieler s ON a.SpielerID=s.SpielerID) ".
			") AS Union1 GROUP BY EventID ORDER BY Zeit ASC";
		$request = getDB()->query($sql);
		
		global $show_all;
		$lastTrainingTableRowWasOld = true;
		while($row = mysql_fetch_assoc($request))
		{
			if(time()-SHOW_OLD_TRAININGS_UNTIL>strtotime($row['Zeit']) && $show_all != "true")
				continue;
				
			$isNewEntry=time()<strtotime($row['Zeit']);
			$isFirstNewEntry = $lastTrainingTableRowWasOld && $isNewEntry;
			$lastTrainingTableRowWasOld = !$isNewEntry;

			printTrainingTableRow($row['EventID'], $row['Typ'], $row['Zeit'], $row['Ort'], $row['Bemerkung'], $row['EndZeit'], $row['Link'], $row['AnzahlSpieler'], $row['AnzahlAbgemeldet'], $row['binAbgemeldet'], $isNewEntry, $isFirstNewEntry);
		}
		echo "</table>";
		
		echo "<br/><div align='right'>";
		echo "<table cellspacing='0' style='font-size:x-small' id='training_legend'>";
		echo "<tr><td colspan='2' style='text-align:right'><a href='icalexport.php'><img src='img/calendar.png' alt='Ical Export' title='export as Icalendar format'/></a></td></tr>";
		echo "<tr><td colspan='2'><a href='".$_SERVER['PHP_SELF']."?show_all=true' style='font-size:x-small;'>alle Termine anzeigen</a></td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 1px; border-color: black;' class='".Event::toClass(Event::$INDOOR_MEN)."'/><td>Herren Hallentraining</td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 0px 1px 1px 1px; border-color: black;' class='".Event::toClass(Event::$GAME_MEN)."'/><td>Herren Meisterschaftsspiel</td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 0px 1px 1px 1px; border-color: black;' class='".Event::toClass(Event::$INDOOR_WOMEN)."'/><td>Damen Hallentraining</td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 0px 1px 1px 1px; border-color: black;' class='".Event::toClass(Event::$GAME_WOMEN)."'/><td>Damen Meisterschaftsspiel</td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 0px 1px 0px 1px; border-color: black;' class='".Event::toClass(Event::$BEACH)."'/><td>Beach-Einheit</td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 0px 1px 0px 1px; border-color: black;' class='".Event::toClass(Event::$OTHER)."'/><td>sonstiges Event</td></tr>";
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 1px; border-color: black;' class='".Event::toClass(Event::$EXTERN)."'/><td>Externes</td></tr>";
		echo "</table><br/>";
		echo "</div>";
	}
	
	function printTrainingTableRow($trainingid, $typ, $zeit, $ort, $bemerkung, $endZeit, $link=NULL, $spieleranzahl=-1, $numAbwesend=-1, $isAbgemeldet=-1, $isNewEntry = true, $isFirstNewEntry = false)
	{
		$cclass = Event::toClass($typ);
		if($isFirstNewEntry)
			$cclass .= " topborder";
		
		echo "<tr class='".$cclass."'";
		if( Event::userCanJoin($typ) )
		echo " style='cursor:pointer;' onclick='location.href=\"".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;type=show_details\"'";
		echo ">\n";
		echo "<td nowrap='nowrap' style='padding-left: 5px; padding-right:15px'>";
			echo HP::formatDate($zeit);
			if($endZeit != NULL)
				echo "<br/><div style='text-align:center'>--- bis ---</div>".HP::formatDate($endZeit);
		echo "</td>\n";
		echo "<td nowrap='nowrap' style='padding-right:15px'>".HP::toHtml($ort)."</td>\n";
		echo "<td>".HP::toHtml($bemerkung, true)."</td>\n";
		echo "<td>";
		if($link != NULL)
		{
			echo "<a href='".$link."' target='_blank'><img src='img/url.gif' alt='Link' title='Link'/></a>";
		}
		echo "</td>\n";
		
		if($spieleranzahl<0) //kein Argument uebergeben
		{
			echo "<td/><td/><td/><td/></tr>\n";
			return;
		}
		
		// only events where players can participate
		echo "<td style='text-align: center;'>";
		if(Event::isJoinable($typ))
		{
			echo ($spieleranzahl - $numAbwesend)."/".$spieleranzahl;
		}
		echo "</td>\n";
		
		echo "<td style='text-align:center'>";
		if( Event::userCanJoin($typ) && $isNewEntry && time()+Event::getDeadline($typ)<strtotime($zeit))
		{
			if($isAbgemeldet) //anmelden
			{
				echo "<a href='".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;type=subscribe'><img  src='img/anmelden.png' alt='Anmelden' title='Anmelden'/></a>\n";
			}						
			else //abmelden
			{
				echo "<a href='".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;type=unsubscribe_step1'><img  src='img/abmelden.png' alt='Abmelden' title='Abmelden'/></a>\n";
			}
		}
		else
			echo "<img  src='img/gesperrt.png' alt='gesperrt' title='gesperrt'/>\n";
		echo "</td>\n";
		
		echo "<td style='text-align:center'>";
		if( Event::userCanModify($typ) )
		{
			echo "<a href='".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;type=addmodify_event'><img  src='img/calendar_edit.png' alt='ändern' title='Event ändern'/></a>\n";
		}
		echo "</td>\n";
					
		echo "<td style='padding-right: 5px; text-align:center'>";
		if( Event::userCanModify($typ) )
		{	
			echo "<a href='".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;type=delete_event'><img  src='img/calendar_delete.png' alt='löschen' title='Event löschen'/></a>\n";
		}
		echo "</td>\n";

		echo "</tr>\n";
	}
	
	function printDetailsTable($trainingid)
	{
		
		$sql = "SELECT Typ, Zeit, Ort, Bemerkung, EndZeit FROM Events WHERE EventID=".$trainingid;
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		
		if(!Event::userCanJoin($row['Typ']))
			return;
		
		
		
		echo "<table cellspacing='0' cellpadding='2' width='100%' id='training'>";
		printTrainingTableRow($trainingid, $row['Typ'], $row['Zeit'], $row['Ort'], $row['Bemerkung'], $row['EndZeit']);
		echo "</table>";
		
		// ---------------------------------------------------------------
		
		$sql = "SELECT s.SpielerID AS SpielerID, Nachname, Vorname, a.EventID IS NULL AS Angemeldet, Zeitpunkt, Grund ".
			"FROM (Spieler s LEFT JOIN Abmeldung a ".
			"ON s.SpielerID=a.SpielerID AND a.EventID=".$trainingid.") ".
			"JOIN Events t ON t.EventID=".$trainingid." ".
			"WHERE ";
		
		$rightSql = "";
		foreach(User::getRoles() as $role)
		{
			if(!Event::userCanJoin($row['Typ'], $role))
				continue;
			$rightSql .= " OR Rights & ".$role;
		}
		$rightSql = "(".substr($rightSql, 3).")";
		$sql .= $rightSql;
		
		$sql .= " AND s.CreationDate < t.Zeit ORDER BY a.SpielerID IS NOT NULL, a.Zeitpunkt DESC, Nachname ASC";
		
		$request = getDB()->query($sql);

		echo "<table cellpadding='5' cellspacing='0' width='100%'>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th width='100%'>Grund</th>";
		echo "<th>Zeitpunkt</th>";
		echo "<th/></tr>";

		$rowc=0;
		while($row = mysql_fetch_assoc($request))
		{
			echo "<tr class='rowColor".($rowc++%2)."'>";
				
			echo "<td nowrap='nowrap' style='padding-right:15px'>".HP::toHtml($row['Nachname']." ".$row['Vorname'])."</td>\n";
			if($row['Angemeldet'])
			{
				echo "<td/><td/><td>";
				if(getUser()->isAdmin()) 
					echo "<a href='".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;playerid=".$row['SpielerID']."&amp;type=unsubscribe_step1'><img src='img/abmelden.png' alt='abmelden' title='abmelden'/></a>";
				else
					echo "<img src='img/abmelden.png' alt='abmelden'/>";
				echo "</td>\n";
			}
			else
			{
				echo "<td>".$row['Grund']."</td>\n";
				echo "<td nowrap='nowrap'>".HP::formatDate($row['Zeitpunkt'])."</td>\n";
				echo "<td>";
				if(getUser()->isAdmin()) 
					echo "<a href='".$_SERVER['PHP_SELF']."?trainingid=".$trainingid."&amp;playerid=".$row['SpielerID']."&amp;type=subscribe'><img src='img/anmelden.png' alt='anmelden' title='anmelden'/></a>";
				else
					echo "<img src='img/anmelden.png' alt='anmelden'/>";
				echo "</td>\n";
			}
					
			echo "</tr>\n";
		}
		echo "</table>";
	}
?> 
