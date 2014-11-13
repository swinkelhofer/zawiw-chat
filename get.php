<?php

add_shortcode('zawiw_chat', 'zawiw_chat_shortcode');
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_stylesheet' );
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_script' );

function zawiw_chat_shortcode()
{
	global $wpdb;
	if(!is_user_logged_in())
	{
		echo "<div id='zawiw-chat-message'>Sie müssen angemeldet sein, um diese Funktion zu nutzen</div>";
		return;
	}
?>

 <!-- html div bereich-->
<div id ="zawiw-chat-area" style="height:400px; overflow-y: scroll; overflow-x: hidden;" >

</div>

<div id="zawiw_chat">
	<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'zawiw_chat' ); ?>
		<input type="text" name="msg" id="msg">
		<input type="submit" name="submit" value="Senden">
	</form>
</div>

<?php

}

function zawiw_chat_queue_stylesheet() {
    //wp_enqueue_style( 'zawiw_poll_style', plugins_url( 'style.css', __FILE__ ) );
    //wp_enqueue_style( 'font_awesome4.2', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
    //wp_enqueue_style( 'datetimepickercss', plugins_url( 'datetimepicker/jquery.datetimepicker.css', __FILE__ ) );
}

function zawiw_chat_queue_script() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'zawiw_chat_script', plugins_url( 'helper.js', __FILE__ ) );
    //wp_enqueue_script( 'datetimepickerjs', plugins_url( 'datetimepicker/jquery.datetimepicker.js', __FILE__ ) );

}


?>