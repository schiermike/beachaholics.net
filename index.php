<?php
	/******************************
	 SITE OFFLINE
	 */
	//echo "<html><head><title>Site temporarily offline!</title></head>";
	//echo "<body><br/><br/><br/><h1>CONSTRUCTION WORK</h1><h2>Come back later!</h2></body></html>";
	//*****************************
	
	require_once "init.php";
 
	HP::printPageHead("Beachaholics.net");
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
*************************/

	echo "
	<h2 style='text-align:center;'><strong>Willkommen bei den Beachaholics Kufstein</strong></h2>
<br/>
<h3>Es ist Sommer! (Egal ob man schwitzt oder ...)</h3>

<p>Ja, lange hats gedauert doch nun hat das Warten ein Ende - jetzt ist er weg, der böse Schnee und ab gehts wieder in den Sand!</p>

<p>Wie zu jeder Jahreszeit würden wir uns auch am Beachplatz über neue Gesichter freuen. Wann wo gespielt wird, könnt ihr <a href='training.php'>hier</a> herausfinden! Und falls einmal nichts anstehen sollte, versucht es am Besten am <a href='gb.php'>Messageboard</a>, 3 weitere Freaks sind normalerweise schnell gefunden...</p>

<p>Gespielt wird meistens im Kufsteiner Freischwimmbad, an heißen Tagen wird gerne auf den Hechtsee ausgewichen. Anfahrtspläne und weitere Spielorte finden sich <a href='plan.php'>hier</a>.</p>

<br/><br/>
<p style='text-align:right;'>Einen schönen Sommer wünschen euch <br/>die <i>Beachaholics Kufstein</i><p>
<br/>";
	
	HP::printPageTail();
	
?>
