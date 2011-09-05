<?php
require_once "init.php";
define('EURO_PER_KILOMETER', 0.15); // in Euro

HP::printPageHead("Fahrtkosten Indoor", "img/top_driving.png");
printPage();
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {
	if (!getUser()->isAuthorized(User::$ROLE_INDOOR_MEN | User::$ROLE_INDOOR_WOMEN)) {
		HP::printLoginError();
		return;
	}

	switch (HP::getParam('action')) {
		case 'add_or_modify_entry':
			addOrModifyEntry();
			break;
		case 'print_edit_entry':
			printToolBar();
			printAddModifyForm();
			printDrivingTable();
			break;
		case 'ask_delete_entry':
			printAskDelete();
			break;
		case 'delete_entry':
			deleteEntry();
			break;
		case 'confirm_entry':
			confirmEntry();
			break;
		case 'ask_confirm_entry':
			printAskConfirm();
			break;
		default:
			printToolBar();
			printDrivingTable();
			break;
	}	
}

function deleteEntry() {
	if (!isset($_GET['id']))
		Log::fatal("Cannot delete driving cost entry without an ID");
	if(!getUser()->isAdmin()) {
		HP::printLoginError();
		return;
	}
	
	$sql="DELETE FROM driving WHERE id=".$_GET['id'];
	
	if(getDB()->query($sql)) {
		printToolBar();
		printDrivingTable();
	}
}

function printAskDelete() {
	if (!isset($_GET['id']))
		Log::fatal("Cannot delete driving cost entry without an ID");
	printToolBar();
	
	echo "<p style='text-align:center'><b>Löschen bestätigen</b></p>";
	echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
	echo "<p style='text-align:center'>";
	echo "<input type='submit' name='Submit' value='Wirklich löschen'/>";
	echo "<input type='hidden' name='id' value='".$_GET['id']."'/>";
	echo "<input type='hidden' name='action' value='delete_entry'/>";
	echo "</p>";
	echo "</form>"; 
	echo "<hr/>";
	
	printDrivingTable($_GET['id']);
}

function addOrModifyEntry() {
	if (!getUser()->isAdmin()) {
		HP::printLoginError();
		return;
	}
	
	if ( !isset($_GET['date']) || !isset($_GET['distance']) || !isset($_GET['extra']) || !isset($_GET['note']) || !isset($_GET['userid']))
		Log::fatal("Cannot add/change driving cost entry due to missing parameters!");
	
	$_GET['state'] = isset($_GET['state']) ? 1 : 0;
	$_GET['distance'] = str_replace(',','.',$_GET['distance']); 		
	$_GET['extra'] = str_replace(',','.',$_GET['extra']);
	
	if (!is_numeric($_GET['distance']) || !is_numeric($_GET['extra']) || $_GET['date']=='' || $_GET['note']=='') {
		printToolBar();
		HP::printErrorText("Eingabedaten sind fehlerhaft!");
		printAddModifyForm();
		printDrivingTable();
		return;
	}
	
	if(isset($_GET['id']))
		$sql = "UPDATE driving SET user_id=".$_GET['userid'].", date='".$_GET['date']."', distance='".$_GET['distance']."', extra='".$_GET['extra']."', note='".$_GET['note']."', state=".$_GET['state']." WHERE id=".$_GET['id'];	
	else
		$sql = "INSERT INTO driving (user_id, date, distance, extra, note, state) VALUES (".$_GET['userid'].", '".$_GET['date']."','".$_GET['distance']."','".$_GET['extra']."', '".$_GET['note']."', ".$_GET['state'].")";

	getDB()->query($sql);
	printToolBar();
	printDrivingTable();
}

