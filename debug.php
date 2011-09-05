<?php
require_once "init.php";

if (!getUser()->isItMe())
	exit(0);

if (!isset($_GET['action']))
	$_GET['action'] = '';
	
switch(HP::getParam('action')) {
	case 'clear':
		clearLog();
		break;
	case 'delete':
		if (HP::isParamSet('log_id'))
			deleteLogEntry(HP::getParam('log_id'));
		break;
}

printPage();

function printPage() {
	echo "<html><head><title>Error Log</title></head><body>";
	echo "<div style='text-align: left; font-size: 8pt;'>";
	echo "CLIENT-IP: ".$_SERVER['REMOTE_ADDR']."<br/>";
	echo "SERVER: ".gethostname()."<br/>";
	echo "SESSION-ID: ".session_id()."<br/>";
	echo "SESSION-DATA: ";
	$sessiondata = getDB()->removePassFromString(print_r($_SESSION, true));
	$sessiondata = str_replace("\n", "<br/>", $sessiondata);
	$sessiondata = str_replace(" ", "&nbsp;", $sessiondata);
	echo $sessiondata;
	
	$dbStats = explode('  ', mysql_stat(getDB()->getConnection()));
	for ($i=0;$i<=7;$i++)
		echo "<br/>MySQL ".$dbStats[$i];
	$result = mysql_list_processes(getDB()->getConnection());
	while ($row = mysql_fetch_assoc($result))
 		printf("<br/>MySQL process: %s %s %s %s %s\n", $row["Id"], $row["Host"], $row["db"], $row["Command"], $row["Time"]);
	
	echo "<br/>ERRORLOG: ";
	echo "(<a href='".$_SERVER['PHP_SELF']."?action=clear'>clear log</a>)<br/>";
	echo "<div style='border-style: dotted; border-width: 1px; border-color: #b00000;'>";

	$result = getDB()->query("SELECT id, time, session, message, stacktrace FROM log ORDER BY time DESC");
	while ($row = mysql_fetch_assoc($result)) {
		echo "<b>".$row['time']."</b><br/>";
		echo HP::toHtml($row['session'], true)."<br/>";
		echo "<b>".$row['message']."</b><br/>";
		echo "<font size='1'>".HP::toHtml($row['stacktrace'], true)."</font><br/>";
		echo "<a href='".$_SERVER['PHP_SELF']."?action=delete&amp;log_id=".$row['id']."'>delete entry</a><br/><br/>";
	}
	echo "</div>";
	echo "</div></body></html>";
}

function clearLog() {
	getDB()->query("DELETE FROM log");
}

function deleteLogEntry($logId) {
	getDB()->query("DELETE FROM log WHERE id=".$logId);
}

?>