<?php
	require_once "init.php";
	
	function toIcalTime($sqlTime)
	{
		$tmp = str_replace("-", "", $sqlTime);
		$tmp = str_replace(":", "", $tmp);
		return str_replace(" ", "T", $tmp);
	}
	
	$output = "BEGIN:VCALENDAR\n";
	$output .= "VERSION:2.0\n";
	$output .= "PRODID:-//schiermike/beachaholics//DE\n";
	$output .= "CALSCALE:GREGORIAN\n";
	$output .= "BEGIN:VTIMEZONE\n";
	$output .= "TZID:Europe/Vienna\n";
	$output .= "X-LIC-LOCATION:Europe/Vienna\n";
	$output .= "BEGIN:DAYLIGHT\n";
	$output .= "TZOFFSETFROM:+0100\n";
	$output .= "TZOFFSETTO:+0200\n";
	$output .= "TZNAME:CEST\n";
	$output .= "DTSTART:19700329T020000\n";
	$output .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=3;BYDAY=-1SU\n";
	$output .= "END:DAYLIGHT\n";
	$output .= "BEGIN:STANDARD\n";
	$output .= "TZOFFSETFROM:+0200\n";
	$output .= "TZOFFSETTO:+0100\n";
	$output .= "TZNAME:CET\n";
	$output .= "DTSTART:19701025T030000\n";
	$output .= "RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=10;BYDAY=-1SU\n";
	$output .= "END:STANDARD\n";
	$output .= "END:VTIMEZONE\n";
	
	
	$result = getDB()->query("SELECT Zeit, Endzeit, Bemerkung, Ort, Typ, Link FROM Events");
	
	while($row = mysql_fetch_assoc($result))
	{
		$output .= "BEGIN:VEVENT\n";

		
		$output .= "DTSTART:".toIcalTime($row['Zeit'])."\n";
		if($row['Endzeit'] != Null)
			$output .= "DTEND:".toIcalTime($row['Endzeit'])."\n";
		$output .= "SUMMARY:".Event::toString($row['Typ'])."\n";
		$output .= "DESCRIPTION:".$row['Bemerkung']." ".$row['Link']."\n";
		$output .= "LOCATION:".$row['Ort']."\n";
		$output .= "CATEGORIES: Beachaholics,".Event::toString($row['Typ'])."\n";
		
		$output .= "END:VEVENT\n";
	}
	
	$output .= "END:VCALENDAR\n";
		
	header('Date: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Expires: '.gmdate('D, d M Y H:i:s') . ' GMT', 24*60*60);
	header('Content-Length: '.strlen($output));
	header("Content-type: text/calendar");
//	header("Content-type: text/plain");
	echo $output;
?>