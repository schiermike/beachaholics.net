<?php
	require_once "init.php";
	define('EURO_PER_KILOMETER', 0.15); // in Euro
	
	HP::printPageHead("Fahrtkosten Indoor", "img/top_driving.png");
	printPage();
	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{
		global $type, $id, $playerId, $date, $distance, $addAmount, $comment, $state;
		if(!getUser()->isAuthorized(User::$ROLE_INDOOR_MEN | User::$ROLE_INDOOR_WOMEN))
		{
			HP::printLoginError();
			return;
		}
		
		switch($type)
		{
			case 'add_or_modify_entry':
				addOrModifyEntry($id, $playerId, $date, $distance, $addAmount, $comment, $state);
				break;
			case 'print_edit_entry':
				printToolBar();
				printAddModifyForm($id);
				printDrivingTable();
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
			default:
				printToolBar();
				printDrivingTable();
				break;
		}	
	}
	
	function deleteEntry($id)
	{
		if(!getUser()->isAdmin())
		{
			HP::printLoginError();
			return;
		}
		
		$sql="DELETE FROM Fahrten WHERE FahrtID=".$id;
		
		if(getDB()->query($sql))
		{
			printToolBar();
			printDrivingTable();
		}
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
		
		printDrivingTable($id);
	}
	
	function addOrModifyEntry($id, $playerId, $date, $distance, $addAmount, $comment, $state)
	{
		if(!getUser()->isAdmin())
		{
			HP::printLoginError();
			return;
		}
		
		$state = isset($state) ? 1 : 0;		
		if($addAmount == NULL) $addAmount = 0;
		$addAmount=str_replace(',','.',$addAmount);
		$distance=str_replace(',','.',$distance);
		
		if(!is_numeric($distance) || !is_numeric($addAmount) || $date=='' || $comment=='')
		{
			printToolBar();
			HP::printErrorText("Eingabedaten sind fehlerhaft!");
			printAddModifyForm($id, $playerId, $date, $distance, $addAmount, $comment, $state);
			printDrivingTable();
			return;
		}
		
		if($id == NULL)
			$sql = "INSERT INTO Fahrten (SpielerID, Datum, KM, Zusaetz_Betrag, Bemerkung, Status) VALUES (".$playerId.", '".$date."','".$distance."','".$addAmount."', '".$comment."', ".$state.")";
		else
			$sql = "UPDATE Fahrten SET SpielerID=".$playerId.", Datum='".$date."', KM='".$distance."', Zusaetz_Betrag='".$addAmount."', Bemerkung='".$comment."', Status=".$state." WHERE FahrtID=".$id;
		

		getDB()->query($sql);
		printToolBar();
		printDrivingTable();
	}
	
	function printAddModifyForm($id=NULL, $playerId=NULL, $date=NULL, $distance=NULL, $addAmount=NULL, $comment=NULL, $state=NULL)
	{
		if($id!=NULL && $date==NULL)
		{
			$sql = "SELECT SpielerID, Datum, KM, Zusaetz_Betrag, Bemerkung, Status FROM Fahrten WHERE FahrtID=".$id;
			$request = getDB()->query($sql);
			$row = mysql_fetch_assoc($request);
			
			$playerId=$row['SpielerID'];
			$date=$row['Datum'];
			$distance=$row['KM'];
			$addAmount=$row['Zusaetz_Betrag'];
			$comment=$row['Bemerkung'];
			$state=$row['Status'];
		}
		
		echo "<p style='text-align:center'><b>";
		echo $id==NULL ? "Eintragung hinzufügen" : "Eintragung ändern";
			
		echo "</b></p>";
		
		echo "<form name='accountForm' method='post' action='".$_SERVER['PHP_SELF']."' enctype='multipart/form-data'>";
		echo "<table width='100%' style='text-align:center'>";
		
		$sql = "SELECT SpielerID, Vorname, Nachname FROM Spieler WHERE SpielerID != ".User::getGuestId();
		$request = getDB()->query($sql);
		echo "<tr><td style='text-align:right' width='40%'>Benutzer:</td>";
		echo "<td><select name='playerId'>";
		while($row = mysql_fetch_assoc($request))
		{
			echo "<option value='".$row['SpielerID']."' ".($row['SpielerID'] == $playerId ? "selected='selected'" : "").">".$row['Nachname']." ".$row['Vorname']."</option>";
		}
		echo "</select></td></tr>";
		
		echo "<tr><td style='text-align:right' width='40%'>Zeit:</td>";
		echo "<td><input type='text' readonly='readonly' name='date' size='9' value='".$date."'/>";
		echo "<a href='javascript:calDate.popup();'><img src='img/cal.gif' alt='Datum wählen'/></a></td></tr>";
		
		echo "<tr><td style='text-align:right'>Gefahrene Kilometer:</td>";
		echo "<td><input type='text' name='distance' size='10' value='".$distance."'/>KM</td></tr>";
		
		echo "<tr><td style='text-align:right'>Zusätzlicher Betrag:</td>";
		echo "<td><input type='text' name='addAmount' size='10' value='".$addAmount."'/>&euro;</td></tr>";
		
		echo "<tr><td style='text-align:right'>Bemerkung:</td>";
		echo "<td><input type='text' name='comment' size='40' value='".$comment."'/></td></tr>";
		
		echo "<tr><td style='text-align:right'>Abgerechnet:</td>";
		echo "<td><input type='checkbox' name='state' size='40' ".($state == 0 ? "" : "checked='checked'")."/></td></tr>";
		 
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
	
	function printToolBar()
	{
		echo "<div align='right'><a href='".$_SERVER['PHP_SELF']."?type=print_edit_entry'><img src='img/money_add.png' alt='neu' title='neuen Eintrag erzeugen'/></a></div>";
		echo "<hr/>\n";
	}

	function printDrivingTable($toDeleteId=NULL)
	{
		$sql = "SELECT FahrtID, Nachname, Vorname, Datum, Bemerkung, KM, Status, Zusaetz_Betrag FROM Fahrten JOIN Spieler USING(SpielerID) ORDER BY Datum DESC";
		$request = getDB()->query($sql);

		echo "<table cellspacing='0' cellpadding='3' width='100%'>";
		echo "<tr>";
		echo "<th style='text-align:left'>Name</th>";
		echo "<th style='text-align:left'>Datum</th>";
		echo "<th width='100%'>Bemerkung</th>";
		echo "<th style='text-align:right'><img src='img/car.png' alt='Gefahrene Kilometer' title='Gefahrene Kilometer'/></th>";
		echo "<th style='text-align:right'><img src='img/coins.png' alt='Kosten' title='Kosten'/></th>";
		echo "<th><img src='img/checklist.gif' alt='Status' title='Status'/></th>";
		echo "<th></th><th></th>";
		echo "</tr>";

		$rowc=0;
		while($row = mysql_fetch_assoc($request))
		{
			if($toDeleteId==$row['FahrtID'])
				echo "<tr class='rowColorSelected'>";
			else
				echo "<tr class='rowColor".($rowc%2)."'>";
			
				echo "<td nowrap='nowrap' style='padding-right:15px'>".HP::toHtml($row['Nachname']." ".$row['Vorname'])."</td>";
				echo "<td nowrap='nowrap' style='padding-right:15px'>".$row['Datum']."</td>";
				echo "<td>".HP::toHtml($row['Bemerkung'])."</td>";
				echo "<td style='text-align:right' nowrap='nowrap'>".$row['KM']." km</td>";
				echo "<td nowrap='nowrap' style='padding-left:15px; text-align:right'>".($row['Zusaetz_Betrag'] + EURO_PER_KILOMETER*$row['KM'])." &euro;</td>";
				
				echo "<td style='padding-left:15px'><img src='";
				echo $row['Status'] == 0 ? "img/cross.png" : "img/ok.png";
				echo "' alt=''/></td>";
				
				echo "<td><a href='".$_SERVER['PHP_SELF']."?type=print_edit_entry&amp;id=".$row['FahrtID']."'><img src='img/money_edit.png' alt='editieren' title='diesen Eintrag editieren'/></a></td>";
				echo "<td><a href='".$_SERVER['PHP_SELF']."?type=ask_delete_entry&amp;id=".$row['FahrtID']."'><img src='img/money_delete.png' alt='löschen' title='diesen Eintrag löschen'/></a></td>";
				
			echo "</tr>";
			$rowc++;
		}
		echo "</table><div style='text-align:right'><font size='2'>Kilometergeld: ".EURO_PER_KILOMETER." &euro;/km</font></div><br/>";

		$sql = "SELECT Nachname, Vorname, SUM(KM), SUM(Zusaetz_Betrag) FROM Fahrten JOIN Spieler USING(SpielerID) WHERE Status = 0 GROUP BY Nachname, Vorname ORDER BY Nachname, Vorname DESC";
		$request = getDB()->query($sql);

		echo "<div align='right'><table cellspacing='0' cellpadding='3'>";
		echo "<tr>";
		echo "<th>Name</th>";
		echo "<th style='text-align:right'><img src='img/car.png' alt='Gefahrene Kilometer' title='Gefahrene Kilometer'/></th>";
		echo "<th style='text-align:right'><img src='img/coins.png' alt='Kosten' title='Kosten'/></th>";
		echo "</tr>";

		$km = 0;
		$money = 0;
		$rowc = 0;

		while($row = mysql_fetch_assoc($request))
		{
			$km = $km + $row['SUM(KM)'];
			$money = $money + $row['SUM(KM)']*EURO_PER_KILOMETER + $row['SUM(Zusaetz_Betrag)'];

			echo "<tr class='rowColor".($rowc%2)."'>";
			
				echo "<td>".HP::toHtml($row['Nachname']." ".$row['Vorname'])."</td>";
				echo "<td nowrap='nowrap' style='padding-left:25px; text-align:right'>".$row['SUM(KM)']." km</td>";
				echo "<td nowrap='nowrap' style='padding-left:15px; text-align:right'>".($row['SUM(KM)']*EURO_PER_KILOMETER + $row['SUM(Zusaetz_Betrag)'])." &euro;</td>";
			echo "</tr>";
			$rowc++;
		}

		echo "<tr class='column_three'>";
				echo "<td><b>GESAMTBETRAG</b></td>";
				echo "<td style='text-align:right' nowrap='nowrap'><b>".$km." km</b></td>";
				echo "<td style='text-align:right' nowrap='nowrap' style='padding-left:15px'><b>".$money." &euro;</b></td>";
		echo "</tr>";

		echo "</table><font size='2'>Kilometergeld: ".EURO_PER_KILOMETER." &euro;/km</font></div><br/>";
	}
?>