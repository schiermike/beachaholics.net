<?php
	require_once "init.php";
  	
  	HP::printPageHead("Datenaustausch", "img/top_files.png", "files_script/styles.css");
  	
  	global $showFtp;
  	if(!getUser()->isGuest())
  	{
  		if($showFtp == "true")
  			printFtpDescription();
  		else
  		{
  			printFileList();
  			printUploadLinks();
  		}
  	}
  	else
  		HP::printLoginError();
  	
  	HP::printPageTail();
  	
  	function printUploadLinks()
  	{
  		echo "<center>";
		echo "<a href=\"javascript:window.open('uploader.php', 'uploaderWindow', 'width=350, height=350, status=no,scrollbars=no,resizable=no').focus();\">Java Dateiupload</a>";
		echo "&nbsp;&nbsp;&nbsp;";
		echo "<a href='".$_SERVER['PHP_SELF']."?showFtp=true'>FTP Dateiupload</a>";
		echo "</center>";
  	}
  	
  	function printFtpDescription()
  	{
  		echo "<p>Daten können auch mit Hilfe des File-Transfer Protokolls auf den Beachaholics-Server hochgeladen werden. Die dazu benötigten Zugangsdaten sind:</p>";
  		
  		echo "<p>";
  		echo "Server-Adresse: <b>beachaholics.net</b><br/>";
  		echo "Benutzername: <b>".getUser()->nickName."</b><br/>";
  		echo "Passwort: <b>Dein Beachaholics-Passwort</b><br/>";
  		echo "<p/>";
  		
  		echo "Screenshots eines exemplarischen Upload-Vorganges finden sich hier: <h1>";
  		for($i=1;$i<=6;$i++)
  			echo "<a href='img/ftp_step$i.jpg' target='_blank'><font size='16pt'>$i</font></a>&nbsp;&nbsp;";
  		echo "</h1><br/>";
  		
  		echo "<p>Falls du noch nie etwas mit FTP zu tun hattest, hier eine kleine Anleitung dazu:</p>";
  		
  		echo "<p><b>Schritt 1:</b> Besorg dir einen anständiges Client Programm: <a href='http://prdownloads.sourceforge.net/filezilla/FileZilla_3.2.0_win32-setup.exe'>FileZilla Download</a>";
  		echo "<p><b>Schritt 2:</b> Starte das Programm - du findest es im Startmenü oder auf dem Desktop oder wo auch immer</p>";
  		echo "<p><b>Schritt 3:</b> Gib die Server-Adresse, Benutzername und Passwort ein und klicke auf 'Verbinden'. Nun sollte im rechten Teil des Fensters das Serververzeichnis erscheinen.</p>";
  		echo "<p><b>Schritt 4:</b> Die linke Seite des Programms stellt dein lokales Dateiverzeichnis dar. Navigiere zu der Datei, die du raufladen willst und klicke auf sie doppelt.</p>";
  		echo "<p><b>Schritt 5:</b> Warte ab, bis die Übertragung fertig ist (Statusfenster im unteren Teil). Erst dann darfst du das Programm wieder schließen.</p>";
  		echo "<p><b>Schritt 6:</b> Check nochmal, ob alles am Server liegt (Dateien, Dateigrößen, ev. Fehlermeldungen).</p>";
  		echo "<br/>";
  		echo "<br/>";
  		echo "<p>Falls die Beschreibung noch immer nicht ausreichen sollte, findest du <a href='http://www.filezilla.de/schnelleinstieg.htm' target='_blank'>hier</a> mehr Informationen!</p>";
  	}
  	
	function printFileList()
	{
		/*
		Directory Listing Script - Version 2
		====================================
		Script Author: Ash Young <ash@evoluted.net>. www.evoluted.net
		Layout: Manny <manny@tenka.co.uk>. www.tenka.co.uk
		
		CONFIGURATION
		=============
		Edit the variables in this section to make the script work as
		you require.
		
		Start Directory - To list the files contained within the current
		directory enter '.', otherwise enter the path to the directory
		you wish to list. The path must be relative to the current
		directory.
		*/
		$file_location = 'files/';
		
		/*
		Show Thumbnails? - Set to true if you wish to use the
		scripts auto-thumbnail generation capabilities.
		This requires that GD2 is installed.
		*/
		$showthumbnails = true;
		
		/*
		Show Directories - Do you want to make subdirectories available?
		If not set this to false
		*/
		$showdirs = true;
		
		/*
		Force downloads - Do you want to force people to download the files
		rather than viewing them in their browser?
		*/
		$forcedownloads = false;
		
		/*
		Hide Files - If you wish to hide certain files or directories
		then enter their details here. The values entered are matched
		against the file/directory names. If any part of the name
		matches what is entered below then it is now shown.
		*/
		$hide = array(
						'files_script',
						'index.php',
						'Thumbs',
						'.htaccess',
						'.htpasswd'
					);
		
		/*
		Show index files - if an index file is found in a directory
		to you want to display that rather than the listing output
		from this script?
		*/
		$displayindex = false;
		
		/*
		Allow uploads? - If enabled users will be able to upload
		files to any viewable directory. You should really only enable
		this if the area this script is in is already password protected.
		*/
		$allowuploads = false;
		
		/*
		Overwrite files - If a user uploads a file with the same
		name as an existing file do you want the existing file
		to be overwritten?
		*/
		$overwrite = false;
		
		/*
		Index files - The follow array contains all the index files
		that will be used if $displayindex (above) is set to true.
		Feel free to add, delete or alter these
		*/
		
		$indexfiles = array (
						'index.html',
						'index.htm',
						'default.htm',
						'default.html'
					);
		
		/*
		File Icons - If you want to add your own special file icons use
		this section below. Each entry relates to the extension of the
		given file, in the form <extension> => <filename>.
		These files must be located within the files_script directory.
		*/
		$filetypes = array (
						'png' => 'jpg.gif',
						'jpeg' => 'jpg.gif',
						'bmp' => 'jpg.gif',
						'jpg' => 'jpg.gif',
						'gif' => 'gif.gif',
						'zip' => 'archive.png',
						'rar' => 'archive.png',
						'exe' => 'exe.gif',
						'setup' => 'setup.gif',
						'txt' => 'text.png',
						'htm' => 'html.gif',
						'html' => 'html.gif',
						'fla' => 'fla.gif',
						'swf' => 'swf.gif',
						'xls' => 'xls.gif',
						'doc' => 'doc.gif',
						'sig' => 'sig.gif',
						'fh10' => 'fh10.gif',
						'pdf' => 'pdf.gif',
						'psd' => 'psd.gif',
						'rm' => 'real.gif',
						'mpg' => 'video.gif',
						'mpeg' => 'video.gif',
						'mov' => 'video2.gif',
						'avi' => 'video.gif',
						'eps' => 'eps.gif',
						'gz' => 'archive.png',
						'asc' => 'sig.gif',
					);
		
		/*
		That's it! You are now ready to upload this script to the server.
		
		Only edit what is below this line if you are sure that you know what you
		are doing!
		*/
		error_reporting(0);
		if(!function_exists('imagecreatetruecolor')) $showthumbnails = false;
		
		if($_GET['dir']) {
			//check this is okay.
		
			if(substr($_GET['dir'], -1, 1)!='/') {
				$_GET['dir'] = $_GET['dir'] . '/';
			}
		
			$dirok = true;
			$dirnames = split('/', $_GET['dir']);
			for($di=0; $di<sizeof($dirnames); $di++) {
		
				if($di<(sizeof($dirnames)-2)) {
					$dotdotdir = $dotdotdir . $dirnames[$di] . '/';
				}
		
				if($dirnames[$di] == '..') {
					$dirok = false;
				}
			}
		
			if(substr($_GET['dir'], 0, 1)=='/') {
				$dirok = false;
			}
		
			if($dirok)
				 $leadon = $_GET['dir'];
			else
			{
				echo HP::printErrorText("Files Section is currently unavailable!");
				return;
			}
		}
		
		if($_GET['download'] && $forcedownloads) {
			$file = str_replace('/', '', $_GET['download']);
			$file = str_replace('..', '', $file);
		
			if(file_exists($file_location . $leadon . $file)) {
				header("Content-type: application/x-download");
				header("Content-Length: ".filesize($file_location . $leadon . $file));
				header('Content-Disposition: attachment; filename="'.$file.'"');
				readfile($file_location . $leadon . $file);
				die();
			}
		}
		
		if($allowuploads && $_FILES['file']) {
			$upload = true;
			if(!$overwrite) {
				if(file_exists($file_location .$leadon.$_FILES['file']['name'])) {
					$upload = false;
				}
			}
		
			if($upload) {
				move_uploaded_file($_FILES['file']['tmp_name'], $file_location . $leadon . $_FILES['file']['name']);
			}
		}
		
		$opendir = $file_location . $leadon;
		if(! ($file_location . $leadon) ) $opendir = '.';
		if(!file_exists($opendir)) 
		{
			echo HP::printErrorText("Files Section is currently unavailable!");
			return;
		}
		
		clearstatcache();
		if ($handle = opendir($opendir)) {
			while (false !== ($file = readdir($handle))) {
				//first see if this file is required in the listing
				if ($file == "." || $file == "..")  continue;
				$discard = false;
				for($hi=0;$hi<sizeof($hide);$hi++) {
					if(strpos($file, $hide[$hi])!==false) {
						$discard = true;
					}
				}
		
				if($discard) continue;
				if (@filetype($file_location . $leadon.$file) == "dir") {
					if(!$showdirs) continue;
		
					$n++;
					if($_GET['sort']=="date") {
						$key = @filemtime($file_location .$leadon.$file) . ".$n";
					}
					else {
						$key = $n;
					}
					$dirs[$key] = $file . "/";
				}
				else {
					$n++;
					if($_GET['sort']=="date") {
						$key = @filemtime($file_location .$leadon.$file) . ".$n";
					}
					elseif($_GET['sort']=="size") {
						$key = @filesize($file_location .$leadon.$file) . ".$n";
					}
					else {
						$key = $n;
					}
					$files[$key] = $file;
		
					if($displayindex) {
						if(in_array(strtolower($file), $indexfiles)) {
							header("Location: $file");
							die();
						}
					}
				}
			}
			closedir($handle);
		}
		
		//sort our files
		if($_GET['sort']=="date") {
			@ksort($dirs, SORT_NUMERIC);
			@ksort($files, SORT_NUMERIC);
		}
		elseif($_GET['sort']=="size") {
			@natcasesort($dirs);
			@ksort($files, SORT_NUMERIC);
		}
		else {
			@natcasesort($dirs);
			@natcasesort($files);
		}
		
		//order correctly
		if($_GET['order']=="desc" && $_GET['sort']!="size") {$dirs = @array_reverse($dirs);}
		if($_GET['order']=="desc") {$files = @array_reverse($files);}
		$dirs = @array_values($dirs); $files = @array_values($files);
		
		// START OF HTML OUTPUT

		if($showthumbnails) 
		{
			echo "<script language='javascript' type='text/javascript'>\n";
			echo "function o(n, i) { document.images['thumb'+n].src = 'files_script/i.php?f='+i; }\n";
			echo "function f(n) { document.images['thumb'+n].src = 'files_script/trans.gif'; }\n";
			echo "</script>";
		}
		
		echo "<div id='breadcrumbs'>";
			echo "<a href='".$_SERVER['PHP_SELF']."'>home</a>";
		
			$breadcrumbs = split('/', $leadon);
			if(($bsize = sizeof($breadcrumbs))>0) 
			{
				$sofar = '';
				for($bi=0;$bi<($bsize-1);$bi++) 
				{
					$sofar = $sofar . $breadcrumbs[$bi] . '/';
					echo " &gt; <a href='".$_SERVER['PHP_SELF']."?dir=".urlencode($sofar)."'>".$breadcrumbs[$bi]."</a>";
				}
			}

			$baseurl = $_SERVER['PHP_SELF'] . "?dir=".$_GET['dir']."&amp;";
		echo "</div>";
		

			
		echo "<div id='listing'>";
		
		$class = "b";
		if($dirok) 
		{
			echo "<div>";
				echo "<a href='".$_SERVER['PHP_SELF']."?dir=".urlencode($dotdotdir)."' class='".$class."'>";
					echo "<img src='files_script/dirup.png' alt='Folder'/>";
					echo "<strong>..</strong> <em>-</em> ".date("M d Y h:i:s A", filemtime($dotdotdir));
				echo "</a>";
			echo "</div>";
	
			$class = $class = "b" ? "w" : "b";
		}
	
		$arsize = sizeof($dirs);
		for($i=0;$i<$arsize;$i++) 
		{
			echo "<div>";
				echo "<a href='".$_SERVER['PHP_SELF']."?dir=".urlencode($leadon.$dirs[$i])."' class='".$class."'>";
					echo "<img src='files_script/folder.png' alt='".$dirs[$i]."'/>";
					echo "<strong>".$dirs[$i]."</strong> <em>-</em> ".date("M d Y h:i:s A", filemtime($file_location .$leadon.$dirs[$i]));
				echo "</a>";
			echo "</div>";
		
			$class = $class = "b" ? "w" : "b";
		}
	
		$arsize = sizeof($files);
		for($i=0;$i<$arsize;$i++) 
		{
			$icon = "unknown.png";
			$ext = strtolower(substr($files[$i], strrpos($files[$i], '.')+1));
			$supportedimages = array('gif', 'png', 'jpeg', 'jpg');
			$thumb = "";
	
			if($showthumbnails && in_array($ext, $supportedimages)) 
			{
				$thumb = "<span><img src='files_script/trans.gif' alt='".$files[$i]."' name='thumb".$i."' /></span>";
				$thumb2 = " onmouseover='o(".$i.", \"".urlencode($file_location . $leadon . $files[$i])."\");' onmouseout='f(".$i.");'";
			}
	
			if($filetypes[$ext])
				$icon = $filetypes[$ext];
	
			$filename = $files[$i];
			if(strlen($filename)>43)
				$filename = substr($files[$i], 0, 40) . "...";
	
			$fileurl = $file_location . $leadon . $files[$i];
			if($forcedownloads)
				$fileurl = $_SESSION["PHP_SELF"] . "?dir=" . urlencode($file_location .$leadon) . "&download=" . urlencode($files[$i]);
				
			echo "<div>";
				echo "<a href='".$fileurl."' class='".$class."'".$thumb2.">";
					echo "<img src='files_script/".$icon."' alt='".$files[$i]."'/>";
					echo "<strong>".$filename."</strong> <em>".round(filesize($file_location .$leadon.$files[$i])/1024)."KB</em> ".date('M d Y h:i:s A', filemtime($file_location .$leadon.$files[$i])).$thumb;
				echo "</a>";
			echo "</div>";
			
			$class = $class = "b" ? "w" : "b";
		}
		echo "</div>";
		echo "<br/>";
	}
	