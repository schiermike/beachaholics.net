<?php
require_once "init.php";

define('PICTURE_BASE_DIRECTORY', "pics/");
define('THUMB_DIR_NAME', "thumbs/");
define('IMAGE_DIR_NAME', "normalsized/");
define('THUMB_MAX_WIDTH', 145);
define('THUMB_MAX_HEIGHT', 100);
define('IMAGE_MAX_WIDTH', 750);
define('IMAGE_MAX_HEIGHT', 1200);
define('THUMBS_PER_ROW',5);

HP::printPageHead("Bildergallerie", "img/top_gallery.png");

printPage();

HP::printPageTail();

// ===================================================================
// ===================================================================

function printPage() {
	if (isset($_GET['dir'])) {
		if (isset($_GET['file'])) {
			printSinglePicture($_GET['dir'], $_GET['file']);
			
			if (isset($_GET['comment']))
				addComment($_GET['comment'], $_GET['dir'], $_GET['file']);
		}
		else
			printPictureGallery($_GET['dir']);
			
		if (isset($_GET['regenerate']))
			checkAndPreparePictureDirectory($_GET['dir'], false);		
	}
	else
		printPictureDirectories();
}

function printSinglePicture($subdir, $file) {
	if(!file_exists(PICTURE_BASE_DIRECTORY.$subdir)) {
		HP::printErrorText("Kein Ordner namens '$subdir' gefunden!");
		return;
	}
					
	$dir=PICTURE_BASE_DIRECTORY.$subdir.IMAGE_DIR_NAME;
	
	echo "<div align='center'>";
	echo "<table cellpadding='3' cellspacing='0' width='100%'>";
	echo "<tr class='rowColor0'><td>";
	echo "<a href='".$_SERVER['PHP_SELF']."'><img src='img/folderleft.png' alt='dir up' title='zur Verzeichnisauswahl'/></a>";
	echo "&nbsp;&nbsp;&nbsp;".HP::toHtml($subdir);
	echo "</td><td style='text-align:right'>";
	
	$nPics=getNeighborPics($dir, $file);
	
	$dirUrl=HP::toHtml(urlencode($subdir));
	$picUrl=HP::toHtml($dir.$file);
	
	if ($nPics[0])
		echo "<a href='".$_SERVER['PHP_SELF']."?dir=".$dirUrl."&amp;file=".$nPics[0]."'><img src='img/previous.png' alt='zurück' title='vorheriges Bild'/></a>&nbsp;&nbsp;\n";
	echo "<a href='".$_SERVER['PHP_SELF']."?dir=".$dirUrl."'><img src='img/up.png' alt='Übersicht' title='zur Übersicht'/></a>&nbsp;&nbsp;\n";
	if ($nPics[1])
		echo "<a href='".$_SERVER['PHP_SELF']."?dir=".$dirUrl."&amp;file=".$nPics[1]."'><img src='img/next.png' alt='nächstes' title='nächstes Bild'/></a>\n";
	echo "</td></tr>\n";
	echo "<tr class='rowColor1'><td colspan='2' style='text-align:center'>";
	echo "<a href='javascript:window.back()'><img src='".$picUrl."' alt='' title='zurück'/></a>";
	echo "</td></tr>";
	
	echo "</table>";

	echo "</td></tr>";
	echo "</table></div>\n";
}

function isAccessible($dir) {
	if (!getUser()->isGuest())
		return true;
		
	if (file_exists($dir."/visible"))
		return true;
		
	return false;
}

function checkAndPreparePictureDirectory($dirname, $doChecking) {
	$supportedExtensions = array("jpg", "jpeg", "gif", "png");
	$createThumbs=false;
	$createPics=false;
	$dir = PICTURE_BASE_DIRECTORY.$dirname;
	$picdir=$dir.IMAGE_DIR_NAME;
	$thumbdir=$dir.THUMB_DIR_NAME;
	
	// script darf hoechstens eine stunde dran zu kauen haben
	ini_set("max_execution_time",3600);
	
	// exit here when both directories exist (normal procedure)			
	if ($doChecking && is_dir($thumbdir) && is_dir($picdir))
		return true;
		
	if (!is_writable($dir))
		return Log::error("No write access to directory '".$dir."'");
		
	if (is_dir($thumbdir))
		rmdir_recurse($thumbdir);
		
	if (!mkdir($thumbdir, 0755))
		return Log::error("Cannot create thumb-directory '".$thumbdir."'");
		
	if (is_dir($picdir))
		rmdir_recurse($picdir);
		
	if (!mkdir($picdir, 0755))
		return Log::error("Cannot create thumb-directory '".$picdir."'");

	$zipFile = $dir.substr($dirname, 0, strlen($dirname)-1).".zip";
	if (file_exists($zipFile))
		unlink($zipFile);
		
	$files = getOrderedChildren($dir, true, true);
	
	$zip = new ZipArchive();
	if ($zip->open($zipFile, ZipArchive::CREATE)!==TRUE)
		die("Cannot open zip file '".$zipFile."'");
	
	foreach($files as $file) {
		if (!resizeImage($dir.$file, $picdir.$file, IMAGE_MAX_WIDTH, IMAGE_MAX_HEIGHT))
			return Log::error("Error when generating image '".$dir.$file."'");
			
		if (!resizeImage($dir.$file, $thumbdir.$file, THUMB_MAX_WIDTH, THUMB_MAX_HEIGHT))
			return Log::error("Error when generating thumb image '".$dir.$file."'");
			
		$zip->addFile($dir.$file);
	}
	
	$zip->close();

	echo "<script type='text/javascript'>alert('Bilder wurden neu generiert!');</script>";
	return true;
}

