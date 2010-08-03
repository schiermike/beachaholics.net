<?php
	require_once "init.php";
	
	$uploaddir = "files/";
	$uploadtempdir = "files/tmp/";
	

	if($_GET['merge'] == "true")
	{
		// *** CLEAN UP FILES OLDER THAN ONE MONTH
		$deadline = time() - 12*60*60*31; 
		$handle = opendir($uploadtempdir);
		while (false !== ($file = readdir($handle)))
			if(is_file($uploadtempdir.$file) && filemtime($uploadtempdir.$file) < $deadline)
			{
				unlink($uploadtempdir.$file);
			}
			
		if(!isset($_GET['filename']))
			return;
		
		$fileName = $_GET['filename'];
		$target = $uploaddir.$fileName;
		
		if(file_exists($target) && is_file($target) && !unlink($target))
			die("Couldn't make space for part file - clean up temp directory manually!");
		
		$resourceT = fopen($target, "w");
		for($partIndex = 1; ; $partIndex++)
		{
			$partFileName = $uploadtempdir.$partIndex."_".$fileName;
			if(!file_exists($partFileName))
				break;
				
			$resourceS = fopen($partFileName, "r");
			while(!feof($resourceS))
			{
				$contents = fread($resourceS, 8192);
				fwrite($resourceT, $contents);
			}
			fclose($resourceS);
		}
		fclose($resourceT);
		
		echo "uploadedFileSize=".filesize($target)."Byte";
	}
	else if(sizeof(array_keys($_FILES)) == 1)
	{
		$keys = array_keys($_FILES);
		$partFileName = $keys[0];
		$uploadedFileInfo = $_FILES[$partFileName];
		$target = $uploadtempdir.$uploadedFileInfo['name'];
		
		if(file_exists($target) && !unlink($target))
			die("Couldn't make space for part file - clean up temp directory manually!");
			
		if(!move_uploaded_file($uploadedFileInfo['tmp_name'], $target))
			die("Couldn't move uploaded file!");
	}
	else
	{
		echo "<html>";
		echo "<head><title>UploaderApplet</title></head>";
		echo "<body>";
		echo "<applet code='UploaderApplet' archive='files_script/JFU.jar' height='345' width='325'>";
			echo "<param name='splitSize' value='1024'/>";
			echo "<param name='username' value='".getUser()->nickName."'/>";
			echo "<param name='serverSideScript' value='".$_SERVER['PHP_SELF']."'/>";
			echo "<param name='host' value='".$_SERVER['SERVER_NAME']."'/>";
				echo "This browser does not have a Java Plug-in.<br/>";
				echo "<a href='http://java.sun.com/products/plugin/downloads/index.html'>Get the latest Java Plug-in here.</a>";
		echo "</applet>";
		echo "</body>";
		echo "</html>";
	}		
?>