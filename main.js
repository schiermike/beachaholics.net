
// reloads the current page and appends the name=value url parameters of the object
function updateSiteParam(object)
{
	href = window.location.href;
	href += href.indexOf('?') == -1 ? '?' : '&';
	href += object.name;
	href += '=';
	href += object.value;
	window.location.href = href;	
}			


// correctly places all objects not part of the main table (navigation panel, logo, etc.)
function placeTables()
{
	obj_table = document.getElementById('layout');
						
	obj_navi = document.getElementById('navi');
	obj_navi.style.visibility='visible';
	obj_navi.style.left = obj_table.offsetLeft + obj_table.offsetWidth - obj_navi.offsetWidth - 10 + 'px';
						
	obj_logo = document.getElementById('logo');
	obj_logo.style.visibility='visible';
	obj_logo.style.left = obj_table.offsetLeft + obj_table.offsetWidth - 20 + 'px';
						
	obj_userinfo = document.getElementById('userinfo');
	obj_userinfo.style.visibility='visible';
	obj_userinfo.style.left = obj_table.offsetLeft + obj_table.offsetWidth + 'px';
}

// setting default function handlers
window.onload = placeTables;
window.onresize = placeTables;


// is used to bookmark the login for faster access
function bookmarkLogin(userid, md5pass)
{
	text="Beachaholics Kufstein";
	url="http://beachaholics.net/login.php?userid=" + userid + "&pass=" + md5pass;
	
	if(document.all && window.external) // ie
	{
		window.external.AddFavorite(url,text);
	}
	else if (window.sidebar) // firefox
	{
		alert("Drücke Strg + D, um die Seite zu bookmarken!");
		window.open(url);
	}
	else if(window.opera && window.print) // opera
	{
		var elem = document.createElement('a');
		elem.setAttribute('href',url);
		elem.setAttribute('title',text);
		elem.setAttribute('rel','sidebar');
		elem.click();
	}
	else 
		alert("Konnte Deinen Browser nicht erkennen - füge das Bookmark einfach manuell hinzu:\n\n" + url);
}


// functionality for periodic page reloading - start with a call to periodicPageReload
var interval=300;		
var startTime=(new Date()).getTime();
function periodicPageReload()
{
	nowTime=(new Date()).getTime();
	passedSeconds=(nowTime-startTime)/1000;
	leftSeconds=Math.round(interval-passedSeconds);

	if (leftSeconds > 0)
	{
		var timer=setTimeout(periodicPageReload,1000);
		window.status='Page refreshing in '+leftSeconds+ ' seconds';
	}
	else
	{
		clearTimeout(timer);
		window.location.reload(true);
	}
}
