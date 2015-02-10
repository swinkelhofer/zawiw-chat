var socket;


function init()
{
	// SET THIS TO YOUR SERVER
	var host = "ws://mirror.forschendes-lernen.de:9999/";
	try
	{
		socket = new WebSocket(host);
		socket.onmessage = function(msg)
		{
			jQuery("#zawiw-chat-area").append(decodeURIComponent(msg.data));

		};
		socket.onclose   = function(msg)
		{
			log("Disconnected - status " + this.readyState + " - Try to reconnect");

			reconnect();
		};
		socket.onopen = function()
		{
			jQuery('#zawiw-chat-area').empty();
			var cookieString = jQuery("#cookies").val();
			var prefix = jQuery("#prefix").val();
			socket.send('<prefix>' + prefix + '</prefix>' + cookieString);
		};
	}
	catch(ex)
	{
		log(ex);
	}
	
}

function sendWsMessage()
{
	var txt,msg;
	txt = jQuery("#msg");
	msg = encodeURIComponent(txt.val());
	if(!msg)
	{
		alert("Message can not be empty");
		return;
	}
	txt.val("");
	txt.focus();
	try 
	{
		var userId = jQuery("#userId").val();
		var prefix = jQuery("#prefix").val();
		socket.send('<userId>' + userId + '</userId><prefix>' + prefix + '</prefix>' + msg);
	}
	catch(ex)
	{
		log(ex);
	}
}

function reconnect()
{
	init();
}

function log(msg)
{
	var user = getCookieData();
	if(user != "")
	{
		//$("zawiw-chat-area").innerHTML+="<br>" + user;
		//alert("Welcome again " + user);
	}
	jQuery("#zawiw-chat-area").append("<p>" + msg + "</p>");
}

function getCookieData()
{
	var theCookies = document.cookie.split(';');
	var aString = '';
	    for (var i = 1 ; i <= theCookies.length; i++) {
	        aString += i + ' ' + theCookies[i-1] + "\n";
	    }
	    return aString;
}

//function Onkey Enter to send not implemented at this time