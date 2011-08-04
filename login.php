<?php
	require_once "init.php";
	
	loginProcedure();

// ===================================================================
// ===================================================================

	function loginProcedure() {
		if (!isset($_GET['userid'])) {
			getSession()->logout();
			Session::initialize();
			
			printSelectUser();
		}
		else if (!isset($_GET['response'])){
			printPasswordQuestion($_GET['userid'], true);
		}
		else if (checkResponse($_GET['userid'], $_GET['response'])){
			getSession()->login($_GET['userid']);
			echo "<html><head>";
			echo "<script type='text/css'>body { background-color:black; }</script>";
			echo "<script type='text/javascript'>window.location.href='gb.php'</script>";
			echo "</head></html>";
		}
		else {
			printPasswordQuestion($_GET['userid'], false);
		}
	}
	
// ===================================================================

	function printSelectUser()	{
		$sql = "SELECT SpielerID, Nachname, Vorname, Nick FROM Spieler WHERE Rights>=0 ORDER BY Nick ASC";
		$request = getDB()->query($sql);
		
		HP::printPageHead("Authentifizierung", "img/top_changepass.png");
				
		echo "<form name='loginForm' method='get' action='".$_SERVER['PHP_SELF']."'>\n";
		echo "<center><br/><br/><br/><br/>\n";
		echo "<p style='text-align:center'>\n";
		echo "<select id='userid' name='userid' style='width: 200px;' onkeypress='if (event.keyCode==13) this.form.submit();' onblur='this.form.submit();'>\n";
		echo "<option value='-1'>Benutzer auswählen</option>\n";
		while ($row = mysql_fetch_assoc($request))
			echo "<option value='".$row['SpielerID']."'>".HP::toHtml($row['Nick'])."</option>\n";
					
		echo "</select>\n";
		echo "</p>";
		echo "</center></form>\n";
		echo "<br/><br/>";
		echo "<script type='text/javascript'>document.getElementById('userid').focus();</script>";
		echo "<noscript><p><center><font color='#ff0000'>Bitte Javascript aktivieren. Sonst klappt die Anmeldung nicht!</font><br/><a href='http://www.werle.com/helps/javascri.htm'target='_blank'>Howto</a></center></p></noscript>";
		
		HP::printPageTail();
	}

// ===================================================================
	
	function printPasswordQuestion($userid, $firstAttempt) {
		$sql = "SELECT Nachname, Vorname FROM Spieler WHERE SpielerID=" . getDB()->escape($userid);
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		if ($row === false) {
			printSelectUser();
			return;
		}
		
		HP::printPageHead("Authentifizierung", "img/top_changepass.png");
		
		echo "<center><br/><br/><br/><br/>";
		echo "Benutzer <b>" . $row['Vorname'] . " " . $row['Nachname'] . "</b>\n";
		echo "<p style='text-align:center'>";
		echo "<input type='password' id='password' name='password' style='width: 200px;' onkeypress='{setResponse(); if (event.keyCode==13) document.getElementById(\"loginForm\").submit();}'/>";
		echo "</p>\n";
		echo "<input type='hidden' id='challenge' value='" . createChallenge($userid) . "'/>\n";
		
		echo "<form id='loginForm' method='get' action='".$_SERVER['PHP_SELF']."' onsubmit='setResponse()'>\n";
		echo "<input type='hidden' id='userid' name='userid' value='" . $userid . "'/>\n";
		echo "<input type='hidden' id='response' name='response' value=''/>\n";
//		echo "<p style='text-align:center'><input type='submit' value='Login' style='width: 200px;'/></p>\n";
		echo "</form>";
		if (!$firstAttempt)
			echo "<font color='#ff0000'><b>Login fehlgeschlagen!<b></font>\n";
		echo "</center>";
		echo "<br/><br/>";
		echo "<script>document.getElementById('password').focus();</script>";
		echo "<noscript><p><center><font color='#ff0000'>Bitte Javascript aktivieren. Sonst klappt die Anmeldung nicht!</font><br/><a href='http://www.werle.com/helps/javascri.htm' target='_blank'>Howto</a></center></p></noscript>";
		
		HP::printPageTail();
	}
	
	function createChallenge($userid) {
		$challenge = openssl_random_pseudo_bytes(64);
		$_SESSION['challenge'] = $challenge;
		return base64_encode($challenge);
	}
	
	function checkResponse($userid, $response) {
		$sql = "SELECT Password FROM Spieler WHERE SpielerID=" . getDB()->escape($userid);
		$request = getDB()->query($sql);
		$row = mysql_fetch_assoc($request);
		if ($row === false)
			return false;
			
		if (!isset($_SESSION['challenge']))
			return false;
		$challenge = $_SESSION['challenge'];
		unset($_SESSION['challenge']);
		
		$response = base64_decode($response);
		
		return $response == rc4Crypt($row['Password'], $challenge);
	}
	
	/**
	 * Symmetric en/decryption using the Rivest Cipher 4
	 */ 
	function rc4Crypt($key, $pt) {
		$s = array();
		for ($i=0; $i<256; $i++)
			$s[$i] = $i;
		$j = 0;
		$x;
		for ($i=0; $i<256; $i++) {
			$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
		}
		$i = 0;
		$j = 0;
		$ct = '';
		$y;
		for ($y=0; $y<strlen($pt); $y++) {
			$i = ($i + 1) % 256;
			$j = ($j + $s[$i]) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
			$ct .= $pt[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
		}
		return $ct;
	}
?>
