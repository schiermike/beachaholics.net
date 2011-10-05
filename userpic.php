<?php

require_once "classes/hp.php";
require_once "classes/database.php";
require_once "classes/session.php";

if (!HP::isParamSet('id'))
	exit();
	
printUserPicture(HP::getParam('id'));

/**
 * returns the picture with the given id from the user-database
 */
function printUserPicture($id) {	
	if (!is_numeric($id))
		return;
		
	Session::initialize();
		
	header('Date: '.gmdate('D, d M Y H:i:s') . ' GMT');
	
	header("Cache-Control: public, max-age=10800, pre-check=10800");
	header("Pragma: public");
	header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));
	
	$result = getDB()->query("SELECT avatar FROM user WHERE id=" . enc($id));
	if (mysql_num_rows($result) == 0)
		exit();
	list($pictureData) = mysql_fetch_row($result);
	
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Length: '.strlen($pictureData));
	header("Content-type: image/jpg");
	echo $pictureData;
}
?>