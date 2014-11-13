function insert()
{
	var tmp = jQuery("#zawiw-chat-area")[0].scrollHeight;
	jQuery.get( "../wp-content/plugins/zawiw-chat/ajax.php", function( data ) {
		
 	jQuery( "#zawiw-chat-area" ).html( data );
 	if(jQuery("#zawiw-chat-area").scrollTop() == 0 || jQuery("#zawiw-chat-area").scrollTop() == tmp)
 		jQuery("#zawiw-chat-area").scrollTop(jQuery("#zawiw-chat-area")[0].scrollHeight);
});
}

function startTimer()
{
	window.setInterval("insert()", 1000);
}
jQuery(document).ready(function(){
	startTimer();
});