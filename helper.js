function insert()
{
	jQuery.get( "../wp-content/plugins/zawiw-chat/ajax.php", function( data ) {
 	jQuery( "#zawiw-chat-area" ).text( data );
});
}
function startTimer()
{
	window.setInterval("insert()", 1000);
}
jQuery(document).ready(function(){
	startTimer();
});