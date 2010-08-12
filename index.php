<?php
	/******************************
	 SITE OFFLINE
	 */
	//echo "<html><head><title>Site temporarily offline!</title></head>";
	//echo "<body><br/><br/><br/><h1>CONSTRUCTION WORK</h1><h2>Come back later!</h2></body></html>";
	//*****************************
	
	require_once "init.php";
 
	HP::printPageHead("Beachaholics.net");

	echo "<h2 style='text-align:center;'><strong>Willkommen bei den BeachAholics Kufstein!</strong></h2><br/><br/>";
	echo "<p style='font-size:12pt; text-align: center;'>Unsere Spielzeiten findet ihr <a href='training.php'>hier</a>!</p><br/>";
	echo "<p style='font-size:12pt; text-align: center;'>Weiters findet jeden Donnerstag von 18 bis 19 Uhr das Kindertraining im Freischwimmbad Kufstein statt - jeder ist herzlich dazu eingeladen, teilzunehmen!</p>";
	echo "<p style='font-size:12pt; padding: 20px;'><b>Aktueller Hinweis:</b> Das diesjährige ATV 3:3 Turnier findet am 22. August im Freischwimmbad Kufstein statt - die Anmeldung ist <a href='http://www.mod-kufstein.com' target='_blank'>hier</a> möglich.</p>";


/****************************
	echo "
	<h2 style='text-align:center;'><strong>Die Beachaholics sind in der Winterpause!</strong></h2>
<br/>
<p>Natürlich beschäftigen wir uns auch im Winter mit unserem Lieblingssport Volleyball, allerdings in der Halle in Form von Damen-, Herren- und Mixedtrainings.</p> 

<p style='background-color:#aecde8; margin-left:50px'>
	<em>Herren:</em>
	<br/>
	Mittwoch, VS Sparchen (Kufstein), 20:15 bis 22:15 Uhr 
	<br/>
	Freitag, VS Zell (Kufstein), 20:30 bis 22:30 Uhr 
	<br/>
	Ansprechperson: Klaus Kendlbacher (klaus[DOT]kendlbacher[AT]gmx[DOT]at) 
</p>

<p style='background-color:#ffc8d9; margin-left:100px'>
	<em>Damen:</em>
	<br/>
	Dienstag, Kufstein Arena, 18:45 bis 22:00 Uhr
	<br/>
	Freitag, VS Zell (Kufstein), 18:45 bis 20:30 Uhr 
	<br/>
	Ansprechperson: Verena Eder (eder[DOT]verena[AT]snw[DOT]at)
</p>

<p style='background-color:#74cd48; margin-left:150px'>
	<em>Mixed:</em>
	<br/>
	Sonntag, VS Kirchbichl, ca. 14:00 Uhr 
	<br/>
	Nur nach Vereinbarung! Bei Interesse bitte anmelden. 
	<br/>
	Ansprechperson: Mario Perktold (perktold[AT]kufnet[DOT]at)
</p>

<p>Wir freuen uns <strong>jederzeit</strong> über neue motivierte Mitspieler! Einfach bei der jeweiligen Ansprechperson melden oder direkt zum Training kommen!</p>

<p>Weitere Infos findet ihr hier: <a href='training.php'>Trainingsübersicht</a>, <a href='plan.php'>Anfahrtspläne</a></p>

<br/>";
****************************/

/****************************

	echo "<div style='padding:50px;'>
	<h2 style='text-align:center;'><strong>Willkommen bei den Beachaholics Kufstein</strong></h2>
<br/>
<h3>Es ist Sommer! (Egal ob man schwitzt oder ...)</h3>

<p>Ja, lange hats gedauert doch nun hat das Warten ein Ende - jetzt ist er weg, der böse Schnee und ab gehts wieder in den Sand!</p>

<p>Wie zu jeder Jahreszeit würden wir uns auch am Beachplatz über neue Gesichter freuen. Wann wo gespielt wird, könnt ihr <a href='training.php'>hier</a> herausfinden! Und falls einmal nichts anstehen sollte, versucht es am Besten am <a href='gb.php'>Messageboard</a>, 3 weitere Freaks sind normalerweise schnell gefunden...</p>

<p>Gespielt wird meistens im Kufsteiner Freischwimmbad, an heißen Tagen wird gerne auf den Hechtsee ausgewichen. Anfahrtspläne und weitere Spielorte finden sich <a href='plan.php'>hier</a>.</p>

<p>Weiters erreicht ihr uns unter +4369981931833 / klaus[DOT]kendlbacher[AT]gmx[DOT]at beziehungsweise +4366488539190 / perktold[AT]kufnet[DOT]at</p>

<br/><br/>
<p style='text-align:right;'>Einen schönen Sommer wünschen euch <br/>die <i>Beachaholics Kufstein</i><p>
<br/></div>";

****************************/

/****************************

echo "<center><table cellspacing='8'><tr><td><img src='flyer.jpg' alt=''/></td><td>";

echo "<h3>Hallo Volleyballfreunde!</h3>

<p>Wir Beachaholics Kufstein veranstalten in diesem Jahr bereits zum 6. Mal das ultimativ spaßige <br/><b>BEACH&WATER-Mixed-Volleyballturnier</b></p>
WO: Hechtsee Kufstein<br/>
WANN: 03.07.2010<br/>
BEGINN: 10 Uhr<br/>
NENNGEBÜHR: 16 Euro/Team<br/>
MODUS: 4 vs 4 (mind. 1 Dame)<br/>

<p>Dabei messen sich die Teams nicht nur am Beachvolleyballplatz, sondern müssen auch im Wassercourt ihr Können unter Beweis stellen.</p>

<p>Wie immer warten super Preise für alle teilnehmenden Mannschaften! Außerdem ist für das leibliche Wohl natürlich bestens gesorgt, unsere Bardamen freuen sich schon!</p>

<p>Eine Teilnahme lohnt sich also auf jeden Fall, wir freuen uns auf eure Anmeldungen unter</p>
<p style='text-align:right'>
<i>+43 699 81931833</i><br/>
bzw.<br/>
<i><a href='mailto:klaus_kendlbacher@gmx.at'>klaus_kendlbacher@gmx.at</a></i></p>";

echo "</td></tr></table></center>";

****************************/
	
	HP::printPageTail();
	
?>