function rmdir_recurse($path) {
	$path= rtrim($path, '/').'/';
	$handle = opendir($path);
	while($file = readdir($handle)) {
		if ($file == "." || $file == ".." )
			continue;
		
		$fullpath= $path.$file;
		if (is_dir($fullpath))
			rmdir_recurse($fullpath);
		else
			unlink($fullpath);
	}
	closedir($handle);
	rmdir($path);
}

function getNeighborPics($folder, $pic) {
	$pics = getOrderedChildren($folder, true, true);
	$answer[0]=NULL;
	$answer[1]=NULL;
	
	$pLast=NULL;
	foreach($pics as $pCur) {
		$answer[0]=$pLast;
		$pLast=$answer[1];
		$answer[1]=$pCur;
		if ($pLast==$pic)
			break;
	}
	
	if ($answer[1] == $pic) // we reached the right end
		$answer[1] = NULL;
	else if ($pLast != $pic) // we didn't find the desired pic $pic 
	{
		Log::error("Could not find picture '".$pic."' in folder '".$folder."'");
		$answer[0] = NULL;
		$answer[1] = NULL;
	}
	
	return $answer;
}

function getOrderedChildren($folder, $picFilter=true, $sortAsc=true) {
	$supportedExtensions = array("jpg", "jpeg", "gif", "png");
	// alle Unterverzeichnisse einlesen
	$handle=opendir($folder);
	$i=0;
	$files=array();
	while (false != ($file = readdir($handle))) {
		if ($picFilter) {
			if (!is_file($folder.$file))
				continue;
			if (!in_array(strtolower(substr(strrchr($file,  "." ), 1)), $supportedExtensions))
				continue;
		}
		else {
			if ($file == "." || $file == "..")
				continue;
			if (!is_dir($folder.$file))
				continue;
		}
			
		$files[$i++]=$file;
	}
	
	closedir($handle);
	
	if ($sortAsc)
		asort($files);
	else
		rsort($files);
	
	return $files;
}

function printPictureGallery($subdir) {
	if (!file_exists(PICTURE_BASE_DIRECTORY.$subdir)) {
		HP::printErrorText("Kein Ordner namens '$subdir' gefunden!");
		return;
	}
	
	// path validity check *******
	$dir = PICTURE_BASE_DIRECTORY.$subdir;
	if (strchr($dir, "..") || substr($dir, strlen($dir)-1) != "/")
		return Log::error("Sub-Directory of Picture Gallery is invalid! ('".$dir."')");
	
	echo "<div align='center'>";
	echo "<table cellpadding='3' cellspacing='0' width='100%'>";
	echo "<tr class='rowColor0'><td>";
	echo "<a href='".$_SERVER['PHP_SELF']."'><img src='img/folderleft.png' alt='dir up' title='zur Verzeichnisauswahl'/></a>";
	echo "&nbsp;&nbsp;&nbsp;".HP::toHtml($subdir);
	echo "</td></tr>";
	echo "</table>\n";

	if (!checkAndPreparePictureDirectory($subdir, true))
		return;
		
	if (!isAccessible(PICTURE_BASE_DIRECTORY.$subdir)) {
		echo "<br/><b>NUR FÜR MITGLIEDER SICHTBAR!</b>\n";
		return;
	}

	echo "<table cellpadding='5' cellspacing='0' width='100%'>";
	
	$files = getOrderedChildren($dir.IMAGE_DIR_NAME, true, true);
	
	$colCount = 0;
	foreach ($files as $file) {
		if ($colCount==0)
			echo "<tr>\n";

		$thumbUrl = HP::toHtml($dir.THUMB_DIR_NAME.$file);
		$dirUrl = HP::toHtml(urlencode($subdir));
		$fileUrl = HP::toHtml(urlencode($file));
				
		echo "<td style='text-align:center'>";
		echo "<a href='".$_SERVER['PHP_SELF']."?dir=".$dirUrl."&amp;file=".$fileUrl."'>";
		echo "<img class='commented' src='".$thumbUrl."' alt=''/></a>";
		echo "</td>\n";
				
		if (++$colCount == THUMBS_PER_ROW) {
			$colCount=0;
			echo "</tr>";
		}
	}

	echo "</table>";
	echo "</div>";
}

