<?php
add_shortcode('zawiw_chat', 'zawiw_chat_shortcode');
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_script' );
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_stylesheet' );
header('Content-Type: charset=utf-8');
function zawiw_chat_shortcode()
{
	if(!is_user_logged_in())
	{
		echo "<div id='zawiw-chat-message'>Sie müssen angemeldet sein, um diese Funktion zu nutzen</div>";
		return;
	}
?>
 <!-- html div bereich-->
<div id="zawiw-chat-view">
	<div id="zawiw-notification-placeholder">
		<div id="zawiw-chat-notification">
		</div>
	</div>
	<div id="zawiw-user-filter">
		<input class="" type="text" name="user-filter" id="user-filter" placeholder="Search for user messages" />
	</div>
<div id ="zawiw-chat-area">

</div>
</div>

<div id="zawiw_chat">
	<form action="" id="form" method="post" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8" autocomplete="off">
		<?php wp_nonce_field( 'zawiw_chat' ); ?>
		<div class="placeholder">
		<div class="chat_input">
			<div id="emoji_button" onClick="javascript: emojiList()" onselectstart="return false">😈</div>
			<input class="" type="text" name="msg" id="msg" placeholder="Type your message" />
			<div class="submit_button one-third">
				<input type="hidden" name="submit" value="Senden" />
				<input onClick="javascript: postMessage()" type="button" id="send" value="Send" />
			</div>
		</div>
		</div>
		<div id="zawiw_chat_download">
			<a href="javascript: expand('#zawiw_chat_download_expandable')" class="fa fa-chevron-down">Create chat history</a>
			<div id="zawiw_chat_download_expandable">
				<div id="wait"><img src='../wp-content/plugins/zawiw-chat/animatedEllipse.gif' /></div>
				<div id="pdfcontainer"></div>
				<label for="from">From</label>
				<input type="text" id="from" name="from" />
				<label for="to">To</label>
				<input type="text" id="to" name="to" />
				<input type="hidden" name="download" value="Senden" />
				<input type="button" name="download" id="download" onClick="javascript: getPDF()" value="Create history file" />
			</div>
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
    wp_enqueue_style( 'font_awesome4.2', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
    wp_enqueue_style( 'lato_font', 'http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' );
	wp_enqueue_style( 'datetimepickercss', plugins_url( 'datetimepicker/jquery.datetimepicker.css', __FILE__ ) );

}
function zawiw_chat_queue_script()
{
	global $post;
	if(!has_shortcode($post->post_content, 'zawiw_chat'))	//Loads stylesheets only if shortcode exists
		return;
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'zawiw_chat_script', plugins_url( 'helper.js', __FILE__ ) );
	wp_enqueue_script( 'datetimepickerjs', plugins_url( 'datetimepicker/jquery.datetimepicker.js', __FILE__ ) );
}
?>
