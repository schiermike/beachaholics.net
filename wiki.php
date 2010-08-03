<?php
	require_once("init.php");
	require_once("wikilib.php");

	HP::printPageHead("Wiki", "img/top_wiki.png");

	printPage();

	HP::printPageTail();
	
// ===================================================================
// ===================================================================

	function printPage()
	{
		if( getUser()->isMember())
		{	  	
			// strip slash hack - sonst wird ''BLA'' zu \'\'BLA\'\'
			$_REQUEST['content'] = stripslashes($_REQUEST['content']);
			
			echo "<div>";
			echo ewiki_page();
			echo "</div>";
		}
		else
			HP::printLoginError();
	}

?>
