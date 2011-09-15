<?php

require_once "init.php";
define('SHOW_OLD_EVENTS_UNTIL', 60*60*24*7*2);

HP::printPageHead("Events", "img/top_event.png");
printPage();
HP::printPageTail();
	
// ===================================================================
// ===================================================================

function printPage() {
	$playerId = getUser()->id;
	if (HP::isParamSet('playerid'))
		$playerId = HP::getParam('playerid');
	
	$start_date = HP::getParam('start_date');
	if (HP::isParamSet('start_date_day') && HP::isParamSet('start_date_hour') && HP::isParamSet('start_date_minute'))
		$start_date = HP::getParam('start_date_day') . " " . HP::getParam('start_date_hour') . ":" . HP::getParam('start_date_minute') . ":00";

	$end_date = HP::getParam('end_date');
	if (HP::isParamSet('set_end_date') && HP::isParamSet('end_date_day') && HP::isParamSet('end_date_hour') && HP::isParamSet('end_date_minute'))
		$end_date = HP::getParam('end_date_day') . " " . HP::getParam('end_date_hour') . ":" . HP::getParam('end_date_minute') . ":00";
			
	switch(HP::getParam('action')) {
		case 'unsubscribe_step1':
			printUnsubscribeForm(HP::getParam('event_id'), $playerId);
			break;
		case 'unsubscribe_step2':
			unsubscribe(HP::getParam('event_id'), HP::getParam('reason'), $playerId);
			break;
		case 'subscribe':
			resubscribe(HP::getParam('event_id'), $playerId);
			break;
		case 'show_details':	
			printEventDetails(HP::getParam('event_id'));
			break;
		case 'delete_event':
			printDeleteConfirmation(HP::getParam('event_id'));
			break;
		case 'delete_event_confirmed':
			deleteEvent(HP::getParam('event_id'));
			break;
		case 'addmodify_event':
			printAddModifyForm(HP::getParam('event_id'), HP::getParam('event_type'), $start_date, $end_date, HP::getParam('location'), HP::getParam('comment'));
			break;
		case 'addmodify_event_confirmed':
			addModifyEvent(HP::getParam('event_id'), HP::getParam('event_type'), $start_date, $end_date, HP::getParam('location'), HP::getParam('comment'), HP::getParam('link'));
			break;
		default: 
			printEvents(HP::isParamSet('show_all') ? TRUE : FALSE);
			break;
	}
}
	
