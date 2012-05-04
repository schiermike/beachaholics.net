<?php
require_once "init.php";
define('COLOR_OUT','#ff0000');
define('COLOR_IN','#008800');

$hp_envelope = HP::getParam('action') != 'print' && HP::getParam('action') != 'download';
if ($hp_envelope)
	HP::printPageHead("Kontoübersicht", "img/top_account.png");

printPage();

if ($hp_envelope)
	HP::printPageTail();
	
// ===================================================================
// ===================================================================
	
function printPage() {
	if (!getUser()->isVorstand()) {
		HP::printLoginError();
		return;
	}
	
	if (!isset($_SESSION['account_start_date']))
		$_SESSION['account_start_date'] = NULL;
	if (!isset($_SESSION['account_end_date']))
		$_SESSION['account_end_date'] = NULL;
	if (HP::isParamSet('start_date'))
		$_SESSION['account_start_date'] = HP::getParam('start_date');
	if (HP::isParamSet('end_date'))
		$_SESSION['account_end_date'] = HP::getParam('end_date');
	
	switch(HP::getParam('action')) {
		case 'add_or_modify_entry':
			addOrModifyEntry(HP::getParam('id'), HP::getParam('date'), HP::getParam('amount'), HP::getParam('note'));
			break;
		case 'print_edit_entry':
			printToolBar();
			printAddModifyForm(HP::getParam('id'));
			printAccountTable();
			break;
		case 'ask_delete_entry':
			printAskDelete(HP::getParam('id'));
			break;
		case 'delete_entry':
			deleteEntry(HP::getParam('id'));
			break;
		case 'confirm_entry':
			confirmEntry(HP::getParam('id'));
			break;
		case 'ask_confirm_entry':
			printAskConfirm(HP::getParam('id'));
			break;
		case 'print':
			printPrintPage();
			break;
		case 'download':
			sendDownload(HP::getParam('id'));
			break;
		case 'reset_timeframe':
			resetTimeFrame();
			break;
		default:
			printToolBar();
			printAccountTable();
			break;
	}	
}

function deleteEntry($id) {
	if (!is_numeric($id))
		return;

	$sql = "DELETE FROM account WHERE id=" . esc($id) . " AND checked_by IS NULL";
	
	if (getDB()->query($sql)) {
		printToolBar();
		printAccountTable();
	}
}

function printToolBar() {
	echo "<div style='text-align:right; padding-top: 10px;'>";
	echo "<form name='timefilterform' method='get' action='".$_SERVER['PHP_SELF']."'>";
	echo "<input type='text' readonly='readonly' name='start_date' size='9' value='" . $_SESSION['account_start_date'] . "'/>";
	echo "<a href='javascript:calStartDate.popup();'><img src='img/clock_play.png' alt='Startzeitpunkt' title='Startzeitpunkt'/></a> - ";
	echo "<input type='text' readonly='readonly' name='end_date' size='9' value='" . $_SESSION['account_end_date'] . "'/>";
	echo "<a href='javascript:calEndDate.popup();'><img src='img/clock_stop.png' alt='Endzeitpunkt' title='Endzeitpunkt'/></a> ";
	echo "&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?action=reset_timeframe'><img src='img/clock_delete.png' alt='Zeitfilter löschen' title='Zeitfilter löschen'/></a>";
	echo "&nbsp;&nbsp;&nbsp;";
	echo "<a href='".$_SERVER['PHP_SELF']."?action=print' target='_blank'><img src='img/print.png' alt='Auflistung drucken' title='Auflistung drucken'/></a>";
	echo "</form></div>\n";

	echo "<script type='text/javascript'>";
	echo "var tfform = document.forms['timefilterform'];"; 
	echo "var calStartDate = new calendar3(tfform.elements['start_date'], tfform);";
	echo "var calEndDate = new calendar3(tfform.elements['end_date'], tfform);";
	echo "</script><hr/>\n";
}