function printAddModifyForm() {
	$date = $note = $state = "";
	$distance = $extra = 0;
	if (isset($_GET['id'])) {
		$sql = "SELECT user_id, date, distance, extra, note, state FROM driving WHERE id=".$_GET['id'];
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		
		$userid=$row['user_id'];
		$date=$row['date'];
		$distance=$row['distance'];
		$extra=$row['extra'];
		$note=$row['note'];
		$state=$row['state'];
	}
	
	echo "<p style='text-align:center'><b>";
	echo isset($_GET['id']) ? "Eintragung ändern" : "Eintragung hinzufügen";
		
	echo "</b></p>";
	
	echo "<form name='accountForm' method='get' action='".$_SERVER['PHP_SELF']."' enctype='multipart/form-data'>";
	echo "<table width='100%' style='text-align:center'>";
	
	$sql = "SELECT id, lastname, firstname FROM user where id != " . User::$GUEST_ID . " ORDER BY lastname, firstname";
	$request = getDB()->query($sql);
	echo "<tr><td style='text-align:right' width='40%'>Benutzer:</td>";
	echo "<td><select name='userid'>";
	while ($row = mysql_fetch_assoc($request)) {
		echo "<option value='".$row['id']."' ".($row['id'] == $userid ? "selected='selected'" : "").">".$row['lastname']." ".$row['firstname']."</option>";
	}
	echo "</select></td></tr>";
	
	echo "<tr><td style='text-align:right' width='40%'>Datum:</td>";
	echo "<td><input type='text' readonly='readonly' name='date' size='9' value='".$date."'/>";
	echo "<a href='javascript:calDate.popup();'><img src='img/cal.gif' alt='Datum wählen'/></a></td></tr>";
	
	echo "<tr><td style='text-align:right'>Gefahrene Kilometer:</td>";
	echo "<td><input type='text' name='distance' size='10' value='".$distance."'/>KM</td></tr>";
	
	echo "<tr><td style='text-align:right'>Zusätzlicher Betrag:</td>";
	echo "<td><input type='text' name='extra' size='10' value='".$extra."'/>&euro;</td></tr>";
	
	echo "<tr><td style='text-align:right'>Bemerkung:</td>";
	echo "<td><input type='text' name='note' size='40' value='".$note."'/></td></tr>";
	
	echo "<tr><td style='text-align:right'>Abgerechnet:</td>";
	echo "<td><input type='checkbox' name='state' size='40' ".($state == 0 ? "" : "checked='checked'")."/></td></tr>";
	 
	echo "</table>";
	
	echo "<script type='text/javascript'>";
	echo "var calDate = new calendar3(document.forms['accountForm'].elements['date']);";
	echo "</script>";
	
	echo "<input type='hidden' name='action' value='add_or_modify_entry'/>";
	if (isset($_GET['id']))
		echo "<input type='hidden' name='id' value='".$_GET['id']."'/>";
	echo "<p style='text-align:center'><input type='submit' name='Submit' value='Bestätigen'/></p>";
	echo "</form>";
	echo "<hr/>";
}

function printToolBar() {
	echo "<div align='right'><a href='".$_SERVER['PHP_SELF']."?action=print_edit_entry'><img src='img/money_add.png' alt='neu' title='neuen Eintrag erzeugen'/></a></div>";
	echo "<hr/>\n";
}

