<?php
	/**
	 * returns the picture with the given id from the user-database
	 */

	require_once "classes/database.php";
	require_once "classes/session.php";

	if(!isset($_GET['id']))
		exit();
		
	$pictureId = $_GET['id'];
		
	Session::initialize();
		
	header('Date: '.gmdate('D, d M Y H:i:s') . ' GMT');
	
	header("Cache-Control: public, max-age=10800, pre-check=10800");
	header("Pragma: public");
	header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
	
	// the browser will send a $_SERVER['HTTP_IF_MODIFIED_SINCE'] 
//	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
//	{
		// if the browser has a cached version of this image, send 304
//		header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
//		exit;
//	}
	
	$result = getDB()->query("SELECT Bild FROM Spieler WHERE SpielerID=".$pictureId);
	if(mysql_num_rows($result) == 0)
		exit();
	list($pictureData) = mysql_fetch_row($result);
	
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Length: '.strlen($pictureData));
   	header("Content-type: image/jpg");
   	echo $pictureData;
?>