function insert()
{
	var tmp = jQuery("#zawiw-chat-area")[0].scrollHeight;
	jQuery.get( "../wp-content/plugins/zawiw-chat/ajax.php", function( data ) {
		
 	jQuery( "#zawiw-chat-area" ).html( data );
 	if(jQuery("#zawiw-chat-area").scrollTop() == 0 || jQuery("#zawiw-chat-area").scrollTop() == tmp)
 		jQuery("#zawiw-chat-area").scrollTop(jQuery("#zawiw-chat-area")[0].scrollHeight);
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
	window.setInterval("insert()", 5000);
}
jQuery(document).ready(function(){
	startTimer();
	//jQuery('#msg').css("display", "none");
	jQuery('#msg').bind("keyup", function() {
		replaceEmojis();
	});
        jQuery('#from').datetimepicker({
            lang:'de',
            format: 'd.m.Y H:i',
            timepicker:true,
            step: 60,
            defaultDate: false,
            defaultTime: false,
            allowBlank: true

        });
        jQuery('#to').datetimepicker({
            lang:'de',
            format: 'd.m.Y H:i',
            timepicker:true,
            step: 60,
            defaultDate: false,
            defaultTime: false,
            allowBlank: true

        })
});

function replaceEmojis()
{
	var str = jQuery('#msg').val();
	str = str.replace("O:}", "\uD83D\uDE07");
	str = str.replace("=:}", "\uD83D\uDE08");
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