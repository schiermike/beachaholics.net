<?php
	require_once "init.php";
	define('COLOR_OUT','#ff0000');
	define('COLOR_IN','#008800');
	
	if($type != 'print' && $type != 'download')
		HP::printPageHead("Kontoübersicht", "img/top_account.png");
	
	printPage();

	if($type != 'print' && $type != 'download')
		HP::printPageTail();
		
// ===================================================================
// ===================================================================
		
	function printPage()
	{
		global $startDate, $endDate, $id, $date, $amount, $comment, $type;
		if(!getUser()->isVorstand())
		{
			HP::printLoginError();
			return;
		}
		
		if($startDate!=NULL)
			$_SESSION['accountSelectionStartDate']=$startDate;
		if($endDate!=NULL)
			$_SESSION['accountSelectionEndDate']=$endDate;
				
		switch($type)
		{
			case 'add_or_modify_entry':
				addOrModifyEntry($id, $date, $amount, $comment);
				break;
			case 'print_edit_entry':
				printToolBar();
				printAddModifyForm($id);
				printAccountTable();
				break;
			case 'ask_delete_entry':
				printAskDelete($id);
				break;
			case 'delete_entry':
				deleteEntry($id);
				break;
			case 'confirm_entry':
				confirmEntry($id);
				break;
			case 'ask_confirm_entry':
				printAskConfirm($id);
				break;
			case 'print':
				printPrintPage();
				break;
			case 'download':
				sendDownload($id);
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
	
	function deleteEntry($id)
	{
		$sql="DELETE FROM Konto WHERE BuchungID=".$id." AND UserChecked IS NULL";
		
		if(getDB()->query($sql))
		{
			printToolBar();
			printAccountTable();
		}
	}
	
	function printToolBar()
	{
		echo "\n<div style='text-align:right'>";
		echo "<form name='timeFilterForm' method='get' action='".$_SERVER['PHP_SELF']."'>";
		echo "<input type='text' readonly='readonly' name='startDate' size='9' value='".$_SESSION['accountSelectionStartDate']."'/>";
		echo "<a href='javascript:calStartDate.popup();'><img src='img/clock_play.png' alt='Startzeitpunkt' title='Startzeitpunkt'/></a> - ";
		echo "<input type='text' readonly='readonly' name='endDate' size='9' value='".$_SESSION['accountSelectionEndDate']."'/>";
		echo "<a href='javascript:calEndDate.popup();'><img src='img/clock_stop.png' alt='Endzeitpunkt' title='Endzeitpunkt'/></a> ";
		echo "&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?type=reset_timeframe'><img src='img/clock_delete.png' alt='Zeitfilter löschen' title='Zeitfilter löschen'/></a>";
		echo "&nbsp;&nbsp;&nbsp;";
		echo "<a href='".$_SERVER['PHP_SELF']."?type=print' target='_blank'><img src='img/print.png' alt='Auflistung drucken' title='Auflistung drucken'/></a>";
		echo "</form></div>\n";

		echo "<script type='text/javascript'>";
		echo "var calStartDate = new calendar3(document.forms['timeFilterForm'].elements['startDate'], document.forms['timeFilterForm']);";
		echo "var calEndDate = new calendar3(document.forms['timeFilterForm'].elements['endDate'], document.forms['timeFilterForm']);";
		echo "</script><hr/>\n";
	}
	
	function confirmEntry($id)
	{
		$sql = "UPDATE Konto SET UserChecked=".getUser()->id." WHERE BuchungID=".$id;
		
		if(getDB()->query($sql) && mysql_affected_rows()==1)
		{
			printToolBar();
			printAccountTable();
		}
	}
	
	function resetTimeFrame()
	{
		printToolBar();
		
		$_SESSION['accountSelectionStartDate']=NULL;
		$_SESSION['accountSelectionEndDate']=NULL;
		
		printAccountTable();
	}
	
	function sendDownload($id)
	{
		$sql="SELECT Anhang, AnhangName, AnhangType, AnhangSize FROM Konto WHERE BuchungID=".$id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		
		header("Content-length: ".$row['AnhangSize']);
		header("Content-type: ".$row['AnhangType']);
		header("Content-Disposition: attachment; filename=".$row['AnhangName']);
		echo $row['Anhang'];
		
		exit();
	}
	
	function printAskConfirm($id)
	{
		printToolBar();
		
		$sql = "SELECT Nachname, Vorname FROM Spieler JOIN Konto ON Spieler.SpielerID=Konto.UserEntered WHERE Konto.BuchungID=".$id;
		$result = getDB()->query($sql);
		$row = mysql_fetch_assoc($result);
		
		echo "<p style='text-align:center'><b>Kontoeintrag von ".$row['Nachname']." ".$row['Vorname']." absegnen</b></p>";
		echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
			echo "<p style='text-align:center'>";
			echo "<input type='submit' name='Submit' value='Bestätigen'/>";
			echo "<input type='hidden' name='id' value='".$id."'/>";
			echo "<input type='hidden' name='type' value='confirm_entry'/>";
			echo "</p>";
		echo "</form>"; 
		echo "<br/>";
		
		printAccountTable($id);
	}
	
	function printAskDelete($id)
	{
		printToolBar();
		
		echo "<p style='text-align:center'><b>Löschen bestätigen</b></p>";
		echo "<form name='form1' method='get' action='".$_SERVER['PHP_SELF']."'>";
			echo "<p style='text-align:center'>";
			echo "<input type='submit' name='Submit' value='Wirklich löschen'/>";
			echo "<input type='hidden' name='id' value='".$id."'/>";
			echo "<input type='hidden' name='type' value='delete_entry'/>";
			echo "</p>";
		echo "</form>"; 
		echo "<hr/>";
		
		printAccountTable($id);
	}
	
	function addOrModifyEntry($id, $date, $amount, $comment)
	{
		$amount=str_replace(',','.',$amount);
		
		if(!is_numeric($amount) || $date=='' || $comment=='')
		{
			printToolBar();
			HP::printErrorText("Eingabedaten sind fehlerhaft!");
			printAddModifyForm($id, $date, $amount, $comment);
			printAccountTable();
			return;
		}
		
		$attachment="NULL";
		$attachmentName="NULL";
		$attachmentType="NULL";
		$attachmentSize="NULL";
		if($_FILES['attached']['size'] > 0)
		{
			$fp      = fopen($_FILES['attached']['tmp_name'], 'r');
			$attachment = fread($fp, filesize($_FILES['attached']['tmp_name']));
			$attachment = addslashes($attachment);
			fclose($fp);
			$attachment = "'".$attachment."'";
			$attachmentName="'".$_FILES['attached']['name']."'";
			$attachmentType="'".$_FILES['attached']['type']."'";
			$attachmentSize="'".$_FILES['attached']['size']."'";
		}

		if($id == NULL)
			$sql = "INSERT INTO Konto (Datum, Vermerk, Betrag, UserEntered, Anhang, AnhangName, AnhangType, AnhangSize) VALUES ('".$date."','".$comment."',".$amount.",".getUser()->id.", ".$attachment.", ".$attachmentName.", ".$attachmentType.", ".$attachmentSize.")";
		else
			$sql = "UPDATE Konto SET Datum='".$date."', Vermerk='".$comment."', Betrag='".$amount."', Anhang=".$attachment.", AnhangName=".$attachmentName.", AnhangType=".$attachmentType.", AnhangSize=".$attachmentSize.", UserEntered=".getUser()->id.", UserChecked=NULL WHERE BuchungID=".$id;
		
		if(getDB()->query($sql) && mysql_affected_rows()==1)
		{
			printToolBar();
			printAccountTable();
		}
	}
	
	function printAddModifyForm($id=NULL, $date=NULL, $amount=NULL, $comment=NULL)
	{
		if($id!=NULL && $date==NULL)
		{
			$sql = "SELECT Datum, Vermerk, Betrag FROM Konto WHERE BuchungID=".$id;
			$request = getDB()->query($sql);
			$row = mysql_fetch_assoc($request);
			$date=$row['Datum'];
			$amount=$row['Betrag'];
			$comment=$row['Vermerk'];
		}
		
		echo "<p style='text-align:center'><b>";
		echo $id==NULL ? "Kontoeintrag hinzufügen" : "Kontoeintrag ändern";
			
		echo "</b></p>";
		
		echo "<form name='accountForm' method='post' action='".$_SERVER['PHP_SELF']."' enctype='multipart/form-data'>";
		echo "<table width='100%' style='text-align:center'>";
		
		echo "<tr><td style='text-align:right' width='30%'>Zeit:</td>";
		echo "<td><input type='text' readonly='readonly' name='date' size='9' value='".$date."'/>";
		echo "<a href='javascript:calDate.popup();'><img src='img/cal.gif' alt='Datum wählen'/></a></td></tr>";
		
		echo "<tr><td style='text-align:right'>Betrag:</td>";
		echo "<td><input type='text' name='amount' size='10' value='".$amount."'/>&euro;</td></tr>";
		
		echo "<tr><td style='text-align:right'>Bemerkung:</td>";
		echo "<td><textarea name='comment' rows='6' cols='60'/>".$comment."</textarea>";
		echo "</td></tr>";
		
		echo "<tr><td style='text-align:right'>Beleg (optional):</td>";
		echo "<td><input type='hidden' name='MAX_FILE_SIZE' value='2000000'>";
		echo "<input name='attached' type='file' id='attached' size='30'></td></tr>";
		 
		echo "</table>";
		
		echo "<script type='text/javascript'>";
		echo "var calDate = new calendar3(document.forms['accountForm'].elements['date']);";
		echo "</script>";
		
		echo "<input type='hidden' name='type' value='add_or_modify_entry'/>";
		if($id!=NULL)
			echo "<input type='hidden' name='id' value='".$id."'/>";
		echo "<p style='text-align:center'><input type='submit' name='Submit' value='Bestätigen'/></p>";
		echo "</form>";
		echo "<hr/>";
	}
	
	function printPrintPage()
	{
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
		
		$zeitraum="alles";
		$sql = "SELECT BuchungID, Datum, Vermerk, Betrag FROM Konto ";
		if($_SESSION['accountSelectionStartDate']!=NULL && $_SESSION['accountSelectionEndDate']!=NULL)
		{
			$sql .= "WHERE Datum>='".$_SESSION['accountSelectionStartDate']."' AND Datum<='".$_SESSION['accountSelectionEndDate']."' ";
			$zeitraum = "von ".$_SESSION['accountSelectionStartDate']." bis ".$_SESSION['accountSelectionEndDate'];
		} 
		$sql .= "ORDER BY Datum, Vermerk";
		$request = getDB()->query($sql);
		
		echo "<div style='text-align:right'>User: ".getUser()->getName()." / BA-Indoor-System<br/>";
		echo "generiert: ".HP::getPHPTime()."<br/>";
		echo "Zeitraum: ".$zeitraum; 
		echo "</div><hr/>\n";
		
		echo "<table width='100%'>\n";
		echo "<tr><th>Datum</th>";
		echo "<th style='padding-left:30px;' width='100%'>Vermerk</th>";
		echo "<th style='padding-right:30px; text-align:right'>Betrag</th>";
		echo "<th style='text-align:right'>Summe</th></tr>\n";
		
		$output="";
		$account=0;
		while($row = mysql_fetch_assoc($request))
		{
			$rowOutput="<tr>";
			
			$rowOutput.="<td>".$row['Datum']."</td>";
			$rowOutput.="<td style='padding-left:30px;'>".$row['Vermerk']."</td>";
				
			$color= $row['Betrag']<0 ? COLOR_OUT : COLOR_IN;
			$rowOutput.="<td style='padding-right:30px; text-align:right'><font color='".$color."'>".echoMoney($row['Betrag'])."</font></td>";
				
			$account+=$row['Betrag'];
			$color= $account<0 ? COLOR_OUT : COLOR_IN;
			$rowOutput.="<td style='text-align:right'><font color='".$color."'>".echoMoney($account)."</font></td>";
			
			$rowOutput.="</tr>\n";
			
			$output = $rowOutput.$output;
		}
		echo $output;
		
		echo "</table><br/><hr/>";
		
		printAccountSummary();
		
		echo "<script type='text/javascript'>window.print();</script></body></html>";
	}
	
	function printAccountTable($toDeleteId=NULL)
	{
		$sql = "SELECT BuchungID, Datum, Vermerk, Betrag, AnhangName, UserEntered, s1.Nick as UserEnteredNick, UserChecked, s2.Nick AS UserCheckedNick ";
		$sql .= "FROM Konto JOIN Spieler s1 ON s1.SpielerID=UserEntered LEFT JOIN Spieler s2 ON s2.SpielerID=UserChecked ";
		if($_SESSION['accountSelectionStartDate']!=NULL && $_SESSION['accountSelectionEndDate']!=NULL)
			$sql .= "WHERE Datum>='".$_SESSION['accountSelectionStartDate']."' AND Datum<='".$_SESSION['accountSelectionEndDate']."' "; 
		$sql .= "ORDER BY Datum, Vermerk";
		$request = getDB()->query($sql);

		echo "<table cellspacing='0' cellpadding='3' width='100%'>";
		echo "<tr>";
		echo "<th>Datum</th>";
		echo "<th style='padding-left:10px;' width='100%'>Vermerk</th>";
		echo "<th style='padding-right:10px; text-align:right'>Betrag</th>";
		echo "<th style='padding-right:10px; text-align:right'>Summe</th>";
		echo "<th/><th/>";
		echo "<th><a href='".$_SERVER['PHP_SELF']."?type=print_edit_entry'><img src='img/money_add.png' alt='neu' title='neuen Kontoeintrag anlegen'/></a></th>";
		echo "<th/>";
		echo "</tr>";

		$output="";
		$account=0;
		while($row = mysql_fetch_assoc($request))
		{
			$rowOutput="";
			
			if($toDeleteId==$row['BuchungID'])
				$rowOutput.="<tr class='rowColorSelected'>";
			else if($row['Betrag'] < 0)
				$rowOutput.="<tr class='rowColor0'>";
			else
				$rowOutput.="<tr class='rowColor1'>";
			
				$rowOutput.="<td style='white-space:nowrap;'>".$row['Datum']."</td>";
				$rowOutput.="<td style='padding-left:10px;'>".nl2br($row['Vermerk'])."</td>";
				
				$color= $row['Betrag']<0 ? COLOR_OUT : COLOR_IN;
				$rowOutput.="<td style='padding-right:10px; text-align:right' nowrap='nowrap'><font color='".$color."'>".echoMoney($row['Betrag'])."</font></td>";
				
				$account+=$row['Betrag'];
				$color= $account<0 ? COLOR_OUT : COLOR_IN;
				$rowOutput.="<td style='padding-right:10px; text-align:right' nowrap='nowrap'><font color='".$color."'>".echoMoney($account)."</font></td>";
				
				if($row['AnhangName'])
					$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?type=download&amp;id=".$row['BuchungID']."'><img src='img/attached.gif' alt='' title='".$row['AnhangName']."'/></a></td>";
				else
					$rowOutput.="<td></td>";
				
				$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?type=print_edit_entry&amp;id=".$row['BuchungID']."'><img src='img/money_edit.png' alt='editieren' title='diesen Kontoeintrag editieren'/></a></td>";
				if($row['UserChecked']==NULL)
					$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?type=ask_delete_entry&amp;id=".$row['BuchungID']."'><img src='img/money_delete.png' alt='löschen' title='diesen Kontoeintrag löschen'/></a></td>";
				else
					$rowOutput.="<td></td>";
				
				if($row['UserChecked']==NULL)
				{
					if($row['UserEntered'] == getUser()->id)
						$rowOutput.="<td><img src='img/warn.png' alt='pending' title='Eintrag muss zuerst von einem anderen Vorstandsmitglied abgesegnet werden'/></td>";
					else
						$rowOutput.="<td><a href='".$_SERVER['PHP_SELF']."?type=ask_confirm_entry&amp;id=".$row['BuchungID']."'><img src='img/warn_go.png' alt='markieren' title='Eintrag von ".$row['UserEnteredNick']." als geprüft markieren'/></a></td>";
				}
				else
					$rowOutput.="<td><img src='img/ok.png' alt='ok' title='Eintrag von ".$row['UserEnteredNick']." geprüft von ".$row['UserCheckedNick']."'/></td>";
				
			$rowOutput.="</tr>\n";
			
			$output = $rowOutput.$output;
		}
		echo $output;
		echo "</table>";
		
		echo "<hr/>";
		
		printAccountSummary();
		echo "<br/>";
	}
	
	function printAccountSummary()
	{
		$sql = "SELECT 'Ausgaben' AS Name, SUM(Betrag) AS Betrag FROM Konto WHERE Betrag<0 
			UNION SELECT 'Einnahmen' AS Name, SUM(Betrag) AS Betrag FROM Konto WHERE Betrag>0";
		$request = getDB()->query($sql);
		$in=0;
		$out=0;
		while($row = mysql_fetch_assoc($request))
		{
			switch($row['Name'])
			{
				case 'Ausgaben':
					$out=$row['Betrag'];
					break;
				case 'Einnahmen':
					$in=$row['Betrag'];
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
	
	function echoMoney($amount)
	{
		return money_format('%!-10#6.2i', $amount)."&nbsp;&euro;";
	}
?>