<?php

add_shortcode('zawiw_chat', 'zawiw_chat_shortcode');
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_script' );
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_stylesheet' );


function zawiw_chat_shortcode()
{
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
	<form action="" method="post" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8">
		<?php wp_nonce_field( 'zawiw_chat' ); ?>
		<div class="chat_input">
			<input class="" type="text" name="msg" id="msg" placeholder="Type your message" />
		</div>
		<div class="submit_button">
			<input class="" type="submit" name="submit" value="Senden" />
		</div>
	</form>
</div>

<?php

}

function zawiw_chat_queue_stylesheet()
{
	global $post;	//Contains the whole site content
	if(!has_shortcode($post->post_content, 'zawiw_chat'))	//Loads stylesheets only if shortcode exists
		return;
    wp_enqueue_style( 'zawiw_chat_style', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style( 'lato_font', 'http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' );
}

function zawiw_chat_queue_script()
{
	global $post;
	if(!has_shortcode($post->post_content, 'zawiw_chat'))	//Loads stylesheets only if shortcode exists
		return;
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'zawiw_chat_script', plugins_url( 'helper.js', __FILE__ ) );
}

?>