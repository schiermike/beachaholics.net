<?php

require_once "init.php";

HP::printPageHead("Links", "img/top_links.png");
printPage();
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {	
	$sql = "SELECT url, description FROM link ORDER BY rank ASC";
	$request = getDB()->query($sql);

	echo "<table cellpadding='0' cellspacing='0' width='100%'>";
	$i = 0;
	while ($row = mysql_fetch_assoc($request)) {
		if ($i%2==0)
			echo "<tr>";
		echo "<td style='text-align:left; padding-left: 60px; padding-top: 20px;'><a href='".HP::toHtml($row['url'])."' target='_blank'>".HP::toHtml($row['description'])."</a></td>\n";
		if ($i%2==1)
			echo "</tr>";
		$i++;
	}
	echo "</table>";
}
?>