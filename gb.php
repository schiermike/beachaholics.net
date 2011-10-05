<?php

require_once "init.php";
require_once "securimage.php";

define('VISIBLE_GB_PAGES', 20);

$smileyDir = "img/smiley/";
$smileys = array
(
	"[:D]" => array($smileyDir."biggrin.gif", "lautes Lachen"),
	"[:o]" => array($smileyDir."redface.gif", "peinlich"), 
	"[:)]" => array($smileyDir."smile.gif", "lachen"), 
	"[:(]" => array($smileyDir."frown.gif", "nicht happy"),
	"[:yes:]" => array($smileyDir."yes.gif", "yep"),
	"[:confused:]" => array($smileyDir."confused.gif", "verwirrt"),
	"[:mad:]" => array($smileyDir."mad.gif", "verärgert"),
	"[:p]" => array($smileyDir."tongue.gif", "haha!"),
	"[;)]" => array($smileyDir."wink.gif", "zwinkern"),
	"[:winken:]" => array($smileyDir."winken.gif", "winken"),
	"[:rolleyes:]" => array($smileyDir."rolleyes.gif", "sarkastisch"),
	"[:cool:]" => array($smileyDir."cool.gif", "cool"),
	"[:eek:]" => array($smileyDir."eek.gif", "erstaunt"),
	"[:vogel:]" => array($smileyDir."vogel.gif", "bescheuert"),
	"[:tired:]" => array($smileyDir."tired.gif", "pennen"),
	"[:fuckyou:]" => array($smileyDir."finger.gif", "fuck you"),
	"[:shit:]" => array($smileyDir."shit.gif", "shit")
);
$smileysBig = array
(
	"[:baggern:]" => array($smileyDir."baggern.gif", "baggern"),
	"[:pritschen:]" => array($smileyDir."pritschen.gif", "pritschen"),
	"[:service:]" => array($smileyDir."service.gif", "Service"),
	"[:game:]" => array($smileyDir."game.gif", "Spiel"),
	"[:overnet:]" => array($smileyDir."overnet.gif", "übers Netz")
);
define('LINK_START_TAG', "[:link:]");
define('LINK_END_TAG', "[:/link:]");

// ===================================================================

HP::printPageHead("Pinnwand", "img/top_gb.png");
printPage();
HP::printPageTail();

// ===================================================================

function printPage() {	
	if(HP::isParamSet('gbEntriesPerPage'))
		getUser()->setGbEntriesPerPage(HP::getParam('gbEntriesPerPage'));

	switch(HP::getParam('action')) {
		case 'makeEntry':
			makeGBEntry(HP::getParam('userId'), HP::getParam('datetime'), HP::getParam('message'), 
				HP::getParam('visibility'), HP::getParam('sticky'), HP::getParam('captcha_code'), HP::getParam('messageId'));
			break;
		case 'searchEntry':
			searchEntry(HP::getParam('searchString'));
			return;
		case 'printEntryField':
			printEntryField(HP::getParam('messageId'));
			break;
		case 'printSearchField':
			printSearchField();
			break;		
		case 'deleteEntry':
			deleteEntry(HP::getParam('messageId'));
			break;
		default:
			break;
	}
		
	printGuestbook();	
}

function printNavigationField($currentPageIndex, $numRows) {
	echo "<div style='text-align:right'>";
	if (HP::getParam('action') != "printSearchField")
		echo "<a href='".$_SERVER['PHP_SELF']."?action=printSearchField'>suchen <img src='img/search.png' title='Eintrag suchen' alt=''/></a>";
	if (HP::getParam('action') != "printEntryField")
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?action=printEntryField'>eintragen <img src='img/add_gb_entry.png' title='Eintrag hinzufügen' alt=''/></a>";
	echo "</div>\n";			
	
	echo "<table width='100%' cellpadding='8'><tr><td style='text-align:left'>";
	
	$minVisiblePage = $currentPageIndex - VISIBLE_GB_PAGES/2 + 1;
	$minPageReached = false;
	if ($minVisiblePage <= 0) {
		$minVisiblePage = 0;
		$minPageReached = true;
	}
	$maxVisiblePage = $minVisiblePage + VISIBLE_GB_PAGES;
	$maxPageReached = false;

	if ($maxVisiblePage*getUser()->getGbEntriesPerPage() >= $numRows) {
		$maxVisiblePage = $numRows/getUser()->getGbEntriesPerPage();
		$maxPageReached = true;
	}
	$maxVisiblePage = ceil($maxVisiblePage);

	echo "<a href='".$_SERVER['PHP_SELF']."?msg_offset=".($currentPageIndex==0?0:$currentPageIndex-1)."'>&lt;&lt;</a> ";
	
	for ($page=$minVisiblePage; $page<$maxVisiblePage; $page++) {
		echo "<a href='".$_SERVER['PHP_SELF']."?msg_offset=".$page."'>";
		if ($currentPageIndex == $page)
			echo "<u>".($page+1)."</u>";
		else
			echo $page+1;
		echo "</a>\n ";
	}

	echo "<a href='".$_SERVER['PHP_SELF']."?msg_offset=".($currentPageIndex==$maxVisiblePage-1?$maxVisiblePage-1:$currentPageIndex+1)."'>&gt;&gt;</a> ";
		
	echo "</td><td style='text-align:right'>";
	
	echo "Einträge pro Seite: <select name='gbEntriesPerPage' onchange='updateSiteParam(this)'>";
	foreach (array(3, 5, 10, 15, 20, 50) as $numEntries) {
		echo "<option value='".$numEntries."'";
		if (getUser()->getGbEntriesPerPage() == $numEntries)
			echo " selected='selected'";
		echo ">".$numEntries."</option>";
	}
	
	echo "</select>";
	echo "</td></tr></table>";	
}