function addModifyEvent($eventId, $eventType, $startDate, $endDate, $location, $comment, $link) {
	if (!Event::userCanModify($eventType))
		return;
		
	if ($startDate == "" || $location == "") {
		echo "<br/>";
		HP::printErrorText("Fehlerhafte Eingabedaten - bitte korrigieren!");
		echo "<br/>";
		printAddModifyForm($eventId, $eventType, $startDate, $endDate, $location, $comment, $link);
		return;
	}
		
	$endDate = $endDate == NULL ? "NULL" : "'$endDate'";
	$link = $link == NULL ? "NULL" : "'$link'";
		
	if ($eventId==NULL) {
		$result = getDB()->query("INSERT IGNORE INTO event (start_time, end_time, location, description, type, link) 
			VALUES ('".$startDate."', " . $endDate . ", '".getDB()->escape($location)."', '".getDB()->escape($comment)."', " . $eventType . ", " . $link . ")");
		$eventId = mysql_insert_id();
	}
	else {
		// Typ kann nicht geändert werden, da sonst Chaos
		getDB()->query("UPDATE event SET start_time='".$startDate."', end_time=" . $endDate . ", location='".getDB()->escape($location) . 
			"', description='".getDB()->escape($comment)."', link=" . $link . " WHERE id=" . $eventId);
	}
		
	printEvents();
}
	
function printAddModifyForm($id=NULL, $type=NULL, $startDate=NULL, $endDate=NULL, $location=NULL, $comment=NULL, $link=NULL) {
	if ($id!==NULL && $type===NULL) {
		$sql = "SELECT location, start_time, description, type, end_time, link FROM event WHERE id=".$id;
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		$location=$row['location'];
		$startDate=$row['start_time'];
		$endDate=$row['end_time'];
		$link=$row['link'];
		$comment=$row['description'];
		$type=$row['type'];
	}
	$startDate = $startDate == NULL ? getdate() : getdate(strtotime($startDate));
	$endDate = $endDate == NULL ? getdate() : getdate(strtotime($endDate));
	
	if ($id!==NULL && !Event::userCanModify($type) )
		return;
		
	echo "<p style='text-align:center'><b>";
	echo $id === NULL ? "Event hinzufügen" : "Event ändern";
	echo "</b></p>";
	
	echo "<form name='EventForm' method='get' action='".$_SERVER['PHP_SELF']."'>\n";
	echo "<table width='100%' style='text-align:center'>\n";
	
	echo "<tr><td style='text-align:right'>Eventtyp:</td>";
	echo "<td width='60%'>\n";
	
	if ($id == NULL) {
		echo "<select name='event_type' id='select'>";
		foreach (Event::getEvents() as $event)
			if (Event::userCanModify($event))		
				echo "<option value='".$event."' ".($event == $type ? "selected='selected'" : "").">".Event::toString($event)."</option>";
	}
	else
		echo "<b>" . Event::toString($type) . "</b><input type='hidden' name='event_type' value = '" . $type . "'";
	echo "</select>\n";
	echo "</td></tr>\n";
	
	echo "<tr><td style='text-align:right'>Startzeit:</td>";
	echo "<td>";
	echo "<input type='text' readonly='readonly' name='start_date_day' size='10' value='".$startDate['year']."-".$startDate['mon']."-".$startDate['mday']."'/><a href='javascript:cal1.popup();'><img src='img/cal.gif' alt=''/></a>";
	echo "&nbsp;&nbsp;";
	echo "<select name='start_date_hour' id='start_date_hour'>";
	for ($i=0;$i<24;$i++)
		echo "<option value='".$i."' ".($i == $startDate['hours'] ? "selected='selected'" : "").">".$i."</option>";
	echo "</select> ";
	echo ":";
	echo "<select name='start_date_minute' id='start_date_minute'>";
	for ($i=0;$i<60;$i+=5)
		echo "<option value='".$i."' ".($i == $startDate['minutes'] ? "selected='selected'" : "").">".$i."</option>";
	echo "</select> ";
	echo "</td></tr>\n";
	
	echo "<tr><td style='vertical-align: top; text-align:right'>Endzeit:</td>";
	echo "<td>";
	echo "<input type='checkbox' name='set_end_date' value='Endzeit setzen' ".($endDate == NULL ? "" : "checked='checked'")."/>";
	echo "<br/>";
	echo "<input type='text' readonly='readonly' name='end_date_day' size='10' value='".$endDate['year']."-".$endDate['mon']."-".$endDate['mday']."'/><a href='javascript:cal2.popup();'><img src='img/cal.gif' alt=''/></a>";
	echo "&nbsp;&nbsp;";
	echo "<select name='end_date_hour' id='end_date_hour'>";
	for ($i=0;$i<24;$i++)
		echo "<option value='".$i."' ".($i == $endDate['hours'] ? "selected='selected'" : "").">".$i."</option>";
	echo "</select> ";
	echo ":";
	echo "<select name='end_date_minute' id='end_date_minute'>";
	for ($i=0;$i<60;$i+=5)
		echo "<option value='".$i."' ".($i == $endDate['minutes'] ? "selected='selected'" : "").">".$i."</option>";
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
	if ($id != NULL)
		echo "<input type='hidden' name='event_id' value='".$id."'/>";
		
	echo "<input type='hidden' name='action' value='addmodify_event_confirmed'/>";
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
	
function printDeleteConfirmation($eventId) {
	$sql = "SELECT start_time, location, description, type FROM event WHERE id=".$eventId;
	$request = getDB()->query($sql);
	$row = mysql_fetch_assoc($request);
	$startTime = HP::formatDate($row['start_time']);
	
	if (!Event::userCanModify($row['type']) )
		return;
		
	echo "<p style='text-align:center'><b>Löschen bestätigen</b></p>";
	echo "<p style='text-align:center'>Event am <b>".$startTime."</b> in <b>".$row['location']."</b><br/>".$row['description']."</p>";
	echo "<br/><br/>";
	echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
	echo "<p style='text-align:center'>";
	echo "<font size='1'>Alle existierenden Abmeldungen werden mitgelöscht!</font>";
	echo "<br/>";
	echo "<input type='submit' name='Submit' value='Wirklich löschen'>";
	echo "<input type='hidden' name='event_id' value='".$eventId."'>";
	echo "<input type='hidden' name='action' value='delete_event_confirmed'>";
	echo "</form>"; 
	echo "</p>";
	echo "<br/><br/><br/>";
}
	
function deleteEvent($eventId) {
	$result = getDB()->query("SELECT type FROM event WHERE id=".$eventId);
	list($eventType) = mysql_fetch_row($result);
	if (!Event::userCanModify($eventType) )
		return;
			
	$sql = "DELETE FROM participation WHERE event_id=".$eventId;
	$request = getDB()->query($sql);
	$sql = "DELETE FROM event WHERE id=".$eventId;
	$request = getDB()->query($sql);
	
	printEvents();
}
	
function printUnsubscribeForm($eventId, $playerId) {
	if ($playerId != getUser()->id && !getUser()->isAdmin())
		return;		
			
	$sql = "SELECT lastname, firstname, type FROM user JOIN event WHERE user.id=".$playerId." AND event.id=".$eventId;
	$request = getDB()->query($sql);
	$row = mysql_fetch_assoc($request);
	
	if (!Event::userCanJoin($row['type']) )
		return;

	echo "<p style='text-align:center'>".HP::toHtml($row['lastname']." ".$row['firstname'])."</p>";
	echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
	echo "<p style='text-align:center'><textarea name='reason' cols='80' rows='4'></textarea></p>";
	echo "<p style='text-align:center'>";
	echo "<input type='submit' name='Submit' value='Abmeldung abschicken'/>";
	echo "<input type='hidden' name='event_id' value='".$eventId."'/>";
	echo "<input type='hidden' name='playerid' value='".$playerId."'/>";
	echo "<input type='hidden' name='action' value='unsubscribe_step2'/>";
	echo "</p></form>";
}

function unsubscribe($eventId, $comment, $playerId)	{
	$result = getDB()->query("SELECT type FROM event WHERE id=".$eventId);
	list($eventType) = mysql_fetch_row($result);
	if (!Event::userCanJoin($eventType) )
		return;
			
	$sql = "INSERT IGNORE INTO participation (event_id, user_id, time, comment) VALUES (".$eventId.",".$playerId.",'".HP::getPHPTime()."','".getDB()->escape($comment)."')";
	$request = getDB()->query($sql);
	
	if ($playerId == getUser()->id)
		printEvents();
	else
		printEventDetails($eventId);
}

function resubscribe($eventId, $playerId) {
	$result = getDB()->query("SELECT type FROM event WHERE id=".$eventId);
	list($eventType) = mysql_fetch_row($result);
	if (!Event::userCanJoin($eventType) )
		return;
		
	$sql = "DELETE FROM participation WHERE event_id=".$eventId." AND user_id=".$playerId;
	$request = getDB()->query($sql);

	if ($playerId == getUser()->id)
		printEvents();
	else
		printEventDetails($eventId);
}
	
function printEvents($showAll = FALSE) {
	echo "<table width='100%' cellspacing='0' cellpadding='0' id='event'>";
	echo "<tr>\n";
	echo "<th nowrap='nowrap' style='text-align:left'>Datum / Uhrzeit</th>\n";
	echo "<th nowrap='nowrap' style='text-align:left'>Halle / Ort</th>\n";
	echo "<th width='100%'>Bemerkung</th>\n";
	echo "<th/>\n";
	if (!getUser()->isGuest()) {
		echo "<th><img src='img/spieler.png' title='Angemeldete Spieleranzahl' alt=''/></th>\n";
		echo "<th><img src='img/anmeldung_header.png' alt='' title='An/Abmeldung'/></th>\n";
		echo "<th colspan='2' style='text-align:center'><a href='".$_SERVER['PHP_SELF']."?action=addmodify_event'><img src='img/calendar_add.png' alt='' title='Event hinzufügen'/></a></th>\n";
	}
	echo "</tr>\n";
	
	$sql = "SELECT id, location, start_time, end_time, description, type, link FROM event ORDER BY start_time ASC";
	$request = getDB()->query($sql);
		
	$lastTableRowWasOld = true;
	while ($row = mysql_fetch_assoc($request)) {
		if (time()-SHOW_OLD_EVENTS_UNTIL>strtotime($row['start_time']) && !$showAll)
			continue;
				
		$isNewEntry=time()<strtotime($row['start_time']);
		$isFirstNewEntry = $lastTableRowWasOld && $isNewEntry;
		$lastTableRowWasOld = !$isNewEntry;
		printEventRow($row['id'], $row['type'], $row['start_time'], $row['end_time'], $row['location'], $row['description'], $row['link'], $isNewEntry, $isFirstNewEntry);
	}
	echo "</table>";
		
	echo "<br/><div align='right'>";
	echo "<table cellspacing='0' style='font-size:x-small' id='event_legend'>";
	echo "<tr><td colspan='2' style='text-align:right'><a href='icalexport.php'><img src='img/calendar.png' alt='Ical Export' title='export as Icalendar format'/></a></td></tr>";
	echo "<tr><td colspan='2'><a href='".$_SERVER['PHP_SELF']."?show_all=true' style='font-size:x-small;'>alle Termine anzeigen</a></td></tr>";
	foreach (Event::getEvents() as $event) {
		echo "<tr><td style='width: 20px; border-style: solid; border-width: 1px 1px 0px 1px; border-color: black;' class='" . 
			Event::toClass($event)."'/><td>" . Event::toString($event) . "</td></tr>";
	}
	echo "</tr></table><br/>";
	echo "</div>";
}
	
function printEventRow($eventId, $type, $startTime, $endTime, $location, $comment, $link=NULL, $isNewEntry = true, $isFirstNewEntry = false) {
	$cclass = Event::toClass($type);
	if ($isFirstNewEntry)
		$cclass .= " topborder";
		
	echo "<tr class='".$cclass."'";
	if (Event::userCanJoin($type) || Event::isJoinable($type) && getUser()->isItMe())
		echo " style='cursor:pointer;' onclick='location.href=\"".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;action=show_details\"'";
	echo ">\n";
	echo "<td nowrap='nowrap' style='padding-left: 5px; padding-right:15px'>";
	echo HP::formatDate($startTime);
	if ($endTime != NULL)
		echo "<br/><div style='text-align:center'>--- bis ---</div>".HP::formatDate($endTime);
	echo "</td>\n";
	echo "<td nowrap='nowrap' style='padding-right:15px'>".HP::toHtml($location)."</td>\n";
	echo "<td>".HP::toHtml($comment, true)."</td>\n";
	echo "<td>";
	if ($link != NULL)
		echo "<a href='".$link."' target='_blank'><img src='img/url.gif' alt='Link' title='Link'/></a>";
	echo "</td>\n";
			
	if (getUser()->isGuest()) {
		echo "</tr>\n";
		return;
	}
	
	$sql = "SELECT COUNT(*) AS groupsize FROM user WHERE roles & " . Event::getAllowedRolesForEvent($type) . " > 0";
	$request = getDB()->query($sql);
	$row = mysql_fetch_assoc($request);
	$groupsize = $row['groupsize'];
	$sql = "SELECT COUNT(*) AS groupsize FROM participation WHERE event_id=" . $eventId;
	$request = getDB()->query($sql);
	$row = mysql_fetch_assoc($request);
	$participation = $row['groupsize'];
	
	if (!Event::isSubscribeEvent($type))
		$participation = $groupsize - $participation;
		
	// only events where players can participate
	echo "<td style='text-align: center;'>";
	if (Event::isJoinable($type))
		echo $participation . "/" . $groupsize;
	echo "</td>\n";
		
	echo "<td style='text-align:center'>";
	if ( Event::userCanJoin($type) && $isNewEntry && time()+Event::getDeadline($type)<strtotime($startTime)) {
		$sql = "SELECT COUNT(*) AS participates FROM participation WHERE user_id=" . getUser()->id . " AND event_id=" . $eventId;
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		if ($row['participates'] == 1)
			echo "<a href='".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;action=subscribe'>";
		else
			echo "<a href='".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;action=unsubscribe_step1'>";
		if ($row['participates'] == 1 ^ Event::isSubscribeEvent($type))
			echo "<img  src='img/anmelden.png' alt='Anmelden' title='Anmelden'/>";
		else
			echo "<img  src='img/abmelden.png' alt='Abmelden' title='Abmelden'/>";
		echo "</a>\n";
	}
	else
		echo "<img  src='img/gesperrt.png' alt='gesperrt' title='gesperrt'/>\n";
	echo "</td>\n";
		
	echo "<td style='text-align:center'>";
	if ( Event::userCanModify($type) ) 
		echo "<a href='".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;action=addmodify_event'><img  src='img/calendar_edit.png' alt='ändern' title='Event ändern'/></a>\n";
	echo "</td>\n";
					
	echo "<td style='padding-right: 5px; text-align:center'>";
	if ( Event::userCanModify($type) )
		echo "<a href='".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;action=delete_event'><img  src='img/calendar_delete.png' alt='löschen' title='Event löschen'/></a>\n";
	echo "</td>\n";
	echo "</tr>\n";
}
	
function printEventDetails($eventId) {
	$sql = "SELECT type, start_time, location, description, end_time FROM event WHERE id=".$eventId;
	$request = getDB()->query($sql);
	$row = mysql_fetch_assoc($request);
	
	if (!Event::userCanJoin($row['type']) && !getUser()->isItMe())
		return;
	
	echo "<table cellspacing='0' cellpadding='2' width='100%' id='event'>";
	printEventRow($eventId, $row['type'], $row['start_time'], $row['end_time'], $row['location'], $row['description']);
	echo "</table>";
	
	// ---------------------------------------------------------------
	
	$sql = "SELECT user.id as userid, lastname, firstname, p.event_id IS NULL AS notpart, time, comment ".
		"FROM (user LEFT JOIN participation p ".
		"ON user.id=p.user_id AND p.event_id=".$eventId.") ".
		"JOIN event ON event.id=".$eventId." ".
		"WHERE ";
	
	$rightSql = "";
	foreach (User::getRoles() as $role) {
		if(!Event::userCanJoin($row['type'], $role))
			continue;
		$rightSql .= " OR roles & ".$role;
	}
	$rightSql = "(".substr($rightSql, 3).")";
	$sql .= $rightSql;
	
	$sql .= " AND creation_date < start_time ORDER BY p.user_id IS ";
	if (!Event::isSubscribeEvent($row['type']))
		$sql .= "NOT ";
	$sql .= "NULL, p.time DESC, lastname ASC";

	$request = getDB()->query($sql);
	echo "<table cellpadding='5' cellspacing='0' width='100%'>";
	echo "<tr>";
	echo "<th>Name</th>";
	echo "<th width='100%'>Grund</th>";
	echo "<th>Zeitpunkt</th>";
	echo "<th/></tr>";
	$rowc=0;
	while ($row = mysql_fetch_assoc($request)) {
		echo "<tr class='rowColor".($rowc++%2)."'>";
			
		echo "<td nowrap='nowrap' style='padding-right:15px'>".HP::toHtml($row['lastname']." ".$row['firstname'])."</td>\n";
		if ($row['notpart']) {
			echo "<td/><td/><td>";
			if(getUser()->isAdmin()) 
				echo "<a href='".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;playerid=".$row['userid']."&amp;action=unsubscribe_step1'><img src='img/abmelden.png' alt='abmelden' title='abmelden'/></a>";
			else
				echo "<img src='img/abmelden.png' alt='abmelden'/>";
			echo "</td>\n";
		}
		else {
			echo "<td>".$row['comment']."</td>\n";
			echo "<td nowrap='nowrap'>".HP::formatDate($row['time'])."</td>\n";
			echo "<td>";
			if(getUser()->isAdmin()) 
				echo "<a href='".$_SERVER['PHP_SELF']."?event_id=".$eventId."&amp;playerid=".$row['userid']."&amp;action=subscribe'><img src='img/anmelden.png' alt='anmelden' title='anmelden'/></a>";
			else
				echo "<img src='img/anmelden.png' alt='anmelden'/>";
			echo "</td>\n";
		}
		
		echo "</tr>\n";
	}
	echo "</table>";
}
?> 
