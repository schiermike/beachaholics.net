

/*
 * returns the URL of the current displayed site without URL parameters
 * Hope this one works ;-)
 */
function getCurrentURL()
{
	var url = window.location.href;
	var pos = url.indexOf('?');
	if(pos == -1)
		return url;
	return url.substr(0, pos);
}

// ------------------------------------------------------------------------------------------------

/*
 * Returns a new instance of XMLHttpRequest - browser dependent behaviour encapsulation.
 */
function getNewXMLHttpRequest()
{
	// Mozilla, Opera, Safari sowie Internet Explorer (ab v7)
	if (typeof XMLHttpRequest != 'undefined')
		return new XMLHttpRequest();

	// Internet Explorer 6 und älter
	try
	{
		return new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch(e) {}

	try
	{
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	catch(e) {}

	alert("This browser cannot work with asynchronous javascript calls using the XMLHttpRequest object!\nDamn man, how old is your computer??? Go out and buy a new one!");
	return null;
}

// ------------------------------------------------------------------------------------------------

/*
 * Requests the content of requestURL by making a GET or POST request depending on the parameters getParams and postParams.
 * Upon receipt of the answer, the function callback will be called
 */
function makeRequest(requestURL, callback, getParams, postParams)
{
	var request = getNewXMLHttpRequest();
	if(getParams != null)
		requestURL += "?" + getParams;

	request.onreadystatechange = function ()
	{
		if (request.readyState == 4 && callback != null)
		{
//			alert("Answer: " + request.responseText);
			callback(request.responseText);
		}
	};
//	alert("makeRequest: " + requestURL);
	if(postParams == null)
	{
		request.open('GET', requestURL, true);
		request.send(null);
	}
	else
	{
		request.open('POST', requestURL, true);
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.setRequestHeader("Content-length", postParams);
		request.setRequestHeader("Connection", "close");
		request.send(postParams);
	}
}

// ------------------------------------------------------------------------------------------------


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

// ------------------------------------------------------------------------------------------------	

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


