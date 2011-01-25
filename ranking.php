<?php
	require_once "init.php";
	
	HP::printPageHead("Derzeitige Platzierung", "img/top_ranking.png");
	
	printPage();
	
	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function getLigaLink($tvvid, $showGames, $name, $nsaison=False)
	{
		$link = "<a href='".$_SERVER['PHP_SELF']."?&tvvid=" . $tvvid . "&name=" . urlencode($name);
		if($showGames)
			$link .= "&showgames=true";
		if($nsaison !== False)
			$link .= "&nsaison=" . $nsaison;
		$link .= "'>" . $name . "</a>";
		return $link;
	}

	function printPage()
	{
		echo "<div style='text-align:right'>";
			echo "<i>Saison 2010/2011:</i> ";
			echo getLigaLink(465, true, "Damen oberes Playoff");
			echo " | " . getLigaLink(430, false, "Damen Cup");
			echo " | " . getLigaLink(462, true, "Herren oberes Playoff");
			echo " | " . getLigaLink(429, false, "Herren Cup");
		echo "</div>";
		echo "<div style='text-align:right'>";
			echo "<i>Saison 2009/2010:</i> ";
			echo getLigaLink(367, true, "Damen oberes Playoff", "2009/2010");
			echo " | " . getLigaLink(311, false, "Damen Cup", "2009/2010");
			echo " | " . getLigaLink(370, true, "Herren oberes Playoff", "2009/2010");
			echo " | " . getLigaLink(312, false, "Herren Cup", "2009/2010");
			echo " | " . getLigaLink(373, true, "Mixed Meister Playoff", "2009/2010");
		echo "</div>";

		global $tvvid;
		global $showgames;
		global $name;

		if(!isset($tvvid))
			return;
		
		echo "<h3>" . $name . "</h3>";
		echo parseLigaTable($tvvid);
		if($showgames)
			echo "<br/>" . parseLigaGames($tvvid);
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
