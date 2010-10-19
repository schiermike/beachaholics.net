<?php
  	require_once "init.php";
  	
  	HP::printPageHead("Passwort ändern", "img/top_changepass.png");
  	
  	global $oldpass, $newpass1, $newpass2;
  	if(!getUser()->isGuest())
  		printPassChangeForm($oldpass, $newpass1, $newpass2);
  	else
  		HP::printLoginError();

	HP::printPageTail();
	
// ===================================================================
// ===================================================================
  	
  	function printPassFields()
	{
  		echo "\n<form name='form1' method='post' action='changepass.php'>";
			echo "<table width='350'>";
				echo "<tr><td scope='col'>Altes Passwort:</td>";
				echo "<td scope='col'><input type='password' name='oldpass'/></td></tr>";
				echo "<tr><td scope='col'>Neues Passwort:</td>";
				echo "<td scope='col'><input type='password' name='newpass1'/></td></tr>";
				echo "<tr><td scope='col'>Passwort bestätigen:</td>";
				echo "<td scope='col'><input type='password' name='newpass2'/></td></tr>";
			echo "</table>";
			echo "<br/>";
	   		echo"<input type='submit' name='Submit' value='Änderung durchführen'/>";
		echo "</form>\n";
	}
	
	function printPassChangeForm($oldpass, $newpass1, $newpass2)
	{
		echo "<div align='center'><br/>";
		
		if($oldpass==NULL || $newpass1==NULL || $newpass2==NULL) //passwoerter erstmal eingeben
			printPassFields();
		else //passwort aendern
		{
			if($newpass1!=$newpass2)
			{
				HP::printErrorText("Passwörter müssen ident sein!");
				printPassFields();
			}
			else if($oldpass==$newpass1)
			{
				HP::printErrorText("Das neue und das alte Passwort müssen sich unterscheiden!");
				printPassFields();
			}
			else
			{
				$sql = "UPDATE Spieler SET Password='".getDB()->escape($newpass1)."' WHERE SpielerID=".getUser()->id." AND Password='".getDB()->escape($oldpass)."'";
				$request = getDB()->query($sql);
				if(mysql_affected_rows()==0)
				{
					HP::printErrorText("Altes Passwort muss korrekt sein!");
					printPassFields();
				}
				else
					HP::printErrorText("Passwortänderung erfolgreich!");
			}
		}
		echo "</div>";	
	}
?>
