<?php

require_once "init.php";

define('SHOW_OLD_TRAININGS_UNTIL', 60*60*24*7*4);

HP::printPageHead("Wettervorhersage", "img/top_weather.png");
printPage();
HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {	
	echo "<div style='margin:15px;'>";
	echo "<center><a href='http://www.zamg.ac.at/wetter/wetteranimation/' target='_blank'>Wetteranimation</a></center><br/>";
	echo "<b>Heute</b>\n";
	$html = HP::getWebsite("http://www.zamg.ac.at/wetter/prognose/tirol/index.php?ts=1310729101");
	$search = "<div id=\"contMain\">";
	$startPos = strpos($html, $search) + strlen($search);
	$search = "<tr valign=\"middle\">";
	$startPos = strpos($html, $search, $startPos) + strlen($search);
	$endPos = strpos($html, "</tr>", $startPos);
	$htmlWeather = substr($html, $startPos, $endPos-$startPos);
	$htmlWeather = str_replace("src=\"/dynx", "src=\"http://www.zamg.ac.at/dynx", $htmlWeather);
	echo "<table width='100%'><tr>$htmlWeather</tr></table>";

	echo "<b>Morgen</b>\n";
	$html = HP::getWebsite("http://www.zamg.ac.at/wetter/prognose/tirol/morgen.php?ts=1310729101");
	$search = "<div id=\"contMain\">";
	$startPos = strpos($html, $search) + strlen($search);
	$search = "<tr valign=\"middle\">";
	$startPos = strpos($html, $search, $startPos) + strlen($search);
	$endPos = strpos($html, "</tr>", $startPos);
	$htmlWeather = substr($html, $startPos, $endPos-$startPos);
	$htmlWeather = str_replace("src=\"/dynx", "src=\"http://www.zamg.ac.at/dynx", $htmlWeather);
	echo "<table width='100%'><tr>$htmlWeather</tr></table>";

	echo "<b>Übermorgen</b>\n";
	$html = HP::getWebsite("http://www.zamg.ac.at/wetter/prognose/tirol/uebermorgen.php?ts=1310729101");
	$search = "<div id=\"contMain\">";
	$startPos = strpos($html, $search) + strlen($search);
	$search = "<tr valign=\"middle\">";
	$startPos = strpos($html, $search, $startPos) + strlen($search);
	$endPos = strpos($html, "</tr>", $startPos);
	$htmlWeather = substr($html, $startPos, $endPos-$startPos);
	$htmlWeather = str_replace("src=\"/dynx", "src=\"http://www.zamg.ac.at/dynx", $htmlWeather);
	echo "<table width='100%'><tr>$htmlWeather</tr></table>";
	echo "</div>";
}
	
?>
