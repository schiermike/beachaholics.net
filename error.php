<?php
	require_once "init.php";
	
	define('SHOW_OLD_TRAININGS_UNTIL', 60*60*24*7*4);

	HP::printPageHead("Seitenfehler");

	printPage();

	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{
		$page = "'".$_SERVER['REDIRECT_URL']."'";
		$code = $_SERVER['REDIRECT_STATUS']; 
		
		$explanation="";
		switch($code)
		{
			case 400:
				$explanation="Bad Request - Server versteht Anfrage aufgrund von fehlerhafter Syntax nicht!";
				break;
			case 401:
				$explanation="Unauthorized - Nicht autorisiert zum Empfang der Seite $page!";
				break;
			case 402:
				$explanation="Payment Required";
				break;
			case 403:
				$explanation="Forbidden - Zugang zu der Seite wurde verweigert!";
				break;
			case 404:
				$explanation="Not Found - Die Seite $page wurde am Server nicht gefunden!";
				break;
			case 405:
				$explanation="Method Not Allowed";
				break;
			case 406:
				$explanation="Not Acceptable";
				break;
			case 407:
				$explanation="Proxy Authentication Required";
				break;
			case 408:
				$explanation="Request Timeout - Der Server wartete auf die Anforderung des Clients zu lange!";
				break;
			case 409:
				$explanation="Conflict";
				break;
			case 410:
				$explanation="Gone - Die angeforderte Seite $page ist nicht länger verfügbar!";
				break;
			case 411:
				$explanation="Length Required - Content-Length des Requests muss im Header bekanntgegeben werden!";
				break;
			case 412:
				$explanation="Precondition Failed";
				break;
			case 413:
				$explanation="Request Entity Too Large - Die vom Client gesendete Anforderung ist einfach zu groß!";
				break;
			case 414:
				$explanation="Request-URI Too Long - Die URI der angeforderten Seite $page ist einfach zu lang!";
				break;
			case 415:
				$explanation="Unsupported Media Type - Server kann den Typ der Anfrage nicht interpretieren!";
				break;
			case 416:
				$explanation="Requested Range Not Satisfiable";
				break;
			case 417:
				$explanation="Expectation Failed";
				break;
			case 500:
				$explanation="Internal Server Error - Irgendwas am Server lief schief, kann aber nicht sagen was!";
				break;
			case 501:
				$explanation="Not Implemented - Die geforderte Funktionalität wird vom Server nicht unterstützt!";
				break;
			case 502:
				$explanation="Bad Gateway";
				break;
			case 503:
				$explanation="Service Unavailable - Anforderung kann derzeit nicht beantwortet werden wegen Überlastung oder Wartungsarbeiten";
				break;
			case 504:
				$explanation="Gateway Timeout";
				break;
			case 505:
				$explanation="HTTP Version Not Supported - Was hast'n du für'n Drecks-Browser?";
				break;
		}
		
		echo "<br/><br/>";
		echo "<p style='text-align: center; color: red; font-weight: bold; font-size: large;'>Fehler #$code</p>";
		echo "<p style='text-align: center; color: red; font-weight: bold; font-size: medium;'>$explanation</p>";
		echo "<br/><br/><br/>";
	}
	
?>