function printDrivingTable($toDeleteId=NULL) {
	$sql = "SELECT driving.id as driving_id, lastname, firstname, date, note, distance, state, extra FROM driving JOIN user ON user_id=user.id ORDER BY date DESC";
	$request = getDB()->query($sql);

	echo "<table cellspacing='0' cellpadding='3' width='100%'>";
	echo "<tr>";
	echo "<th style='text-align:left'>Name</th>";
	echo "<th style='text-align:left'>Datum</th>";
	echo "<th width='100%'>Bemerkung</th>";
	echo "<th style='text-align:right'><img src='img/car.png' alt='Gefahrene Kilometer' title='Gefahrene Kilometer'/></th>";
	echo "<th style='text-align:right'><img src='img/coins.png' alt='Kosten' title='Kosten'/></th>";
	echo "<th><img src='img/checklist.gif' alt='Status' title='Status'/></th>";
	echo "<th></th><th></th>";
	echo "</tr>";

	$rowc=0;
	while ($row = mysql_fetch_assoc($request)) {
		if($toDeleteId==$row['driving_id'])
			echo "<tr class='rowColorSelected'>";
		else
			echo "<tr class='rowColor".($rowc%2)."'>";
		
		echo "<td nowrap='nowrap' style='padding-right:15px'>".HP::toHtml($row['lastname']." ".$row['firstname'])."</td>";
		echo "<td nowrap='nowrap' style='padding-right:15px'>".$row['date']."</td>";
		echo "<td>".HP::toHtml($row['note'])."</td>";
		echo "<td style='text-align:right' nowrap='nowrap'>".$row['distance']." km</td>";
		echo "<td nowrap='nowrap' style='padding-left:15px; text-align:right'>".($row['extra'] + EURO_PER_KILOMETER*$row['distance'])." &euro;</td>";
			
		echo "<td style='padding-left:15px'><img src='";
		echo $row['state'] == 0 ? "img/cross.png" : "img/ok.png";
		echo "' alt=''/></td>";
		
		echo "<td><a href='".$_SERVER['PHP_SELF']."?action=print_edit_entry&amp;id=".$row['driving_id']."'><img src='img/money_edit.png' alt='editieren' title='diesen Eintrag editieren'/></a></td>";
		echo "<td><a href='".$_SERVER['PHP_SELF']."?action=ask_delete_entry&amp;id=".$row['driving_id']."'><img src='img/money_delete.png' alt='löschen' title='diesen Eintrag löschen'/></a></td>";
			
		echo "</tr>";
		$rowc++;
	}
	echo "</table><div style='text-align:right'><font size='2'>Kilometergeld: ".EURO_PER_KILOMETER." &euro;/km</font></div><br/>";

	$sql = "SELECT lastname, firstname, SUM(distance), SUM(extra) FROM driving JOIN user ON user_id=user.id WHERE state = 0 GROUP BY lastname, firstname ORDER BY lastname, firstname DESC";
	$request = getDB()->query($sql);

	echo "<div align='right'><table cellspacing='0' cellpadding='3'>";
	echo "<tr>";
	echo "<th>Name</th>";
	echo "<th style='text-align:right'><img src='img/car.png' alt='Gefahrene Kilometer' title='Gefahrene Kilometer'/></th>";
	echo "<th style='text-align:right'><img src='img/coins.png' alt='Kosten' title='Kosten'/></th>";
	echo "</tr>";

	$km = 0;
	$money = 0;
	$rowc = 0;

	while ($row = mysql_fetch_assoc($request)) {
		$km = $km + $row['SUM(distance)'];
		$money = $money + $row['SUM(distance)']*EURO_PER_KILOMETER + $row['SUM(extra)'];

		echo "<tr class='rowColor".($rowc%2)."'>";	
		echo "<td>".HP::toHtml($row['lastname']." ".$row['firstname'])."</td>";
		echo "<td nowrap='nowrap' style='padding-left:25px; text-align:right'>".$row['SUM(distance)']." km</td>";
		echo "<td nowrap='nowrap' style='padding-left:15px; text-align:right'>".($row['SUM(distance)']*EURO_PER_KILOMETER + $row['SUM(extra)'])." &euro;</td>";
		echo "</tr>";
		$rowc++;
	}

	echo "<tr class='column_three'>";
	echo "<td><b>GESAMTBETRAG</b></td>";
	echo "<td style='text-align:right' nowrap='nowrap'><b>".$km." km</b></td>";
	echo "<td style='text-align:right' nowrap='nowrap' style='padding-left:15px'><b>".$money." &euro;</b></td>";
	echo "</tr>";

	echo "</table><font size='2'>Kilometergeld: ".EURO_PER_KILOMETER." &euro;/km</font></div><br/>";
}
?>