function printPictureDirectories() {
	// ist das Verzeichnis gueltig?
	if (!is_dir(PICTURE_BASE_DIRECTORY))
		return Log::error("Invalid Picture gallery base directory: '".PICTURE_BASE_DIRECTORY."' - is not a directory");
		
	if (!is_readable(PICTURE_BASE_DIRECTORY))
		return Log::error("Invalid Picture gallery base directory: '".PICTURE_BASE_DIRECTORY."' - is not a readable directory");

	// name muss mit einem '/' aufhoeren
	if (substr(PICTURE_BASE_DIRECTORY, strlen(PICTURE_BASE_DIRECTORY)-1)!="/")
		return Log::error("Picture gallery base directory has to have a '/' char at the end!");
		echo "<div align='center'>";
	echo "<table cellpadding='3' cellspacing='0' width='100%'>";
		
	// alle Unterverzeichnisse einlesen
	$subdirs = getOrderedChildren(PICTURE_BASE_DIRECTORY, false, false);
	
	$switchingColor = 0;
	foreach ($subdirs as $subdir) {	
		echo "<tr class='rowColor".($switchingColor++%2)."'>";	
		echo "<td>";
		echo "<img src='img/imagefolder.png' alt=''/>&nbsp;&nbsp;&nbsp;";
		if (isAccessible(PICTURE_BASE_DIRECTORY.$subdir))
			echo "<a href='".$_SERVER['PHP_SELF']."?dir=".HP::toHtml(urlencode($subdir))."/'>".HP::toHtml($subdir)."</a>";
		else
			echo HP::toHtml($subdir);
		echo "</td>\n";
		echo "<td style='text-align:right'>";
		$zipFile = PICTURE_BASE_DIRECTORY.$subdir."/".$subdir.".zip";
		if (isAccessible(PICTURE_BASE_DIRECTORY.$subdir) && file_exists($zipFile))
			echo "<a href='".$zipFile."'><img alt='zipped section' title='download zipped file' src='img/zip.png'/></a>";
		if (getUser()->id == 1)
			echo "<a href='".$_SERVER['PHP_SELF']."?dir=".HP::toHtml(urlencode($subdir))."/&regenerate=true'><img alt='regenerate section' title='regenerate pictures' src='img/regeneratepics.png'/></a>";				
		echo "</td>\n";
		echo "</tr>";
	}

	echo "</table>";
	echo "</div>";
}

function resizeImage($imagePath, $newImagePath, $maxWidth, $maxHeight) {
	$thumbnailJpegQuality=90;
	$imageInfo = getimagesize($imagePath);
	$imgWidth = $imageInfo[0];
	$imgHeight = $imageInfo[1];
	$imgType = $imageInfo[2];
	
	$newImgWidth = $maxWidth;
	$newImgHeight = $maxHeight;
	
	if ($imgWidth / $imgHeight < $maxWidth / $maxHeight)
		$newImgWidth = round(($maxHeight*$imgWidth)/$imgHeight);
	else
		$newImgHeight = round(($maxWidth*$imgHeight)/$imgWidth);

	$imageResource=NULL;
	switch ($imgType) {
		case IMAGETYPE_GIF:
			$imageResource = imagecreatefromgif($imagePath);
			break;
		case IMAGETYPE_JPEG:
			$imageResource = imagecreatefromjpeg($imagePath);
			break;
		case IMAGETYPE_PNG:
			$imageResource = imagecreatefrompng($imagePath);
			break;
		default:
			return Log::error("Unknown image resource type: ".$imgType." for '".$imagePath."'");
	}
	
	// Create resized image
	$newImageResource = imagecreatetruecolor($newImgWidth,$newImgHeight);
	imagecopyresized($newImageResource, $imageResource, 0, 0, 0, 0, $newImgWidth, $newImgHeight, $imgWidth, $imgHeight);

	// rotate the image according to the EXIF metadata
	$exif = exif_read_data($imagePath);
	if ($exif !== FALSE) {
		$orientation = FALSE;
		if (isset($exif['IFD0']))
			$exif = $exif['IFD0'];
		if (isset($exif['Orientation']))
			$orientation = $exif['Orientation'];
		if ($orientation !== FALSE) {
			switch ($orientation) {
				case 3: // 180°
					$newImageResource = imagerotate($newImageResource, 180, 0);
					break;
				case 6: // rotate right
					$newImageResource = imagerotate($newImageResource, 270, 0);
					break;
				case 8: // rotate left
					$newImageResource = imagerotate($newImageResource, 90, 0);
					break;
			}
		}
	}

	imagejpeg($newImageResource, $newImagePath, $thumbnailJpegQuality);
	
	if (!file_exists($newImagePath))
		return Log::error("Couldn't create resized image '".$newImagePath."'");

	// Destroy image resources
	imagedestroy($imageResource);
	imagedestroy($newImageResource);
	
	return true;
}
?>
