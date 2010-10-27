<?php
	/******************************
	 SITE OFFLINE
	 */
	//echo "<html><head><title>Site temporarily offline!</title></head>";
	//echo "<body><br/><br/><br/><h1>CONSTRUCTION WORK</h1><h2>Come back later!</h2></body></html>";
	//*****************************
	
	require_once "init.php";

	function printEditField()
	{
		global $date;
		echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";

		$result = NULL;
		if(isset($date))
			$result = getDB()->query("SELECT Inhalt FROM Inhalte WHERE Datum='" . $date . "'");
		else
			$result = getDB()->query("SELECT Inhalt FROM Inhalte WHERE Datum=(SELECT MAX(Datum) FROM Inhalte)");
		$row = mysql_fetch_assoc($result);

		echo "<center><textarea id='content' name='content' style='width:98%' cols='1' rows='30'>".$row['Inhalt']."</textarea></center>";
		echo "<div style='text-align:right'>Text vom ";
		echo "<select name='date' onChange='window.location=\"?action=edit&date=\" + this.value'>";
		$result = getDB()->query("SELECT Datum FROM Inhalte ORDER BY Datum DESC");
		while($row = mysql_fetch_assoc($result))
		{
			echo "<option value='" . $row['Datum'] . "'";
			if(isset($date) && $date == $row['Datum'])
				echo "selected='selected'";
			echo ">" . $row['Datum'] . "</option>";
		}
		echo "</select>";
		echo " laden | ";
		echo "<input type='submit' value='Speichern'/>";
		echo "</div>";

		echo "<input type='hidden' name='action' value='confirm'/>\n";
		echo "</form>";
	}

	function confirm()
	{
		global $content;
		getDB()->query("DELETE FROM Inhalte WHERE Name='start' AND Datum=DATE(NOW())");
		getDB()->query("INSERT INTO Inhalte (Name, Datum, Inhalt) VALUES ('start', DATE(NOW()), '" . getDB()->escape($content) . "')");
	}	

	function printText()
	{
		$result = getDB()->query("SELECT Inhalt FROM Inhalte WHERE Datum=(SELECT MAX(Datum) FROM Inhalte)");
		$row = mysql_fetch_assoc($result);
		echo $row['Inhalt'];

		if( getUser()->isAdmin() )
		{
			echo "<div style='text-align:right'>";
				echo "<a href='" . $_SERVER['PHP_SELF'] . "?action=edit'><img src='img/text_edit.png' alt='edit'/></a>";
			echo "</div>";
		}
	}
 
	HP::printPageHead("Beachaholics.net");

	if(getUser()->isAdmin() && isset($action))
	{
		switch($action)
		{
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
	
?>
