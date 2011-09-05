<?php

require_once "classes/database.php";
require_once "classes/user.php";
require_once "classes/session.php";
require_once "classes/log.php";
require_once "classes/event.php";
require_once "classes/hp.php";

foreach ($_POST as $key => $value)
    $$key = $value;
foreach ($_GET as $key => $value)
    $$key = $value;	

//------------------------------------------------------------------
//---------------I N I T   P R O C E D U R E------------------------
//------------------------------------------------------------------
Session::initialize();
	
?>
