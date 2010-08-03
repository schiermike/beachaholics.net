<?php
	require_once "init.php";
	
	HP::printPageHead("Anfahrtspläne", "img/top_location.png", NULL, array("http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAACAIT_E71P3eIb-xH4NMDQhTfibC8pX3uhe5mh5-4KgUTvwNs-hQehqkO08mJpv3BSsixkt8BhAJqKA"));
?>	

	<div id='map' style='width: 780px; height: 600px'></div>
		
	<script type='text/javascript'>
if(GBrowserIsCompatible())
{
	var map = new GMap2(document.getElementById('map'));
	map.setCenter(new GLatLng(47.54, 12.1));
	map.setZoom(12);
	map.setUIToDefault();
				
	var houseIcon = new GIcon(G_DEFAULT_ICON);
	houseIcon.image = 'img/navi/anfahrt.gif';
	houseIcon.shadow= '';
	houseIcon.iconSize = new GSize(24,24);
				
	var m1 = new GMarker(new GLatLng(47.5130, 12.0870), {title: 'Volksschule Kirchbichl', icon: houseIcon});
	GEvent.addListener(m1, 'click', function() {m1.openInfoWindowHtml('<p><b>Volksschulhalle Kirchbichl</b></p><p>Ort 36<br/>A-6322 Kirchbichl</p><ul><li>Bundesstraße von Kufstein aus kommend</li><li>Beim Gemeindeamt (großes Haus linkerhand) nach Rechts abbiegen.</li><li>Nach 50m nach der Volksbank links abbiegen und dort parken.</li></ul>');}); 
	map.addOverlay(m1);
				
	var m2 = new GMarker(new GLatLng(47.5837, 12.1584), {title: 'Volksschule Zell', icon: houseIcon});
	GEvent.addListener(m2, 'click', function() {m2.openInfoWindowHtml('<p><b>Volksschule Zell</b></p><p>Langkampfnerstraße 23<br/>A-6330 Kufstein</p><ul><li>Autobahnausfahrt Kufstein Süd nehmen</li><li>Beim den Kreisverkehrs in Richtung Zentrum fahren</li><li>Bei Kreisverkehr vor Festung nach links abbiegen über die Innbrücke</li><li>Abzweigung Lamgkampfen nach links nehmen</li><li>Nach ca. 300m links abbiegen (schmaler Weg)</li></ul>');});
	map.addOverlay(m2);
				
	var m3 = new GMarker(new GLatLng(47.5914, 12.1778), {title: 'Volksschule Sparchen', icon: houseIcon});
	GEvent.addListener(m3, 'click', function() {m3.openInfoWindowHtml('<p><b>Volksschule Sparchen</b></p><p>Sterzingerstraße 5<br/>A-6330 Kufstein</p><ul><li>Autobahnausfahrt Kufstein Nord nehmen</li><li>Beim Kreisverkehr rechts fahren (die erste Ausfahrt)</li><li>Nach 100m den Abzweiger nach rechts Richtung Zentrum nehmen</li><li>Nach der Unterführung über Kreisverkehr</li><li>Beim nächsten Kreisverkehr die letzte Ausfahrt nehmen</li><li>Die erste Straße nach rechts nehmen</li><li>Bei der ersten Möglichkeit links abbiegen</li></ul>');});
	map.addOverlay(m3);
				
	var m4 = new GMarker(new GLatLng(47.5885, 12.1682), {title: 'Kufstein Arena', icon: houseIcon});
	GEvent.addListener(m4, 'click', function() {m4.openInfoWindowHtml('<p><b>Kufstein Arena</b></p><p>Fischergries<br/>A-6330 Kufstein</p><ul><li>Autobahnausfahrt Kufstein Nord nehmen</li><li>Beim Kreisverkehr rechts fahren (die erste Ausfahrt)</li><li>Nach 100m den Abzweiger nach rechts Richtung Zentrum nehmen</li><li>Nach der Unterführung beim Kreisverkehr wieder die erste Ausfahrt rechts nehmen</li><li>Nach 150m rechts abbiegen (Richtung Freibad)</li><li>Der Straße folgen, bis linkerhand der gro&szlig;e Arenaparkplatz auftaucht </li><li>Den Seiteneingang auf der dem Inn abgewandten Seite benützen!</li></ul>');});
	map.addOverlay(m4);
				
	var m5 = new GMarker(new GLatLng(47.5813, 12.1718), {title: 'Gymnasium Kufstein', icon: houseIcon});
	GEvent.addListener(m5, 'click', function() {m5.openInfoWindowHtml('<p><b>Kufstein Gymnasium</b></p><p>Schillerstraße 2<br/>A-6330 Kufstein</p><ul><li>Autobahnausfahrt Kufstein Süd nehmen</li><li>Beim Kreisverkehr die Erste rechts ausfahren</li><li>Beim zweiten Kreisverkehr nach links Richtung Kufstein</li><li>Dem Straßenverlauf folgen</li><li>Begrenzte Parkmöglichkeiten sind vorhanden </li></ul>');});
	map.addOverlay(m5);
				
	var m6 = new GMarker(new GLatLng(47.4870, 12.0692), {title: 'Hauptschule Wörgl', icon: houseIcon});
	GEvent.addListener(m6, 'click', function() {m6.openInfoWindowHtml('<p><b>Hauptschulhalle Wörgl</b></p><p>Dr. Stumpf Straße 4<br/>A-6300 Wörgl</p><ul><li>Autobahnausfahrt Wörgl Ost nehmen (von Kufstein kommend)</li><li>Beim Kreisverkehr die zweite Ausfahrt nehmen</li><li>Nach 300m bei der Ampel zwischen Super-M und M4 links abbiegen.</li><li>Nach 30m parken und zwischen den Gebäuden zum Hauptschuleingang gehen</li></ul>');});
	map.addOverlay(m6);
				
	window.onunload = GUnload;
}
    </script>
	
<?php HP::printPageTail(); ?>