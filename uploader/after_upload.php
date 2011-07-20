<?php
/**
 * This page is displayed by the applet, once all files are uploaded. You can add here any post-treatment you need.
 * 
 * This page content: just display the list of uploaded files. 
 */


session_start();

echo("<H3>List of uploaded files</H3>");
$files = $_SESSION['juvar.files'];
echo ('Nb uploaded files is: ' . sizeof($files));
echo('<table border="1"><TR><TH>Filename</TH><TH>file size</TH><TH>md5sum</TH></TR>');
foreach ($files as $f) {
    echo('<tr><td>');
    echo($f['name']);
    echo('</td><td>');
    echo($f['size']);
    echo('</td><td>');
    echo($f['md5sum']);
    echo('</td></tr>');
    echo("\n");
}
echo("</table>\n");

?>
<P><A HREF="index.php">Go back to the upload page</A></P>
