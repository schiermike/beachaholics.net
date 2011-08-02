<?php

# this is the upload/download plugin, which allows to put arbitrary binary
# files into the ewiki database using the provided specialized form, or the
# standard image upload form below every edit page (if EWIKI_ALLOW_BINARY)


#-- settings
define("EWIKI_UPLOAD_MAXSIZE", 2*1024*1024);
define("EWIKI_PAGE_UPLOAD", "UploadSeite");
define("EWIKI_PAGE_DOWNLOAD", "DownloadSeite");
define("EWIKI_ACTION_ATTACHMENTS", "attachments");  #-- define to 0 to disable

#-- register plugin (main part)
$ewiki_plugins["page"][EWIKI_PAGE_UPLOAD] = "ewiki_page_fileupload";
$ewiki_plugins["page"][EWIKI_PAGE_DOWNLOAD] = "ewiki_page_filedownload";
$ewiki_plugins["action"]["binary"] = "ewiki_binary";
// $ewiki_config["action_links"]["binary_get"]["links"] = 'Linked from';
$ewiki_config["action_links"]["binary_get"]["info"] = 'More Info';

#-- allow per-page downloads
if (defined("EWIKI_ACTION_ATTACHMENTS") && EWIKI_ACTION_ATTACHMENTS) {
	$ewiki_plugins["action"][EWIKI_ACTION_ATTACHMENTS] = "ewiki_action_attachments";
	//$ewiki_config["action_links"]["view"][EWIKI_ACTION_ATTACHMENTS] = "Attachments";
}


#-- icons (best given absolute to www root)
$ewiki_binary_icons = array(
   ".bin"	=> "/icons/exec.gif",
   "application/" => "/icons/exec.gif",
   "application/octet-stream" => "/icons/exec.gif",
   ".ogg"	=> "/icons/son.gif",
   ".jpeg"	=> "/icons/pic.gif",
   "text/"	=> "/icons/txt.gif",
   ".pdf"	=> "/icons/txt.gif",
);


#-- the upload function __can__ use different sections
$ewiki_upload_sections = array(
   "" => "Basisverzeichnis",
   "pictures" => "Bilder",
   "documents" => "Dokumente",
   "stuff" => "Sonstiges",
);

global $result;
$ewiki_t["de"]["UPLOAD0"] = "Mit diesem Formular kannst du beliebige Dateien in das Wiki abspeichern:<br />";
$ewiki_t["de"]["UPL_NEWNAM"] = "Mit unterschiedlichem Dateinamen speichern";
$ewiki_t["de"]["UPL_INSECT"] = "Hochladen in Bereich:";
$ewiki_t["de"]["UPL_TOOLARGE"] = "Deine Datei wurde nicht aufgenommen, weil sie zu groß war!";
$ewiki_t["de"]["UPL_REJSECT"] = 'Der angegebene Download-Bereich "$sect" wird nicht verwendet. Bitte verwende einen von den voreingestellten Bereichen, damit Andere die Datei später auch finden können, oder frag den Administrator das Hochladen für beliebige Seiten zu aktivieren.<br /><br />';
$ewiki_t["de"]["UPL_OK"] = "Deine Datei wurde korrekt hochgeladen [$result], schau einfach auf der <a href='\$script'>".EWIKI_PAGE_DOWNLOAD."</a> nach.<br /><br />";
$ewiki_t["de"]["UPL_ERROR"] = "'Tschuldige, aber irgend etwas ist während des Hochladens gründlich schief gelaufen.<br /><br />";
$ewiki_t["de"]["DWNL_SEEUPL"] = 'Siehe auch <a href="$script'.EWIKI_PAGE_UPLOAD.'">DateiHochladen</a>, auf dieser Seite stehen nur die Downloads.<br /><br />';
$ewiki_t["de"]["DWNL_NOFILES"] = "Noch keine Dateien hochgeladen.<br />\n";
$ewiki_t["de"]["file"] = "Datei";
$ewiki_t["de"]["of"] = "von";
$ewiki_t["de"]["comment"] = "Kommentar";
$ewiki_t["de"]["dwnl_section"] = "Download Bereich";
	




