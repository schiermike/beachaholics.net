<?php
require_once "init.php";
  	
HP::printPageHead("Passwort ändern", "img/top_changepass.png");
  	
if (!getUser()->isGuest()) {
	echo "<div align='center'><br/>";
	printPassChangeForm();
	echo "</div>";
}
else
	HP::printLoginError();

HP::printPageTail();

// ===================================================================
// ===================================================================
  	
function printPassFields() {
	echo "<p><form name='form1' method='post' action='changepass.php'>";
	echo "<table width='350'>";
	echo "<tr><td scope='col'>Altes Passwort:</td>";
	echo "<td scope='col'><input type='password' name='old_password'/></td></tr>";
	echo "<tr><td scope='col'>Neues Passwort:</td>";
	echo "<td scope='col'><input type='password' name='new_password'/></td></tr>";
	echo "<tr><td scope='col'>Passwort bestätigen:</td>";
	echo "<td scope='col'><input type='password' name='new_password_copy'/></td></tr>";
	echo "</table>";
	echo "<br/>";
   echo"<input type='submit' value='Änderung durchführen'/>";
	echo "</form></p>\n";
}

function printPassChangeForm() {	
	if ( !HP::isParamSet('old_password') || !HP::isParamSet('new_password') || !HP::isParamSet('new_password_copy') ) {
		printPassFields();
		return;
	}
	
	if (HP::getParam('new_password') != HP::getParam('new_password_copy')) {
		HP::printErrorText("Passwörter müssen ident sein!");
		printPassFields();
		return;
	}
	
	if (HP::getParam('old_password') == HP::getParam('new_password')) {
		HP::printErrorText("Das neue und das alte Passwort müssen sich unterscheiden!");
		printPassFields();
		return;
	}
	
	if (strlen(HP::getParam('new_password')) < 5) {
		HP::printErrorText("Das gewählte Passwort ist zu kurz!");
		printPassFields();
		return;
	}
	
	$sql = "UPDATE user SET password='" . getDB()->escape(HP::getParam('new_password')) . 
		"' WHERE id=" . getUser()->id . " AND password='" . getDB()->escape(HP::getParam('old_password')) . "'";
	$request = getDB()->query($sql);
	if (mysql_affected_rows()==0) {
		HP::printErrorText("Altes Passwort muss korrekt sein!");
		printPassFields();
		return;
	}
	
	HP::printErrorText("Passwortänderung erfolgreich!");	
}
?>
