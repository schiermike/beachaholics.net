<?php
	require_once "init.php";
	
	define('SHOW_OLD_TRAININGS_UNTIL', 60*60*24*7*4);
	
	HP::printPageHead("Wettervorhersage", "img/top_weather.png");

	printPage();

	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{	
		$html = HP::getWebsite("http://www.zamg.ac.at/wetter/prognose/tirol/?ts=1245835082");
		
		$startPos = strpos($html, "<tr valign=\"middle\">");
		$endPos = strpos($html, "</tr>", $startPos) + 5;
		
		$htmlWeather = substr($html, $startPos, $endPos-$startPos);
		$htmlWeather = str_replace("src=\"/dynx", "src=\"http://www.zamg.ac.at/dynx", $htmlWeather);
		
		echo "<h3>ZAMG Wetterprognose:</h3>";
		echo "<table width='100%'>$htmlWeather</table>";
		
		$startPos = strpos($html, "<table class=\"tableBorder\"");
		$endPos = strpos($html, "</table>", $startPos) + 8;
		
		$htmlWeather = substr($html, $startPos, $endPos-$startPos);
		echo $htmlWeather;
		
		echo "<br/><hr/><br/>";
		
		
		/*
		$html = HP::getWebsite("http://www.meinbezirk.at/Kufstein/bez_106/channel_1-1-18/chsid_0/gid_70513");
		
		$startPos = strpos($html, "<div class=\"lih1\">Kufstein</div>");
		$endPos = strpos($html, "<div style=\"margin-top:10px;margin-bottom:10px;cursor:pointer;\">", $startPos);
		
		$htmlWeather = substr($html, $startPos, $endPos-$startPos);
		$htmlWeather = str_replace("src=\"/elements/pics/", "src=\"http://www.meinbezirk.at/elements/pics/", $htmlWeather);
		
		echo "<h3>Bezirksblätter Wetterprognose:</h3>";
		echo $htmlWeather;
		
		echo "<br/><hr/><br/>";
		*/
		
		
		
		
		$html = HP::getWebsite("http://wetter.orf.at/tir/reportdetail?tmp=14300");
		$startPos = strpos($html, "<td width=327 valign=top nowrap>") + 32;
		$endPos = strpos($html, "<table border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=315 valign=top nowrap><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=3 color=003366><b>Weitere Informationen:", $startPos);
		$htmlWeather = substr($html, $startPos, $endPos-$startPos);
		$htmlWeather = str_replace("nowrap", "", $htmlWeather);
		$htmlWeather = str_replace("width=315", "", $htmlWeather);
		
		echo "<h3>wetter.orf.at Wetterprognose:</h3>";
		echo $htmlWeather;
	}
	
?>