function ewiki_page_fileupload($id, $data, $action, $def_sec="")
{

	global $ewiki_upload_sections, $ewiki_plugins;

	$upload_file = $_FILES[EWIKI_UP_UPLOAD];
	$o = "";
	if($upload_file["size"] > EWIKI_UPLOAD_MAXSIZE) {

		$o .= ewiki_t("UPL_TOOLARGE");

	}
	else if(!empty($upload_file))
	{
		
		$meta = array(
         "X-Content-Type" => $upload_file["type"],
         "Cache-control" => "private", 
		 "X-Content-Length" => $upload_file["size"],
		);
		
		if (($s = $upload_file["name"]) && (strlen($s) >= 3) || ($s = substr(md5(time()+microtime()),0,8) . ".dat"))
		{
			if (strlen($uu = trim($_REQUEST["new_filename"])) >= 3) {
				if ($uu != $s) {
					$meta["Original-Filename"] = $s;
				}
				$s = $uu;
			}
			$meta["Content-Location"] = $s;
			($p = 0) or
			($p = strrpos($s, "/")) and ($p++) or
			($p = strrpos($s, '\\')) and ($p++);
			$meta["Content-Disposition"] = 'attachment; filename="'.urlencode(substr($s, $p)).'"';
		}
		
		if (strlen($sect = $_REQUEST["section"])) 
		{
			if ($ewiki_upload_sections[$sect] || ($action==EWIKI_ACTION_ATTACHMENTS) && ($data["content"]) && strlen($ewiki_plugins["action"][EWIKI_ACTION_ATTACHMENTS])) 
			{
				$meta["section"] = $sect;
			}
			else 
			{
				$o .= ewiki_t("UPL_REJSECT", array('sect' => $sect));
				return($o);
			}
		}
		
		if (strlen($s = trim($_REQUEST["comment"]))) 
		{
			$meta["comment"] = $s;
		}

		$result = ewiki_binary_save_image($upload_file["tmp_name"], "", "RETURN", $meta, "ACCEPT_ALL");

		if ($result) 
		{
			$o .= ewiki_t("Die Datei wurde korrekt hochgeladen [<a href='".ewiki_script_binary($result)."'>".$result."</a>]<br/><br/>");
			ewiki_log("file uploaded to section '$sect'");
		}
		else 
		{
			$o .= ewiki_t("UPL_ERROR");
		}

	}
	 

	$o .= "<p>".ewiki_t("UPLOAD0")."</p>";

	$o .= '<div class="upload">'.
          '<form action="' .
	ewiki_script( ($action!="view" ? $action : ""), $id).
          '" method="POST" enctype="multipart/form-data">' .
          '<b>'.ewiki_t("file").'</b>: <input type="file" name="'.EWIKI_UP_UPLOAD.'"> ';

	if (count($ewiki_upload_sections) > 1 && isset($_REQUEST['section']))
	{
		if (empty($def_sec))
		$def_sec = $_REQUEST["section"];

		$o .= '<select name="section">';
		foreach ($ewiki_upload_sections as $id => $title)
		$o .= '<option value="'.$id.'"' .($id==$def_sec?' selected':''). '>'.$title.'</option>';

		$o .= '</select> ';
	}

	$o .= '<input type="submit" value="Raufladen"><br /><br />';

	$o .= '<b>' . ewiki_t("comment") . '</b><br /><textarea name="comment" cols="60" rows="2"></textarea><br /><br />';

	if (empty($ewiki_upload_sections[$def_sec]))
	$ewiki_upload_sections[$def_sec] = $def_sec;

	$o .= '</form></div>';
	
	$o .= "<div style='text-align:right'><a href='wiki.php?id=".EWIKI_PAGE_DOWNLOAD."'>zur Download-Übersicht</a></div>";

	return($o);
}




function ewiki_page_filedownload($id, $data, $action, $def_sec="") {

	global $ewiki_binary_icons, $ewiki_upload_sections;

	$o = ewiki_make_title($id, $id, 2);

	#-- params (section, orderby)
	($orderby = $_REQUEST["orderby"]) or ($orderby = "created");

	if ($def_sec) 
	{
		$section = $def_sec;
	}
	else 
	{
		($section = $_REQUEST["section"]) or ($section = "");
		if (count($ewiki_upload_sections) > 1) 
		{
			$oa = array();
			$ewiki_upload_sections["*"] = "*";
			if (empty($ewiki_plugins["action"][EWIKI_ACTION_ATTACHMENTS])) 
			{
				$ewiki_upload_sections["**"] = "**";
			}
			foreach ($ewiki_upload_sections as $sec=>$title) 
			{
				$oa[] = '<a href="' . ewiki_script("", $id, array(
               "orderby"=>$orderby, "section" => $sec)) .
               '">' . $title . "</a>";
			}
			$o .= '<div style="text-align:center" class="darker">'.implode(" &middot; ", $oa).'</div><br />';
		}
	}


	#-- collect entries
	$files = array();
	$sorted = array();
	$result = ewiki_db::GETALL(array("flags", "meta", "created", "hits"));

	while ($row = $result->get()) {
		if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_BINARY) {

			$m = &$row["meta"];
			if ($m["section"] != $section) {
				if ($section == "**") {
				}
				elseif (($section == "*") && !empty($ewiki_upload_sections[$m["section"]])) {
				}
				else {
					continue;
				}
			}

			$files[$row["id"]] = $row;
			$sorted[$row["id"]] = $row[$orderby];
		}
	}


	#-- sort
	arsort($sorted);


	#-- slice
	($pnum = $_REQUEST[EWIKI_UP_PAGENUM]) or ($pnum = 0);
	if (count($sorted) > EWIKI_LIST_LIMIT) 
	{
		$o_nl .= '<div class="lighter">&gt;&gt; ';
		for ($n=0; $n < (int)(count($sorted) / EWIKI_LIST_LIMIT); $n++) {
			$o_nl .= '<a href="' . ewiki_script("", $id, array(
           "orderby"=>$orderby, "section"=>$section, EWIKI_UP_PAGENUM=>$n)) .
            '">[' . $n . "]</a>  ";
		}
		$o_nl .= '</div><br />';
		$o .= $o_nl;
	}
	$sorted = array_slice($sorted, $pnum * EWIKI_LIST_LIMIT, EWIKI_LIST_LIMIT);

	#-- output
	if (empty($sorted)) 
	{

		$o .= ewiki_t("DWNL_NOFILES");
	}
	else 
	{
		foreach ($sorted as $id=>$uu) 
		{
			$row = $files[$id];
			$o .= ewiki_entry_downloads($row, $section[0]=="*");
		}
	}

	$o .= $o_nl;
	
	$o .= "<div style='text-align:right'><a href='wiki.php?id=".EWIKI_PAGE_UPLOAD."'>zum Datei-Upload</a></div>";

	return($o);

}


