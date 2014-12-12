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
		
 		jQuery( "#zawiw-chat-area" ).append( data );
 		if(jQuery("#zawiw-chat-area").scrollTop() == 0 || jQuery("#zawiw-chat-area").scrollTop() == tmp)
 			jQuery("#zawiw-chat-area").scrollTop(jQuery("#zawiw-chat-area")[0].scrollHeight);
		window.setTimeout("appendChatItem(true)", 5000);
	});

}

function postMessage()
{
	jQuery.post('../wp-content/plugins/zawiw-chat/ajaxwrite.php', jQuery('#form').serialize(), function( data) {
	
	 	jQuery('#msg').val('');
		appendChatItem(false);
	});
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
 		jQuery( "#zawiw-chat-area" ).append( data );

 		if(data.search("class=\"zawiw-chat-message not_own\"") != -1)
 			notification();
 		if(jQuery("#zawiw-chat-area").prop('scrollTop') == tmp)
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
	startTimer();
	jQuery('#msg').bind("keypress", function() {
		replaceEmojis();
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

        })
});

function notification()
{
	jQuery("zawiw-notification-placeholder").toggle();
	jQuery("#zawiw-notification-placeholder").fadeIn(1);
	jQuery("#zawiw-chat-notification").html("<b>New message alert</b>");
	jQuery("#zawiw-notification-placeholder").fadeOut(5000);
}

function replaceEmojis()
{
	var str = jQuery('#msg').val();
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