function confirmEntry() {
	if (!is_numeric($id))
		Log::fatal("Cannot confirm the account entry without an ID");
		
	$sql = "UPDATE account SET checked_by=" . esc(getUser()->id) . " WHERE id=" . esc($id);
	
	if (getDB()->query($sql) && mysql_affected_rows()==1) {
		printToolBar();
		printAccountTable();
	}
}

function resetTimeFrame() {
	$_SESSION['account_start_date']=NULL;
	$_SESSION['account_end_date']=NULL;
	printToolBar();	
	printAccountTable();
}

function sendDownload($id) {
	if (!is_numeric($id))
		Log::fatal("Cannot download account entry attachment an ID");
		
	$sql="SELECT attachment, attach_name, attach_type, attach_size FROM account WHERE id=" . $id;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	
	header("Content-length: " . $row['attach_size']);
	header("Content-type: " . $row['attach_type']);
	header("Content-Disposition: attachment; filename=" . $row['attach_name']);
	echo $row['attachment'];
	
	exit();
}

function printAskConfirm($id) {
	if (!is_numeric($id))
		Log::fatal("Cannot delete account entry without an ID");
	printToolBar();
	
	$sql = "SELECT lastname, firstname FROM user JOIN account ON user.id=account.created_by WHERE account.id=" . esc($id);
	$result = getDB()->query($sql);
	$row = mysql_fetch_assoc($result);
	
	echo "<p style='text-align:center'><b>Kontoeintrag von ".$row['lastname']." ".$row['firstname']." absegnen</b></p>";
	echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
	echo "<p style='text-align:center'>";
	echo "<input type='submit' name='Submit' value='Bestätigen'/>";
	echo "<input type='hidden' name='id' value='" . $id . "'/>";
	echo "<input type='hidden' name='action' value='confirm_entry'/>";
	echo "</p>";
	echo "</form>"; 
	echo "<br/>";
	
	printAccountTable($id);
}

function printAskDelete($id) {
	if (!is_numeric($id))
		Log::fatal("Cannot delete account entry without an ID");
	printToolBar();
	
	echo "<p style='text-align:center'><b>Löschen bestätigen</b></p>";
	echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
		echo "<p style='text-align:center'>";
		echo "<input type='submit' name='Submit' value='Wirklich löschen'/>";
		echo "<input type='hidden' name='id' value='" . $id . "'/>";
		echo "<input type='hidden' name='action' value='delete_entry'/>";
		echo "</p>";
	echo "</form>"; 
	echo "<hr/>";
	
	printAccountTable($id);
}