function ewiki_entry_downloads($row, $show_section=0) 
{

	global $ewiki_binary_icons, $ewiki_upload_sections,$ewiki_config;

	$meta = &$row["meta"];

	$id = $row["id"];
	$p_title = basename($meta["Content-Location"]);
	$p_time = strftime("%c", $row["created"]);
	$p_hits = ($uu = $row["hits"] ? $uu : "0");
	$p_size = $meta["size"];
	$p_size = isset($p_size) ? (", " . ($p_size>=4096 ? round($p_size/1024)."K" : $p_size." bytes")) : "";
	$p_ct1 = $meta["Content-Type"];
	$p_ct2 = $meta["X-Content-Type"];
	if ($p_ct1==$p_ct2) { unset($p_ct2); }
	if ($p_ct1 && !$p_ct2) { $p_ct = "<tt>$p_ct1</tt>"; }
	elseif (!$p_ct1 && $p_ct2) { $p_ct = "<tt>$p_ct2</tt>"; }
	elseif ($p_ct1 && $p_ct2) { $p_ct = "<tt>$p_ct1</tt>, <tt>$p_ct2</tt>"; }
	else { $p_ct = "<tt>application/octet-stream</tt>"; }
	$p_section = $ewiki_upload_sections[$meta["section"]];
	$p_section = $p_section ? $p_section : $meta["section"];
	$p_comment = strlen($meta["comment"]) ?
   					"&nbsp;&nbsp;&nbsp;Kommentar: ".str_replace('</p>', '', str_replace('<p>', '', ewiki_format($meta["comment"]))) 
	: "";

	$p_icon = "";
	foreach ($ewiki_binary_icons as $str => $i) {
		if (empty($str) || strstr($row["Content-Location"], $str) || strstr($p_ct, $str) || strstr($p_ct2, $str)) {
			$p_icon = $i;
			$p_icon_t = $str;
		}
	}
	
	$url = ewiki_script_binary("", $row["id"]);
	$o .= "<p><a href='$url'>$p_title</a>";
	$o .= "<small>$p_size, Typ $p_ct";
	$o .= "<div style='text-align:right'>hochgeladen am <b>$p_time</b>, <tt>$p_hits</tt> mal abgerufen</div></small>";
	if($p_comment != "")
		$o .= "Kommentar: ".$p_comment."<br/>";
	$o .= "</p>";
	
	return $o;

	$o .= ewiki_t(
     "DWNL_ENTRY_FORMAT",
	array(
	"id" => $id,
	"size" => $p_size,
	"icon" => ($p_icon ? '<img src="'.$p_icon.'" alt="['.$p_icon_t.']" align="left" width="14" height="14" border="0" /> ' : ''),
	"time" => $p_time,
	"hits" => $p_hits,
	"section" => ($show_section ? ewiki_t('dwnl_section') . ": $p_section<br />" : ''),
	"type" => $p_ct,
	"url" =>  ewiki_script_binary("", $row["id"]),
	"title" => $p_title,
        'comment' => $p_comment,
    'control_links'=> ewiki_control_links_list($id, &$data, $ewiki_config["action_links"]["binary_get"],$data["version"])
	)
	);
}



#------------------------------------------------------- per-page uploads ---


function ewiki_action_attachments($id, $data, $action=EWIKI_ACTION_ATTACHMENTS) {

	if (!empty($_FILES[EWIKI_UP_UPLOAD])) {
		$o .= ewiki_page_fileupload($id, $data, EWIKI_ACTION_ATTACHMENTS, $id);
	}

	$o .= ewiki_page_filedownload(ucwords(EWIKI_ACTION_ATTACHMENTS) . " " . ewiki_t("of") . " $id", $data, "view", $id);

	unset($_FILES[EWIKI_UP_UPLOAD]);
	$o .= ewiki_page_fileupload($id, $data, EWIKI_ACTION_ATTACHMENTS, $id);

	return($o);

}


?>
