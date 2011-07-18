

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

function setResponse()
{
	userId = document.getElementById('userid').value;
	challengeB64 = document.getElementById('challenge').value;
	passWord = document.getElementById('password').value;
	challengePlain = base64Decode(challengeB64);
	responsePlain = rc4Crypt(passWord, challengePlain);
	responseB64 = base64Encode(responsePlain);
	document.getElementById('response').value = responseB64;
}

// ------------------------------------------------------------------------------------------------	

// symmetric en/decryption using the Rivest Cipher 4
function rc4Crypt(key, pt) {
	s = new Array();
	for (var i=0; i<256; i++) {
		s[i] = i;
	}
	var j = 0;
	var x;
	for (i=0; i<256; i++) {
		j = (j + s[i] + key.charCodeAt(i % key.length)) % 256;
		x = s[i];
		s[i] = s[j];
		s[j] = x;
	}
	i = 0;
	j = 0;
	var ct = '';
	for (var y=0; y<pt.length; y++) {
		i = (i + 1) % 256;
		j = (j + s[i]) % 256;
		x = s[i];
		s[i] = s[j];
		s[j] = x;
		ct += String.fromCharCode(pt.charCodeAt(y) ^ s[(s[i] + s[j]) % 256]);
	}
	return ct;
}

// ------------------------------------------------------------------------------------------------	

//Encodes data to Base64 format
function base64Encode(data){
	if (typeof(btoa) == 'function') return btoa(data);//use internal base64 functions if available (gecko only)
	var b64_map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var byte1, byte2, byte3;
	var ch1, ch2, ch3, ch4;
	var result = new Array(); //array is used instead of string because in most of browsers working with large arrays is faster than working with large strings
	var j=0;
	for (var i=0; i<data.length; i+=3) {
		byte1 = data.charCodeAt(i);
		byte2 = data.charCodeAt(i+1);
		byte3 = data.charCodeAt(i+2);
		ch1 = byte1 >> 2;
		ch2 = ((byte1 & 3) << 4) | (byte2 >> 4);
		ch3 = ((byte2 & 15) << 2) | (byte3 >> 6);
		ch4 = byte3 & 63;
		
		if (isNaN(byte2)) {
			ch3 = ch4 = 64;
		} else if (isNaN(byte3)) {
			ch4 = 64;
		}

		result[j++] = b64_map.charAt(ch1)+b64_map.charAt(ch2)+b64_map.charAt(ch3)+b64_map.charAt(ch4);
	}

	return result.join('');
}

// ------------------------------------------------------------------------------------------------	

//Decodes Base64 formated data
function base64Decode(data){
	data = data.replace(/[^a-z0-9\+\/=]/ig, '');// strip none base64 characters
	if (typeof(atob) == 'function') return atob(data);//use internal base64 functions if available (gecko only)
	var b64_map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var byte1, byte2, byte3;
	var ch1, ch2, ch3, ch4;
	var result = new Array(); //array is used instead of string because in most of browsers working with large arrays is faster than working with large strings
	var j=0;
	while ((data.length%4) != 0) {
		data += '=';
	}
	
	for (var i=0; i<data.length; i+=4) {
		ch1 = b64_map.indexOf(data.charAt(i));
		ch2 = b64_map.indexOf(data.charAt(i+1));
		ch3 = b64_map.indexOf(data.charAt(i+2));
		ch4 = b64_map.indexOf(data.charAt(i+3));

		byte1 = (ch1 << 2) | (ch2 >> 4);
		byte2 = ((ch2 & 15) << 4) | (ch3 >> 2);
		byte3 = ((ch3 & 3) << 6) | ch4;

		result[j++] = String.fromCharCode(byte1);
		if (ch3 != 64) result[j++] = String.fromCharCode(byte2);
		if (ch4 != 64) result[j++] = String.fromCharCode(byte3);	
	}

	return result.join('');
}


