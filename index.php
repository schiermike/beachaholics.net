<?php

require_once "init.php";

HP::printPageHead("Beachaholics.net");
printPage();
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {
	if (!getUser()->isAdmin()) {
		printText();
		return;
	}
	
	switch(HP::getParam('action')) {
		case 'edit':
			printEditField(HP::getParam('date'));
			break;
		case 'confirm':
			confirm(HP::getParam('content'));
		default:
			printText();
	}
}

function printEditField($date) {
	echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";

	$result = NULL;
	if ($date != NULL)
		$result = getDB()->query("SELECT text FROM content WHERE date=" . esc($date));
	else
		$result = getDB()->query("SELECT text FROM content WHERE date=(SELECT MAX(date) FROM content)");
	$row = mysql_fetch_assoc($result);

	echo "<center><textarea id='content' name='content' style='width:98%' cols='1' rows='30'>" . $row['text'] . "</textarea></center>";
	echo "<div style='text-align:right'>Text vom ";
	echo "<select name='date' onChange='window.location=\"?action=edit&date=\" + this.value'>";
	$result = getDB()->query("SELECT date FROM content ORDER BY date DESC");
	while ($row = mysql_fetch_assoc($result)) {
		echo "<option value='" . $row['date'] . "'";
		if ($date == $row['date'])
			echo "selected='selected'";
		echo ">" . $row['date'] . "</option>";
	}
	echo "</select>";
	echo " laden | ";
	echo "<input type='submit' value='Speichern'/>";
	echo "</div>";

	echo "<input type='hidden' name='action' value='confirm'/>\n";
	echo "</form>";
}

function confirm($content) {
	getDB()->query("DELETE FROM content WHERE pagename='start' AND date=DATE(NOW())");
	getDB()->query("INSERT INTO content (pagename, date, text) VALUES ('start', DATE(NOW()), " . esc($content) . ")");
}	

function printText() {
	$result = getDB()->query("SELECT text FROM content WHERE date=(SELECT MAX(date) FROM content)");
	$row = mysql_fetch_assoc($result);
	echo $row['text'];

	if (getUser()->isAdmin()) {
		echo "<div style='text-align:right'>";
		echo "<a href='" . $_SERVER['PHP_SELF'] . "?action=edit'><img src='img/text_edit.png' alt='edit'/></a>";
		echo "</div>";
	}
}

?>