// -----------------------------------------------------------------------------------------

function makeGBEntry ($userId, $datetime, $message, $visibility, $sticky, $captcha_code, $messageId=NULL) {
	$sticky = $sticky == "sticky" ? "TRUE" : "FALSE";
	if ($userId != getUser()->id && !getUser()->isItMe()) {
		HP::printErrorText("Unzureichende Rechte zum Editieren dieser Nachricht!");
		return false;	
	}
	
	if (!getUser()->isAuthorized($visibility)) {
		HP::printErrorText("Keine Berechtigung zum Eintragen einer solchen Nachricht!");
		return false;
	}
	
	if (getUser()->isGuest() && isset($_SESSION['securimage_code_value']) && 
		$_SESSION['securimage_code_value'] != strtolower($captcha_code)) {
		echo "'" . $_SESSION['securimage_code_value'] . "' != '" . strtolower($captcha_code) . "'";
		HP::printErrorText("Falscher Captcha-Code - versuch es nochmals!");
		return false;
	}
	$_SESSION['securimage_code_value'] = '';
	
	if ($messageId == NULL) {
		// check whether no "refresh" has been performed to avoid duplicate entries
		$sql= "SELECT message FROM guestbook WHERE user_id=" . esc($userId) . " AND time=" . esc($datetime);
		$request = getDB()->query($sql);
		if ($row = mysql_fetch_assoc($request))
			return true;
	}
	
	if ($message=="") {
		HP::printErrorText("Leere Nachrichten sind nicht erlaubt!");
		return false;
	}
			
	$trans = array("'" => "\"");
	$message = strtr($message, $trans);
	$sql="";
	if ($messageId == NULL)
		$sql = "INSERT INTO guestbook (time, user_id, message, visibility, sticky) VALUES (" . 
			esc($datetime) . "," . esc($userId) . "," . esc($message) . "," . esc($visibility) . "," . esc($sticky) . ")";
	else
		$sql = "UPDATE guestbook SET message=" . esc($message) . ", visibility=" . esc($visibility) . 
			", sticky=" . esc($sticky) . " WHERE user_id=" . esc($userId) . " AND id=" . esc($messageId);
		
	$request = getDB()->query($sql);
	
	return true;
}

// -----------------------------------------------------------------------------------------

function deleteEntry($messageId) {
	if (getUser()->isItMe())
		getDB()->query("DELETE FROM guestbook WHERE id=" . esc($messageId));
	else if (!getUser()->isGuest())
		getDB()->query("DELETE FROM guestbook WHERE user_id=" . esc(getUser()->id) . " AND id=" . esc($messageId));
}

// -----------------------------------------------------------------------------------------

function printSearchField($searchString = NULL) {
	echo "<div style='text-align:right'>";
	echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
	echo "<b>Suchbegriff:</b>";
	echo "<input type='hidden' name='action' value='searchEntry'/>\n";
	echo "<input type='text' name='searchString' value='" . ($searchString == NULL ? "" : $searchString) . "'/>\n";
	echo "<input type='submit' value='suchen'/>\n";
	echo "</form>";
	echo "</div>";
}

// -----------------------------------------------------------------------------------------

