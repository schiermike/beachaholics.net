<?php

require_once "classes/database.php";
require_once "classes/session.php";
require_once "classes/hp.php";

if(HP::isParamSet('getPdf'))
	printPdf(HP::getParam('getPdf'));

require_once "init.php";

HP::printPageHead("Material für Übungsleiter", "img/top_exercises.png");
printPage();
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPdf($id) {
	Session::initialize();
	
	header('Date: '.gmdate('D, d M Y H:i:s') . ' GMT');

	$result = getDB()->query("SELECT id, category, pdf FROM exercises WHERE id=".$id);
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

function printPage() {
	if (!getUser()->isMember()) {
		HP::printLoginError();
		return;
	}	
	
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
