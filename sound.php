<?php

switch($_GET['id'])
{
	case 'newmessage':
	default:
		echo "<embed src='sound/newMessage.wav' autostart='true' controls='false' loop='false' width='0' height='0' style='visibility:hidden;'></embed>";
		break;			
}

?>