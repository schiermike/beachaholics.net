<?php
	require_once "init.php";
	
	HP::printPageHead("Links", "img/top_links.png");
	printPage();
	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{	
		$sql = "SELECT Link, Description FROM Links ORDER BY Rank ASC";
		$request = getDB()->query($sql);
	
		echo "<table cellpadding='3' cellspacing='0' width='100%'>";
		$i = 0;
		while($row = mysql_fetch_assoc($request))
		{
			echo "<tr class='rowColor".($i++%2)."'>";
				echo "<td style='text-align:center'><a href='".HP::toHtml($row['Link'])."' target='_blank'>".HP::toHtml($row['Description'])."</a></td>\n";
			echo "</tr>";
		}
		echo "</table>";
	}
?>