function searchEntry($searchString) {
	printSearchField($searchString);
	
	if (strlen($searchString) <= 3) {
		HP::printErrorText("Der Suchbegriff ist zu kurz!");
		return;
	}
	
	$searchString = str_replace("'", "\"", $searchString);
	
	$request = getDB()->query("SELECT guestbook.id AS guestbook_id, nickname, time, message, user_id, visibility, sticky
		FROM guestbook JOIN user ON user_id=user.id
		WHERE ( visibility = 0 OR (visibility & " . esc(getUser()->roles) . ") > 0 )
		AND message LIKE '%" . esc($searchString, false) . "%' 
		ORDER BY time DESC");
		
	echo "\n<table id='guestbook' cellspacing='0' cellpadding='0'>";
	$rowc=0;
	while ($row = mysql_fetch_assoc($request))
		printGuestbookRow($row);
	echo "</table>";
}

// -----------------------------------------------------------------------------------------

function printEntryField($messageId = NULL) {
	$userId = getUser()->id;
	$messageText="";
	$sticky = false;
	$visibility = User::$ROLE_MEMBER;
	$buttonLabel="Eintragen";
	if ($messageId != NULL) {
		$sql= "SELECT user_id, message, visibility, sticky FROM guestbook WHERE id=" . esc($messageId);
		$request = getDB()->query($sql);
		if ($row = mysql_fetch_assoc($request)) {
			$userId = $row['user_id'];
			$messageText = $row['message'];
			$visibility = $row['visibility'];
			$sticky = $row['sticky'];
		}
		$buttonLabel="Bestätigen";
	}
	
	echo "<script type='text/javascript'>";
	echo "function appendText(text)";
	echo "{ document.getElementById('messageTextArea').value += text; } ";
	echo "function insertUrl()";
	echo "{ var link = prompt('Link hier eingeben:', 'http://...'); if(link != null) appendText('".LINK_START_TAG."' + link + '".LINK_END_TAG."'); }";
	echo "</script>";
	
	echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
	echo "<b>Nachricht:</b>";
	echo "<br/><center><textarea id='messageTextArea' name='message' style='width:80%' cols='1' rows='6'>".HP::toHtml($messageText)."</textarea></center>";
	echo "<input type='hidden' name='action' value='makeEntry'/>\n";
	echo "<input type='hidden' name='datetime' value='".HP::getPHPTime()."'/>\n";
	echo "<input type='hidden' name='messageId' value='".$messageId."'/>\n";
	echo "<input type='hidden' name='userId' value='".$userId."'/>\n";
	echo "<script language='javascript'>
		function showBigSmileys() { 
			div_style = document.getElementById('bigSmiley').style;
			if(div_style.height == '')	{ 
				div_style.visibility='hidden'; 
				div_style.height='0px';
			}
			else {
				div_style.visibility='visible'; 
				div_style.height='';
			}
		}
		</script>";
	echo "<p style='text-align:center'>";
	global $smileys, $smileysBig;
	foreach ($smileys as $smText => $smIconName)
		echo "<a href='javascript:appendText(\"".$smText."\")'><img src='".$smIconName[0]."' alt='' title='".$smIconName[1]."'/></a> \n";

	echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:showBigSmileys();'>*</a>";
	echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:insertUrl()'><img src='img/link.gif' alt='' title='link einfügen'/></a>\n";
	echo "</p>\n";
		
	echo "<p id='bigSmiley' style='text-align:center; visibility:hidden; height:0px;'>";
	foreach ($smileysBig as $smText => $smIconName)
		echo "<a href='javascript:appendText(\"".$smText."\")'><img src='".$smIconName[0]."' alt='' title='".$smIconName[1]."'/></a> \n";
	echo "</p>\n";
		
	if (getUser()->isGuest()) {
		echo "<p style='text-align:center'>";
		echo "<img src='securimage.php?captchaImage' alt=''/><br/>";
		echo "<input type='text' name='captcha_code' value=''/>\n";
	echo "</p>\n";	
	}
	echo "<p style='text-align:right'>";
	echo "Als sticky setzen: <input type='checkbox' name='sticky' value='sticky'".($sticky?" checked='checked'":"")."'/>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "Sichtbarkeit: <img src='img/groupkey.png' alt=''/> ";
	echo "<select name='visibility'>";
	foreach (User::getRoles() as $role) {
		if (!getUser()->isAuthorized($role))
			continue;
		echo "<option value='".$role."' ".($role == $visibility ? "selected='selected'" : "").">".User::roleToString($role)."</option>";
	}
	echo "</select>\n";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$buttonLabel."'/>";
	echo "</p>\n";
	echo "</form>";
}

// -----------------------------------------------------------------------------------------

function convertKeywords($string) {
	global $smileys, $smileysBig;
	foreach ($smileys as $smText => $smIconName)
		$string = str_replace($smText, "<img src='".$smIconName[0]."' alt=''/>", $string);
	foreach ($smileysBig as $smText => $smIconName)
		$string = str_replace($smText, "<img src='".$smIconName[0]."' alt=''/>", $string);
	
	while (true) {
		$start = strpos($string, LINK_START_TAG);
		if ($start === false)
			break;
		$end = strpos($string, LINK_END_TAG, $start);
		if ($end === false)
			break;
		
		$url = substr($string, $start + strlen(LINK_START_TAG), $end - $start - strlen(LINK_START_TAG));
		$string = substr($string, 0, $start) . "<a href='" . $url . "'>" . $url . "</a>" . substr($string, $end + strlen(LINK_END_TAG));
	}
	return $string;
}

// -----------------------------------------------------------------------------------------

function printGuestbookRow($row) {
	echo "<tr>";
	echo "<td style='border-width: 1px; border-color: black; border-top-style: solid; border-bottom-style: solid; border-left-style: solid; vertical-align: top; background-color: #d0d0d0;'>";
	echo "<img src='userpic.php?id=".$row['user_id']."' width='".User::$PIC_WIDTH."' height='".User::$PIC_HEIGHT."' alt=''/>";
	echo "</td>";
	echo "<td style='width: 100%; border-width: 1px; border-color: black; border-top-style: solid; border-bottom-style: solid; border-right-style: solid;'>";

	echo "<table cellpadding='0' cellspacing='0' width='100%' style='height: 136px'>";
	echo "<tr>";
	echo "<td style='text-align: left; font: 8pt/120% sans-serif; padding-left: 3px; background-color: #aaaaaa;'>";
	echo "<b>".HP::toHtml($row['nickname'])."</b>&nbsp;&nbsp;";

	if ($row['user_id'] == getUser()->id && !getUser()->isGuest() || getUser()->isItMe()) {	
		$url = $_SERVER['PHP_SELF']."?action=printEntryField&messageId=".$row['guestbook_id'];
		echo "&nbsp;<a href='".$url."'><img src='img/edit_gb_entry.png' alt='editieren' title='Eintrag editieren'/></a>";
		$url = $_SERVER['PHP_SELF']."?action=deleteEntry&messageId=".$row['guestbook_id'];
		echo "&nbsp;<a href='".$url."'><img src='img/delete_gb_entry.png' alt='löschen' title='Eintrag löschen'/></a>";
	}
	echo "</td>";
	echo "<td style='text-align:right; font: 8pt/120% sans-serif; white-space: nowrap; background-color: #aaaaaa;'>";
	if ($row['sticky'] == true)
		echo "<img src='img/sticky.gif' alt='Sticky message' title='Wichtige Nachricht'/>&nbsp;&nbsp;";
	echo "<img src='img/groupkey.png' alt='' title='Sichtbarkeit der Nachricht'/> ".User::roleToString($row['visibility']) . ",&nbsp;&nbsp;";
	echo HP::formatDate($row['time'], true)." <img src='img/clock.png' alt='' title='Zeitpunkt des Eintrags'/> - #".$row['guestbook_id'];
	echo "</td>\n";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='2' style='width: 100%; height: 100%; text-align: left; padding-left: 10px; background-color: #e0e0e0;'>".convertKeywords(HP::toHtml($row['message'], true))."</td>\n";
	echo "</tr>\n";
	echo "</table>";

	echo "</td>";
	echo "</tr>";
	echo "<tr style='height: 5px;'><td colspan='2'/></tr>";
}

// -----------------------------------------------------------------------------------------

function printGuestbook() {
	$msg_offset = 0;
	if (HP::isParamSet('msg_offset'))
		$msg_offset = HP::getParam('msg_offset');

	// Anzahl der Eintraege erfragen
	$sql = "SELECT COUNT(*) FROM guestbook WHERE (visibility & " . esc(getUser()->roles) . ") > 0 OR visibility=0";
	$request = getDB()->query($sql);
	$row = mysql_fetch_assoc($request);
	$num_rows = $row['COUNT(*)'];
	
	printNavigationField($msg_offset, $num_rows);
	
	$sql = "SELECT guestbook.id AS guestbook_id, nickname, time, message, user_id, visibility, sticky
		FROM guestbook JOIN user ON user_id=user.id WHERE visibility = 0 OR (visibility & " . 
		esc(getUser()->roles) . ") > 0 ORDER BY sticky DESC, time DESC LIMIT " . 
		esc($msg_offset*getUser()->getGbEntriesPerPage()) . " , " . esc(getUser()->getGbEntriesPerPage());
	
	$request = getDB()->query($sql);
	
	echo "\n<table cellspacing='0' cellpadding='0' style='width: 100%'>";
	while ($row = mysql_fetch_assoc($request))
		printGuestbookRow($row);
	echo "</table>";
	
	printNavigationField($msg_offset, $num_rows);
}
?>
