<?php

require_once "classes/database.php";
require_once "classes/session.php";

if(isset($_GET['getPdf'])) {
	$pdfId = $_GET['getPdf'];	

	Session::initialize();
	
	header('Date: '.gmdate('D, d M Y H:i:s') . ' GMT');

	$result = getDB()->query("SELECT id, category, pdf FROM exercises WHERE id=".$pdfId);
	if (mysql_num_rows($result) == 0)
		exit();
	$row = mysql_fetch_assoc($result);
	
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Length: '.strlen($row['pdf']));
	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="' . $row['id'] . '_' . $row['category'] . '.pdf"');
	echo $row['pdf'];		 
	exit();
}

require_once "init.php";

HP::printPageHead("Material für Übungsleiter", "img/top_exercises.png");
if (getUser()->isMember())
	printPage();
else
	HP::printLoginError();
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {
	$result = getDB()->query("SELECT id, category FROM exercises ORDER BY category");
	
	$kategorie = "";
	$count = 0;
	echo "<ul style='line-height: 20px;'>\n";
	while ( $row = mysql_fetch_assoc($result)) {
		if (strcmp($kategorie, $row['category']) != 0) {
			if ($kategorie != "")
				echo "</li>\n";
			$kategorie = $row['category'];
			$count = 0;
			
			echo "<li>";
			echo $kategorie.": ";
		}
		$count++;
		
		echo "<a href='".$_SERVER['PHP_SELF']."?getPdf=".$row['id']."'>$count</a>&nbsp;&nbsp;";
	}
	echo "</ul>";
}
?>
