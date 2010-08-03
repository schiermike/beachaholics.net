<?php

	require_once "classes/database.php";
	require_once "classes/session.php";

	if(isset($_GET['getPdf']))
	{
		$pdfId = $_GET['getPdf'];	

		Session::initialize();
		
		header('Date: '.gmdate('D, d M Y H:i:s') . ' GMT');
	
		$result = getDB()->query("SELECT Pdf FROM Uebungen WHERE UebungID=".$pdfId);
		if(mysql_num_rows($result) == 0)
			exit();
		list($pdfData) = mysql_fetch_row($result);
		
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Length: '.strlen($pdfData));
	   	header("Content-type: application/pdf");
	   	echo $pdfData;		 
		exit();
	}

	require_once "init.php";
	
	HP::printPageHead("Material für Übungsleiter", "img/top_exercises.png");
	if( getUser()->isMember())
		printPage();
	else
		HP::printLoginError();
	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{
		$result = getDB()->query("SELECT UebungID, Kategorie FROM Uebungen ORDER BY Kategorie");
		
		$kategorie = "";
		$count = 0;
		echo "<ul style='line-height: 20px;'>\n";
		while( $row = mysql_fetch_assoc($result))
		{
			if(strcmp($kategorie, $row['Kategorie']) != 0)
			{
				if($kategorie != "")
					echo "</li>\n";
				$kategorie = $row['Kategorie'];
				$count = 0;
				
				echo "<li>";
				echo $kategorie.": ";
			}
			$count++;
			
			echo "<a href='".$_SERVER['PHP_SELF']."?getPdf=".$row['UebungID']."'>$count</a>&nbsp;&nbsp;";
		}
		echo "</ul>";
	}
?>