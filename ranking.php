<?php
	require_once "init.php";
	
	HP::printPageHead("Derzeitige Platzierung", "img/top_ranking.png");
	
	printPage();
	
	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{
		echo "<div style='text-align:right'>";
			echo "<a href='".$_SERVER['PHP_SELF']."?section=ligamen'>Herren Liga</a> | ";
			echo "<a href='".$_SERVER['PHP_SELF']."?section=ligawomen'>Damen Liga</a> | ";
			echo "<a href='".$_SERVER['PHP_SELF']."?section=cupmen'>Herren Cup</a> | ";
			echo "<a href='".$_SERVER['PHP_SELF']."?section=cupwomen'>Damen Cup</a> | ";
			echo "<a href='".$_SERVER['PHP_SELF']."?section=mixed'>Mixed Bewerb</a>";
		echo "</div>";
		
		switch($_GET['section'])
		{
			case 'cupmen':
				echo "<h3>Herren Cup Spiele: (<a href='http://www.tvv.at/pdf/TVV_Cup_Raster_Herren.pdf' target='_blank'>Cup Raster</a>)</h3>";
				echo parseLigaGames(312);
				break;
			case 'cupwomen':
				echo "<h3>Damen Cup Spiele: (<a href='http://www.tvv.at/pdf/TVV_Cup_Raster_Damen.pdf' target='_blank'>Cup Raster</a>)</h3>";
				echo parseLigaGames(311);
				break;
			case 'mixed':
				echo "<h3>Mixed-A-Liga Herbstdurchgang:</h3>";
				echo parseLigaTable(319);
				echo parseLigaGames(319);
				break;
			case 'ligawomen':
				echo "<h3>Damen oberes Playoff:</h3>";
				echo parseLigaTable(367);
				echo parseLigaGames(367);
				break;
			case 'ligamen':
			default:
				echo "<h3>Herren oberes Playoff:</h3>";
				echo parseLigaTable(370);
				echo parseLigaGames(370);
				break;
		}
	}
	
	function parseLigaTable($ligaid)
	{
		$page = HP::getWebsite("http://www.tvv.at/tabelle.php?liga=".$ligaid);
		
		$page = substr($page, strpos($page, "<table width=\"744\""));
		$page = substr($page, 0, strpos($page, "</table>") + 8);
		$page = str_replace("<table width=\"744\"", "<table width='100%' class='tvvparsed'", $page);
		$page = styleCorrections($page);
		
		return $page;
	}
	
	function parseLigaGames($ligaid)
	{
		$page = "";
		$pCount = 0;
		
		while(true)
		{
			
			$subPage = HP::getWebsite("http://www.tvv.at/alle.php?lim=".$pCount."&liga=".$ligaid);

			$tableHead = "<table width=\"744\" border=\"0\" cellpadding=\"5\" cellspacing=\"1\">";
			$subPage = substr($subPage, strpos($subPage, $tableHead) + strlen($tableHead));
			$subPage = substr($subPage, 0, strpos($subPage, "</table>"));

			// keine weiteren spalten
			if(strlen($subPage) < 200)
				break;
			
			$page .= $subPage;
			$pCount+=20;
		}
		
		$page = str_replace("<strong>", "", $page);
		$page = str_replace("</strong>", "", $page);
		
		$page = styleCorrections($page);
		// überflüssige Spalten kicken
		$page = preg_replace("%<td width=\"65\" valign=\"top\">(.|\n)*?</td>%", "", $page);
		$page = preg_replace("%<td width=\"18\" valign=\"top\">(.|\n)*?</td>%", "", $page);
		$page = str_replace("colspan=\"7\"", "colspan=\"5\"", $page);
		
		$page = "<table width='100%' cellpadding='3' cellspacing='0' class='tvvparsed'>".$page."</table>";
		
		return $page;
	}
	
	function styleCorrections($page)
	{
		$page = str_replace(" color=\"#FFFFFF\"", " ", $page);
		$page = str_replace(" class=\"normal\"", "", $page);
		$page = str_replace("&nbsp;</div>", "", $page);
		$page = str_replace("<br>", "<br/>", $page);
		$page = str_replace(" bgcolor=\"#cc0000\"", " class='rowColor2'", $page);
		$page = str_replace(" bgcolor=\"#F0B7B7\"", " class='rowColor1'", $page);
		$page = str_replace(" bgcolor=\"#fde7e7\"", " class='rowColor0'", $page);
		$page = str_replace(" bgcolor=\"#F2C1C1\"", " class='rowColor1'", $page);
		
		$page = str_replace(" href=\"mannschaften_detail.php", " target=\"_blank\" href=\"http://www.tvv.at/mannschaften_detail.php", $page);
		$page = str_replace("ATV MOD Kufstein", "<strong>ATV MOD Kufstein</strong>", $page);
		$page = str_replace("Beachaholics Kufstein", "<strong>Beachaholics Kufstein</strong>", $page);
		return $page;
	}
?>