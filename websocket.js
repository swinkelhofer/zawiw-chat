var socket;
var checkConnection = 0;

function init()
{

	if (checkConnection < 10 ){
		// SET THIS TO YOUR SERVER
		var host = "ws://88.80.205.25:9999/";
		try
		{	
			socket = new WebSocket(host);
			socket.onmessage = function(msg)
			{
				data = replaceData(decodeURIComponent(msg.data));
				jQuery("#zawiw-chat-area").append(data);
				embedMedia();
				var tmp = jQuery("#zawiw-chat-area")[0].scrollHeight;
				if(jQuery("#zawiw-chat-area").scrollTop() == 0 || jQuery("#zawiw-chat-area").scrollTop() == tmp)
				{
 					jQuery("#zawiw-chat-area").scrollTop(jQuery("#zawiw-chat-area")[0].scrollHeight);
 				}

			};
			socket.onclose   = function(msg)
			{
				log("status " + this.readyState + "- connecting ...")
				//log("Disconnected - status " + this.readyState + " - Try to reconnect");
				checkConnection = checkConnection + 1;
				reconnect();
			};
			socket.onopen = function()
			{
				jQuery('#zawiw-chat-area').empty();
				var cookieString = jQuery("#cookies").val();
				var prefix = jQuery("#prefix").val();
				socket.send('<prefix>' + prefix + '</prefix>' + cookieString);
				checkConnection = 0;
			};
		}
		catch(ex)
		{
			checkConnection = checkConnection + 1;
			log(ex);
		}
	}
	else
	{
		socket.onmessage = function(msg)
		{
			return;
		};
		socket.onclose   = function(msg)
		{
			log("Fall back to ajax" + this.readyState);
			return;
		};
		socket.onopen = function()
		{
			return;
		};
		startTimer();
		return;
	}  	
	
}

/*
 * send message via websocket
*/
function sendWsMessage()
{
	var txt,msg;
	txt = jQuery("#msg");
	msg = encodeURIComponent(txt.val());
	txt.removeClass("error");
	if(!msg)
	{
		//alert("Message can not be empty");
		txt.addClass("error");
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
	jQuery("#zawiw-chat-area").html("<p>" + msg + "</p>");
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
