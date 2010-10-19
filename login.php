<?php
	require_once "init.php";
	
	if(isset($userid) && $userid == -1)
	{
		getSession()->logout();
		Session::initialize();
	}

	if(isset($userid) && isset($pass) && getSession()->login($userid, $pass))
	{
		echo "<html><head>";
		echo "<script type='text/css'>body { background-color:black; }</script>";
		echo "<script type='text/javascript'>window.location.href='gb.php'</script>";
		echo "</head></html>";
	}
	else
	{			
		HP::printPageHead("Authentifizierung", "img/top_changepass.png");
		if(isset($pass))
			HP::printErrorText("Passwort inkorrekt!");
		printLogin();
		HP::printPageTail();
	}

// ===================================================================
// ===================================================================
	
	function printLogin()
	{
		$sql = "SELECT SpielerID, Nachname, Vorname, Nick FROM Spieler WHERE Rights>=0 ORDER BY Nick ASC";
		$request = getDB()->query($sql);
				
		echo "<form method='get' action='".$_SERVER['PHP_SELF']."'>";
		echo "<center><br/><br/>";
		echo "<p style='text-align:center'><select name='userid' id='select' style='width: 150px;'>";
		echo "<option value='-1'>Benutzer auswählen</option>";
		while($row = mysql_fetch_assoc($request))
			echo "<option value='".$row['SpielerID']."'>".HP::toHtml($row['Nick'])."</option>";
					
		echo "</select></p>";
		echo "<p style='text-align:center'><input type='password' name='pass' style='width: 150px;'/></p>";
		echo "<p style='text-align:center'><input type='submit' value='Login' style='width: 150px;'/></p>";
		echo "</center></form>";
		echo "<br/><br/>";
	}
?>
