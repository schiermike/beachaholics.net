<?php

require_once "init.php";

HP::printPageHead("Beachaholics.net");

if (getUser()->isAuthorized(User::$ROLE_MEMBER)) {
	$action = "";
	if (isset($_GET['action']))
		$action = $_GET['action'];
	if (isset($_POST['action']))
		$action = $_POST['action'];
	switch($action) {
		case 'edit':
			printEditField();
			break;
		case 'confirm':
			confirm();
		default:
			printText();
	}
}
else
	printText();


HP::printPageTail();

// ===================================================================
// ===================================================================

function printEditField() {
	echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";

	$result = NULL;
	if (isset($_GET['date']))
		$result = getDB()->query("SELECT text FROM content WHERE date='" . $_GET['date'] . "'");
	else
		$result = getDB()->query("SELECT text FROM content WHERE date=(SELECT MAX(date) FROM content)");
	$row = mysql_fetch_assoc($result);

	echo "<center><textarea id='content' name='content' style='width:98%' cols='1' rows='30'>".$row['text']."</textarea></center>";
	echo "<div style='text-align:right'>Text vom ";
	echo "<select name='date' onChange='window.location=\"?action=edit&date=\" + this.value'>";
	$result = getDB()->query("SELECT date FROM content ORDER BY date DESC");
	while ($row = mysql_fetch_assoc($result)) {
		echo "<option value='" . $row['date'] . "'";
		if (isset($_GET['date']) && $_GET['date'] == $row['date'])
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

function confirm() {
	getDB()->query("DELETE FROM content WHERE pagename='start' AND date=DATE(NOW())");
	getDB()->query("INSERT INTO content (pagename, date, text) VALUES ('start', DATE(NOW()), '" . getDB()->escape($_POST['content']) . "')");
}	

function printText() {
	$result = getDB()->query("SELECT text FROM content WHERE date=(SELECT MAX(date) FROM content)");
	$row = mysql_fetch_assoc($result);
	echo $row['text'];

	if (getUser()->isAuthorized(User::$ROLE_MEMBER)) {
		echo "<div style='text-align:right'>";
		echo "<a href='" . $_SERVER['PHP_SELF'] . "?action=edit'><img src='img/text_edit.png' alt='edit'/></a>";
		echo "</div>";
	}
}

?>
