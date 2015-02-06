function emojiSupported() {
	//TODO: replace EMoji-Range with PNGs
	var node = document.createElement('canvas');
	if(node == null)
		return false;
	if (!node.getContext || !node.getContext('2d') || typeof node.getContext('2d').fillText !== 'function')
	{
		return false;
	}
	var ctx = node.getContext('2d');
	ctx.rect(0,0,32,32);
	ctx.fillStyle="white";
	ctx.fill();
	ctx.textBaseline = 'top';
	ctx.fillStyle="black";
	ctx.font = '32px Arial';
	ctx.fillText('\ud83d\ude03', 0, 0);
	return ctx.getImageData(12,12, 1, 1).data[0] === 0;
}

//christmas settings (emojis with santa hat) not used at the moment
function christmas(data)
{
	data = data.replace(/(\uD83D.)/gi, '</span><span class="inlineEmoji">$1</span><span>');
	return data;
}
function encode_utf8(s)
{
	s = encodeURIComponent(s);
	var splitted = s.split('%');
	return "0x" + (((parseInt(splitted[1],16) & 0xf) << 18) | ((parseInt(splitted[2],16) & 0x3f) << 12) | ((parseInt(splitted[3],16) & 0x3f) << 6) | (parseInt(splitted[4],16) & 0x3f)).toString(16);
}
function replaceData(data)
{
	//data = christmas(data); // Christmas settings ;)
	if(!emojiSupported())	// Fallback for older browsers
		data = data.replace(/(\uD83D.)/gi, function(e) { return '</span><img class="emojiPNG" src="../wp-content/plugins/zawiw-chat/emojis/'+ encode_utf8(e) +'.png" /><span>'; });
	data = data.replace(/([^"'])(https?:\/\/[^< ]+)/g, '$1</span><a href="$2"><span>$2</span></a></span>');
	return data;
}


function embedMedia()
{
	var a = jQuery('.zawiw-chat-message a');
	a.each(function(index,elem) {
		var prop = jQuery(elem).prop('href');
		if(prop.search(/https?:\/\/www\.youtube.com/) != -1 && !jQuery(elem).parents('.msg_container').find('iframe').length)
		{
			prop = prop.replace(/https?.*\?v=(.*)/, '$1');
			jQuery('<iframe width="350" height="250" class="embed" src="//www.youtube.com/embed/' + prop + '" frameborder="0" allowfullscreen></iframe>').insertAfter(jQuery(elem).parents('.zawiw-chat-message'));
		}
		else if(prop.search(/https?:\/\/.+\.(jpg|bmp|gif|png)/i) != -1 && !jQuery(elem).parents('.msg_container').find('img.embed').length)
		{
			jQuery('<img src="' + prop + '" height=250 class="embed" />').insertAfter(jQuery(elem).parents('.zawiw-chat-message'));
		}
	});
}

function insert()
{
	var date = new Date((new Date()).valueOf() - 604800000);
	var month = parseInt(date.getMonth())+1;
	if(parseInt(month) < 10)
		month = "0" + month;
	var day = date.getDate();
	if(parseInt(day) < 10)
		day = "0" + day;
	var hours = date.getHours();
	if(parseInt(hours) < 10)
		hours = "0" + hours;
	var minutes = date.getMinutes();
	if(parseInt(minutes) < 10)
		minutes = "0" + minutes;
	var seconds = date.getSeconds();
	if(parseInt(seconds) < 10)
		seconds = "0" + seconds;
	var datestring = date.getFullYear() + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds;
	var tmp = jQuery("#zawiw-chat-area")[0].scrollHeight;
	jQuery.post( "../wp-content/plugins/zawiw-chat/ajax.php", { lastpost: datestring }, function( data ) {
		data = replaceData(decodeURIComponent(data));
 		jQuery( "#zawiw-chat-area" ).append( data );
 		embedMedia();
 		if(jQuery("#zawiw-chat-area").scrollTop() == 0 || jQuery("#zawiw-chat-area").scrollTop() == tmp)
 			jQuery("#zawiw-chat-area").scrollTop(jQuery("#zawiw-chat-area")[0].scrollHeight);
		window.setTimeout("appendChatItem(true)", 5000);
	});

}

function postMessage()
{
	//TODO redirect to sendWsMessage() if Websocket is supported else fallback
	  if( typeof(WebSocket) != "function" ) {
	    //jQuery("#zawiw-chat-area").html("<h1>Error</h1><p>Your browser does not support HTML5 Web Sockets. Try Google Chrome instead.</p>");
	  	// no websocket supported fallback to ajax
	  }
	  else {
	  	//jQuery("#zawiw-chat-area").html("<p>Gratulations! your browser supports websockets. And you can enjoy fancy features</p>");
	  	// Here use the websocket now
	  	sendWsMessage();
	  	return;
	  }

	var errors = 0;
	jQuery('#msg').removeClass("error");
	jQuery('#pseudonym').removeClass("error");
	if(jQuery('#msg').val() == "")
	{
		jQuery('#msg').addClass("error");
		errors++;
	}
	if(jQuery('#pseudonym').val() == "")
	{
		jQuery('#pseudonym').addClass("error");
		errors++;
	}
	if(errors > 0)
		return;
	jQuery('#msg').val(encodeURIComponent(jQuery('#msg').val()));
	if(jQuery('#pseudonym') != null)
	{
		jQuery.post('../wp-content/plugins/zawiw-chat/ajaxwrite.php', jQuery('#form').serialize() +  '&pseudonym='+ jQuery('#pseudonym').val(), function( data) {
		
		 	jQuery('#msg').val('');
			appendChatItem(false);

		});
	}
	else
	{
		jQuery.post('../wp-content/plugins/zawiw-chat/ajaxwrite.php',  jQuery('#form').serialize(), function( data) {
		
		 	jQuery('#msg').val('');
			appendChatItem(false);

		});
	}
}

function copyEmoji(elem, text)
{
	jQuery(elem).val(jQuery(elem).val()+text);
	jQuery('#emojiList').addClass('invisible');
	jQuery(elem).focus();
}

function emojiList()
{
	if(!jQuery('#emojiList').length)
	{
		jQuery('#zawiw-chat-view').append("<div id='emojiList' class='invisible' onselectstart=\"return false;\"><div class='content' onselectstart=\"return false;\"></div></div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"O:}\")'>\uD83D\uDE07</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \">:}\")'>\uD83D\uDE08</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-)\")'>\uD83D\uDE0A</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-D\")'>\uD83D\uDE03</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":&#39-D\")'>\uD83D\uDE02</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"xD\")'>\uD83D\uDE06</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"B-)\")'>\uD83D\uDE0E</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \";-)\")'>\uD83D\uDE09</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-P\")'>\uD83D\uDE0B</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"xP\")'>\uD83D\uDE1D</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-O\")'>\uD83D\uDE31</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"o.O\")'>\uD83D\uDE32</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"|-*\")'>\uD83D\uDE1A</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \";-P\")'>\uD83D\uDE1C</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-*\")'>\uD83D\uDE18</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-|\")'>\uD83D\uDE10</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"x(\")'>\uD83D\uDE23</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":-(\")'>\uD83D\uDE1E</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":&#39&#39&#39&#39-(\")'>\uD83D\uDE2D</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \":&#39-(\")'>\uD83D\uDE22</div>");
		jQuery('#emojiList .content').append("<div class='quarter' onselectstart=\"return false;\" onClick='copyEmoji(\"#msg\", \"*-*\")'>\uD83D\uDE0D</div>");
		jQuery('#zawiw-chat-area, #msg, #send').bind("click", function() {
			if(jQuery('#emojiList').css('height') != '0px')
			{
				jQuery('#emojiList').addClass('invisible');
			}
		});
		jQuery('#emojiList').removeClass('invisible');
	}
	else
	{
		if(jQuery('#emojiList').css('height') != '0px')
		{
			jQuery('#emojiList').addClass('invisible');
		}
		else
		{
			jQuery('#emojiList').removeClass('invisible');
		}
	}
}

function getPDF()
{
	jQuery('#pdfcontainer').empty();
	jQuery('#wait').css('height','40px');
	jQuery('#wait').css('width', '40px');
	jQuery.post('../wp-content/plugins/zawiw-chat/download.php', jQuery('#form').serialize(), function( data) {
		if(data.substr(-4,4) == ".pdf")
		{
		 	jQuery('#pdfcontainer').append("<a href=\""+ data +"\" class=\"fa fa-download\" id=\"zawiw_chat_pdf\">Download chat history</a>");
			jQuery('#from').removeClass('warning');
			jQuery('#to').removeClass('warning');
			jQuery('#wait').css('height','0px');
			jQuery('#wait').css('width','0px');
		}
		else
		{
			jQuery('#pdfcontainer').append("<div class='pdferror'>Input data not valid</div>");
			jQuery('#from').addClass('warning');
			jQuery('#to').addClass('warning');
			jQuery('#wait').css('height','0px');
			jQuery('#wait').css('width','0px');
		}
	});

}

function appendChatItem(selfUpdate)
{
	var tmp = jQuery('#zawiw-chat-area').prop('scrollHeight') - parseInt((jQuery('#zawiw-chat-area').css('height')).replace("px", ""));
	var timestamp = jQuery('#zawiw-chat-area').children('input').val();
	if(timestamp == null)
		return;

	jQuery('#zawiw-chat-area').children('input').remove();
	jQuery.post( "../wp-content/plugins/zawiw-chat/ajax.php", { lastpost: timestamp }, function( data ) {
 		if(selfUpdate)
			window.setTimeout("appendChatItem(true)", 5000);
		data = replaceData(decodeURIComponent(data));
 		jQuery( "#zawiw-chat-area" ).append( data );
 		embedMedia()
 		if(data.search("class=\"zawiw-chat-message not_own\"") != -1)
 			notification();
 		if(jQuery("#zawiw-chat-area").prop('scrollTop') <= tmp+10 && jQuery("#zawiw-chat-area").prop('scrollTop') >= tmp-10)
 			jQuery("#zawiw-chat-area").prop('scrollTop', jQuery("#zawiw-chat-area").prop('scrollHeight'));

	});

}

function expand(elem)
{
	elem = jQuery(elem);
	if(elem.css('max-height') == '0px')
	{
		elem.css('max-height', '300px');
		elem.parent().children('a.fa').removeClass('fa-chevron-down').addClass('fa-chevron-up');
	}
	else
	{
		elem.css('max-height', '0px');
		elem.parent().children('a.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');		
	}
}

function startTimer()
{
	insert();
}

jQuery(document).ready(function(){
	//TODO redirect to sendWsMessage() if Websocket is supported else fallback
	  if( typeof(WebSocket) == "function" ) {
	    //jQuery("#zawiw-chat-area").html("<h1>Error</h1><p>Your browser does not support HTML5 Web Sockets. Try Google Chrome instead.</p>");
	  	// no websocket supported fallback to ajax
	  	init();
	  }
	  else
	  {
		startTimer();
	  }
	jQuery('#msg').bind("keypress", function() {
		replaceEmojis();
	});
	jQuery('#search-filter').bind("keyup", function(){
		searchtext();
	});
    jQuery('#from').datetimepicker({
        lang:'de',
        format: 'd.m.Y H:i',
        timepicker:true,
        step: 60,
        defaultDate: false,
        defaultTime: false,
        allowBlank: true,
        closeOnDateSelect:true
    });
    jQuery('#to').datetimepicker({
        lang:'de',
        format: 'd.m.Y H:i',
        timepicker:true,
        step: 60,
        defaultDate: false,
        defaultTime: false,
        allowBlank: true,
        closeOnDateSelect:true
	});
	if(!emojiSupported())	//Fallback for older browsers
	{
		jQuery('#emoji_button').html("<img src='../wp-content/plugins/zawiw-chat/emojis/0x1f608.png' />");
	}
});

function notification()
{
	jQuery("zawiw-notification-placeholder").toggle();
	jQuery("#zawiw-notification-placeholder").fadeIn(1);
	jQuery("#zawiw-chat-notification").html("<b>New message alert</b>");
	jQuery("#zawiw-notification-placeholder").fadeOut(5000);
}

function searchtext()
{
	var searchValueTmp = jQuery('#search-filter').val();
	var searchValue = jQuery('#search-filter').val().toLowerCase();
	//alert(jQuery('.msg_container').contents().filter(function() { return this.nodeType == 3; }));
	jQuery('.msg_container').each(function(index, elem){
		if(searchValue == "") {
			jQuery(elem).css('display', 'block');

			jQuery(elem).find('span').each(function(subIndex, subElem){
				jQuery(subElem).html(jQuery(subElem).text());
			});
		}
		else if(jQuery(elem).text().toLowerCase().indexOf(searchValue) > -1) {
			jQuery(elem).css('display', 'block');
			jQuery(elem).find('span').each(function(subIndex, subElem){
				jQuery(subElem).html(jQuery(subElem).text().replace(new RegExp("("+searchValue+")", "gi"), '<b>$1</b>'));
			});
		}
		else
		{
			jQuery(elem).css('display', 'none');
		}
		jQuery("#zawiw-chat-area").scrollTop(jQuery("#zawiw-chat-area")[0].scrollHeight);
	});
}

function replaceEmojis()
{
	var str = jQuery('#msg').val();
	if(str.search(/(:|;|xD|o\.O|x\(|\*-\*|\^-\^|\||B-\))/) == -1)
		return;
	str = str.replace("O:}", "\uD83D\uDE07");
	str = str.replace(">:}", "\uD83D\uDE08");
	str = str.replace(":-)", "\uD83D\uDE0A").replace(":)", "\uD83D\uDE0A");
	str = str.replace(":-D", "\uD83D\uDE03").replace(":D", "\uD83D\uDE03");
	str = str.replace(":'-D", "\uD83D\uDE02").replace(":'D", "\uD83D\uDE02");
	str = str.replace("xD", "\uD83D\uDE06");
	str = str.replace("B-)", "\uD83D\uDE0E");
	str = str.replace(";-)", "\uD83D\uDE09").replace(";)", "\uD83D\uDE09");
	str = str.replace(":-P", "\uD83D\uDE0B").replace(":P", "\uD83D\uDE0B");
	str = str.replace("xP", "\uD83D\uDE1D");
	str = str.replace(":-O", "\uD83D\uDE31").replace(":O", "\uD83D\uDE31");
	str = str.replace("o.O", "\uD83D\uDE32");
	str = str.replace("|-*", "\uD83D\uDE1A");
	str = str.replace(";-P", "\uD83D\uDE1C").replace(";P", "\uD83D\uDE1C");
	str = str.replace(":-*", "\uD83D\uDE18").replace(":*", "\uD83D\uDE18");
	str = str.replace(":-|", "\uD83D\uDE10").replace(":|", "\uD83D\uDE10");
	str = str.replace("x(", "\uD83D\uDE23");
	str = str.replace(":-(", "\uD83D\uDE1E").replace(":(", "\uD83D\uDE1E");
	str = str.replace(":''''-(", "\uD83D\uDE2D").replace(":''(", "\uD83D\uDE2D");
	str = str.replace(":'-(", "\uD83D\uDE22").replace(":'(", "\uD83D\uDE22");
	str = str.replace("*-*", "\uD83D\uDE0D").replace("^-^", "\uD83D\uDE04");
	jQuery('#msg').val(str);
}