function addOrModifyEntry($id, $date, $amount, $note) {
	if ($date == NULL || $amount == NULL || $note == NULL)
		Log::fatal("Not sufficient parameters for account add/change operation");

	$amount=str_replace(',','.',$amount);	
	if (!is_numeric($amount) || $date=='' || $note=='') {
		printToolBar();
		HP::printErrorText("Eingabedaten sind fehlerhaft!");
		printAddModifyForm($id);
		printAccountTable();
		return;
	}
	
	$attachment="NULL";
	$attachmentName="NULL";
	$attachmentType="NULL";
	$attachmentSize="NULL";
	if ($_FILES['attached']['size'] > 0) {
		$fp = fopen($_FILES['attached']['tmp_name'], 'r');
		$attachment = fread($fp, filesize($_FILES['attached']['tmp_name']));
		fclose($fp);
		$attachment = esc($attachment);
		$attachmentName = esc($_FILES['attached']['name']);
		$attachmentType = esc($_FILES['attached']['type']);
		$attachmentSize = esc($_FILES['attached']['size']);
	}

	if (is_numeric($id))
		$sql = "UPDATE account SET date=" . esc($date) . ", note=" . esc($note) . ", amount=" . esc($amount) . 
		", attachment=" . $attachment .	", attach_name=" . $attachmentName . ", attach_type=" . $attachmentType . 
		", attach_size=" . $attachmentSize . ", created_by=" . esc(getUser()->id) . ", checked_by=NULL WHERE id=" . esc($id);
	else
		$sql = "INSERT INTO account (date, note, amount, created_by, attachment, attach_name, attach_type, attach_size) 
		VALUES (" . esc($date) . "," . esc($note) . "," . esc($amount) . "," . esc(getUser()->id) . "," . $attachment . "," . 
		$attachmentName . "," . $attachmentType . "," . $attachmentSize . ")";
	
	if (getDB()->query($sql) && mysql_affected_rows()==1) {
		printToolBar();
		printAccountTable();
	}
}

function printAddModifyForm($id) {
	$date = "";
	$amount = "";
	$note = "";
	if (is_numeric($id)) {
		$sql = "SELECT date, note, amount FROM account WHERE id=" . esc($id);
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		$date = $row['date'];
		$amount = $row['amount'];
		$note = $row['note'];
	}

	echo "<p style='text-align:center'><b>";
	echo is_numeric($id) ? "Kontoeintrag ändern" : "Kontoeintrag hinzufügen";
		
	echo "</b></p>";
	
	echo "<form name='accountForm' method='post' action='" .$_SERVER['PHP_SELF'] . "' enctype='multipart/form-data'>";
	echo "<table width='100%' style='text-align:center'>";
	
	echo "<tr><td style='text-align:right' width='30%'>Zeit:</td>";
	echo "<td><input type='text' readonly='readonly' name='date' size='9' value='" . $date . "'/>";
	echo "<a href='javascript:calDate.popup();'><img src='img/cal.gif' alt='Datum wählen'/></a></td></tr>";
	
	echo "<tr><td style='text-align:right'>Betrag:</td>";
	echo "<td><input type='text' name='amount' size='10' value='" . $amount . "'/>&euro;</td></tr>";
	
	echo "<tr><td style='text-align:right'>Bemerkung:</td>";
	echo "<td><textarea name='note' rows='6' cols='60'/>" . $note . "</textarea>";
	echo "</td></tr>";
	
	echo "<tr><td style='text-align:right'>Beleg (optional):</td>";
	echo "<td><input type='hidden' name='MAX_FILE_SIZE' value='2000000'>";
	echo "<input name='attached' type='file' id='attached' size='30'></td></tr>";
	 
	echo "</table>";
	
	echo "<script type='text/javascript'>";
	echo "var calDate = new calendar3(document.forms['accountForm'].elements['date']);";
	echo "</script>";
	
	echo "<input type='hidden' name='action' value='add_or_modify_entry'/>";
	if (is_numeric($id))
		echo "<input type='hidden' name='id' value='" . $id . "'/>";
	echo "<p style='text-align:center'><input type='submit' name='Submit' value='Bestätigen'/></p>";
	echo "</form>";
	echo "<hr/>";
}

function printPrintPage() {
	HP::printDocumentHead();
	echo "<head>\n";
	echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>\n";
	echo "<title>Kontoübersicht drucken</title>\n";
	echo "<style type='text/css'>";
	echo "body {font-size:x-small;}\n";
	echo "td {font-size:small;}\n";
	echo "th {font-size:small;}\n";
	echo "</style></head>\n";
	echo "<body>\n";
	echo "<h2 style='text-align:center'>Beachaholics Kontoübersicht</h2><br/>\n";
	
	$sql = "SELECT id, date, note, amount FROM account ";
	if ($_SESSION['account_start_date'] != NULL)
		$sql .= "WHERE date>=" . esc($_SESSION['account_start_date']);
	if ($_SESSION['account_end_date'] != NULL) {
		$sql .= $_SESSION['account_start_date'] == NULL ? "WHERE " : "AND ";
		$sql .= " date<=" . esc($_SESSION['account_end_date']);
	}
	$sql .= " ORDER BY date, note";
	
	$zeitraum = "von " . ($_SESSION['account_start_date'] == NULL ? "Anfang" : $_SESSION['account_start_date']);
	$zeitraum .= " bis " . ($_SESSION['account_end_date'] == NULL ? "Ende" : $_SESSION['account_end_date']);	
	
	$request = getDB()->query($sql);
	
	echo "<div style='text-align:right'>User: ".getUser()->getName()."<br/>";
	echo "generiert: " . HP::getPHPTime() . "<br/>";
	echo "Zeitraum: " . $zeitraum; 
	echo "</div><hr/>\n";
	
	echo "<table width='100%'>\n";
	echo "<tr><th>Datum</th>";
	echo "<th style='padding-left:30px;' width='100%'>Vermerk</th>";
	echo "<th style='padding-right:30px; text-align:right'>Betrag</th>";
	echo "<th style='text-align:right'>Summe</th></tr>\n";
	
	$output="";
	$account=0;
	while ($row = mysql_fetch_assoc($request)) {
		$rowOutput="<tr>";
		
		$rowOutput.="<td>".$row['date']."</td>";
		$rowOutput.="<td style='padding-left:30px;'>".$row['note']."</td>";
			
		$color= $row['amount']<0 ? COLOR_OUT : COLOR_IN;
		$rowOutput.="<td style='padding-right:30px; text-align:right' nowrap='nowrap'><font color='".$color."'>".echoMoney($row['amount'])."</font></td>";
			
		$account+=$row['amount'];
		$color= $account<0 ? COLOR_OUT : COLOR_IN;
		$rowOutput.="<td style='text-align:right' nowrap='nowrap'><font color='".$color."'>".echoMoney($account)."</font></td>";
		
		$rowOutput.="</tr>\n";
		
		$output = $rowOutput.$output;
	}
	echo $output;
	
	echo "</table><br/><hr/>";
	
	printAccountSummary();
	
	echo "<script type='text/javascript'>window.print();</script></body></html>";
}

function printAccountTable($toDeleteId=NULL) {	
	$sql = "SELECT account.id as account_id, date, note, amount, attach_name, created_by, u1.nickname as created_by_nick, checked_by, u2.nickname AS checked_by_nick ";
	$sql .= "FROM account JOIN user u1 ON u1.id=created_by LEFT JOIN user u2 ON u2.id=checked_by ";
	if ($_SESSION['account_start_date'] != NULL)
		$sql .= "WHERE date>=" . esc($_SESSION['account_start_date']);
	if ($_SESSION['account_end_date'] != NULL) {
		$sql .= $_SESSION['account_start_date'] == NULL ? "WHERE " : "AND ";
		$sql .= " date<=" . esc($_SESSION['account_end_date']);
	}
	$sql .= " ORDER BY date, note";
	$request = getDB()->query($sql);

	echo "<table cellspacing='0' cellpadding='3' width='100%'>";
	echo "<tr>";
	echo "<th>Datum</th>";
	echo "<th style='padding-left:10px;' width='100%'>Vermerk</th>";
	echo "<th style='padding-right:10px; text-align:right'>Betrag</th>";
	echo "<th style='padding-right:10px; text-align:right'>Summe</th>";
	echo "<th/><th/>";
	echo "<th><a href='" . $_SERVER['PHP_SELF'] . "?action=print_edit_entry'><img src='img/money_add.png' alt='neu' title='neuen Kontoeintrag anlegen'/></a></th>";
	echo "<th/>";
	echo "</tr>";

	$output="";
	$account=0;
	while ($row = mysql_fetch_assoc($request)) {
		$rowOutput="";
		if ($toDeleteId==$row['account_id'])
			$rowOutput.="<tr class='rowColorSelected'>";
		else if ($row['amount'] < 0)
			$rowOutput.="<tr class='rowColor0'>";
		else
			$rowOutput.="<tr class='rowColor1'>";
		
		$rowOutput.="<td style='white-space:nowrap;'>".$row['date']."</td>";
		$rowOutput.="<td style='padding-left:10px;'>".nl2br($row['note'])."</td>";
			
		$color= $row['amount']<0 ? COLOR_OUT : COLOR_IN;
		$rowOutput.="<td style='padding-right:10px; text-align:right' nowrap='nowrap'><font color='".$color."'>".echoMoney($row['amount'])."</font></td>";
			
		$account+=$row['amount'];
		$color= $account<0 ? COLOR_OUT : COLOR_IN;
		$rowOutput.="<td style='padding-right:10px; text-align:right' nowrap='nowrap'><font color='".$color."'>".echoMoney($account)."</font></td>";
			
		if ($row['attach_name'])
			$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?action=download&amp;id=".$row['account_id']."'><img src='img/attached.gif' alt='' title='".$row['attach_name']."'/></a></td>";
		else
			$rowOutput.="<td></td>";
			
		$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?action=print_edit_entry&amp;id=".$row['account_id']."'><img src='img/money_edit.png' alt='editieren' title='diesen Kontoeintrag editieren'/></a></td>";
		if ($row['checked_by']==NULL)
			$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?action=ask_delete_entry&amp;id=".$row['account_id']."'><img src='img/money_delete.png' alt='löschen' title='diesen Kontoeintrag löschen'/></a></td>";
		else
			$rowOutput.="<td></td>";
			
		if ($row['checked_by']==NULL) {
			if ($row['created_by'] == getUser()->id)
				$rowOutput.="<td><img src='img/warn.png' alt='pending' title='Eintrag muss zuerst von einem anderen Vorstandsmitglied abgesegnet werden'/></td>";
			else
				$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?action=ask_confirm_entry&amp;id=".$row['account_id']."'><img src='img/warn_go.png' alt='markieren' title='Eintrag von ".$row['created_by_nick']." als geprüft markieren'/></a></td>";
		}
		else
			$rowOutput.="<td><img src='img/ok.png' alt='ok' title='Eintrag von ".$row['created_by_nick']." geprüft von ".$row['checked_by_nick']."'/></td>";
			
		$rowOutput.="</tr>\n";
		
		$output = $rowOutput.$output;
	}
	echo $output;
	echo "</table>";
	
	echo "<hr/>";
	
	printAccountSummary();
	echo "<br/>";
}

function printAccountSummary() {
	$sql = "SELECT 'Ausgaben' AS name, SUM(amount) AS amount FROM account WHERE amount<0 
		UNION SELECT 'Einnahmen' AS name, SUM(amount) AS amount FROM account WHERE amount>0";
	$request = getDB()->query($sql);
	$in=0;
	$out=0;
	while ($row = mysql_fetch_assoc($request)) {
		switch ($row['name']) {
			case 'Ausgaben':
				$out=$row['amount'];
				break;
			case 'Einnahmen':
				$in=$row['amount'];
				break;
		}
	}
	
	echo "<div align='right'><table style='font-weight:bold' width='300' cellpadding='0' cellspacing='0'>";
	echo "<tr><td style='text-align:right'><font color='".COLOR_IN."'>Summe Einnahmen:</font></td>";
	echo "<td style='text-align:right'><font color='".COLOR_IN."'>".echoMoney($in)."</font></td></tr>";
	echo "<tr><td style='text-align:right'><font color='".COLOR_OUT."'>Summe Ausgaben:</font></td>";
	echo "<td style='text-align:right'><font color='".COLOR_OUT."'>".echoMoney($out)."</font></td></tr>";
	echo "<tr><td style='text-align:right'>Kontostand:</td>";
	echo "<td style='text-align:right'>".echoMoney($in+$out)."</td></tr>";
	echo "</table></div>";
}

function echoMoney($amount) {
	return money_format('%!-10#6.2i', $amount)."&nbsp;&euro;";
}
?>
