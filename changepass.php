<?php
require_once "init.php";
  	
HP::printPageHead("Passwort ändern", "img/top_changepass.png");
printPage(); 
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {
	echo "<div align='center'><br/>";
	if (!getUser()->isGuest())	
		printPassChangeForm(getUser()->id);
	else if (HP::isParamSet('userid'))
		printPassChangeForm(HP::getParam('userid'));
	echo "</div>";
}
  	
function printPassFields($id) {
	$sql = "SELECT lastname, firstname FROM user WHERE id = " . esc($id);
	$result = getDB()->query($sql);
	$row = mysql_fetch_assoc($result);
	if ($row === false)
		return;
	
	echo "<script type='text/javascript'>";
	echo "function doCheck() {
		pw = document.getElementById('newpw').value;
		score = getPasswordStrength(pw);
		document.getElementById('score').value = (10*score) + '%';
		document.getElementById('submit').disabled = score<7;
		}";
	echo "</script>";
	if (HP::isParamSet('expired'))
		echo "<p style='color: #ff0000; text-align:center;'>Dein Passwort wurde entweder zu alt oder ist zu schwach!<br/>Bitte ändere es jetzt.</p>";
	echo "<p><form name='form1' method='post' action='changepass.php'>";
	echo "<table width='350'>";
	echo "<tr><td scope='col'>Benutzer:</td>";
	echo "<td scope='col'>" . $row['lastname'] . " " . $row['firstname'] . "</td></tr>";
	echo "<tr><td scope='col'>Altes Passwort:</td>";
	echo "<td scope='col'><input type='password' name='old_password'/></td></tr>";
	echo "<tr><td scope='col'>Neues Passwort:</td>";
	echo "<td scope='col'><input id='newpw' type='password' onchange='doCheck();' onkeyup='doCheck();' name='new_password'/></td></tr>";
	echo "<tr><td scope='col'>Passwort bestätigen:</td>";
	echo "<td scope='col'><input type='password' name='new_password_copy'/></td></tr>";
	echo "<tr><td scope='col'>Passwortstärke:</td>";
	echo "<td scope='col'><input id='score' type='text' disabled='disabled' size='4'></input> (mindestens 70%)</td></tr>";
	echo "</table>";
	echo "<br/>";
   echo"<input id='submit' type='submit' value='Änderung durchführen' disabled='disabled'/>";
   echo"<input type='hidden' name='userid' value='" . $id . "'/>";
   if (HP::isParamSet('expired'))
   	echo"<input type='hidden' name='expired' value='yes'/>";
	echo "</form></p>\n";
}

function printPassChangeForm($id) {
	if (!is_numeric($id))
		return;
		
	if ( !HP::isParamSet('old_password') || !HP::isParamSet('new_password') || !HP::isParamSet('new_password_copy') ) {
		printPassFields($id);
		return;
	}
	
	if (HP::getParam('new_password') != HP::getParam('new_password_copy')) {
		HP::printErrorText("Passwörter müssen ident sein!");
		printPassFields($id);
		return;
	}
	
	if (HP::getParam('old_password') == HP::getParam('new_password')) {
		HP::printErrorText("Das neue und das alte Passwort müssen sich unterscheiden!");
		printPassFields($id);
		return;
	}
	
	if (strlen(HP::getParam('new_password')) < 7) {
		HP::printErrorText("Das gewählte Passwort ist zu kurz!");
		printPassFields($id);
		return;
	}
	
	$sql = "UPDATE user SET password=" . esc(HP::getParam('new_password')) . ", pw_timestamp=NOW()" .
		" WHERE id=" . esc($id) . " AND password=" . esc(HP::getParam('old_password'));
	$request = getDB()->query($sql);
	if (mysql_affected_rows()==0) {
		HP::printErrorText("Altes Passwort muss korrekt sein!");
		printPassFields($id);
		return;
	}
	
	HP::printErrorText("Passwortänderung erfolgreich!");
	getSession()->login($id);
}
?>
