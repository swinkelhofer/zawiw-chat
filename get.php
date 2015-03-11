<?php
add_action('after_setup_theme', 'anonymous_login');
add_shortcode('zawiw_chat', 'zawiw_chat_shortcode');
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_script' );
add_action( 'wp_enqueue_scripts', 'zawiw_chat_queue_stylesheet' );

header('Content-Type: text/html; charset=utf-8');

/*
 * gets cookies for hidden fields
*/
function processCookies()
{
	$str = "";
	foreach ($_COOKIE as $cookie => $value) 
	{
		$str .= $cookie . ":" . $value . ";";  	
	}
	return $str;
}

/*
 * anonymous user will be logged in if parameter is set
*/
function anonymous_login()
{
	global $wpdb;
	$get_post = "";
	$result = $wpdb->get_results("SELECT * FROM ".$wpdb->posts." WHERE post_name='".str_replace("/", "",$_SERVER['REQUEST_URI'])."'");

	if(!empty($result))
	{
		$get_post = $result[0]->post_content;
	}
	if(preg_match('/^.*\[zawiw_chat.*?anonymous=[’"]?true.*?\].*$/', (string)$get_post) == 1)
	{
		$creds = array();
		$creds['user_login'] = 'anonymous';
		$creds['user_password'] = 'anonymous$123';
		$creds['remember'] = true;
		$user = wp_signon($creds, false);
		wp_set_current_user($user->ID);
		$GLOBALS['is_anonymous'] = true;
	}
}


function zawiw_chat_shortcode($param)
{
	if(!is_user_logged_in())
	{
		echo "<div id='zawiw-chat-message'>Sie müssen angemeldet sein, um diese Funktion zu nutzen!</div>";
		return;
	}
?>


<input type="hidden" id="prefix" value="<?php global $wpdb; echo $wpdb->get_blog_prefix();?>" />
<input type="hidden" id="cookies" value="<?php echo processCookies();?>" />
<input type="hidden" id="userId" value="<?php echo get_current_user_id();?>" />
<div id="zawiw-chat-view">
	<div id="zawiw-notification-placeholder">
		<div id="zawiw-chat-notification">
		</div>
	</div>
	<div id="zawiw-search-filter">
					<?php
 if(isset($GLOBALS['is_anonymous'])){
 		?>
		<div id="anonymous_user"> 
			<input id="pseudonym" type="text" name="pseudonym" placeholder="Type your name"/>
		</div>
		<?php
	}
	else
	{
	?>
		<input class="" type="text" name="search-filter" id="search-filter" placeholder="Search ..." />
	<?php } ?>
	</div>
	<div id="blur"></div>
	<div id ="zawiw-chat-area">
	</div>
</div>

<div id="zawiw_chat">
	<form action="" id="form" method="post" enctype="application/x-www-form-urlencoded" accept-charset="UTF-8" autocomplete="off">
		<?php wp_nonce_field( 'zawiw_chat' ); ?>
		<div class="placeholder">

		<div class="chat_input">
			<div id="emoji_button" onClick="javascript: emojiList()" onselectstart="return false">😈</div>
			<input class="" type="text" name="msg" id="msg" onKeyPress="if(event.keyCode==13) postMessage();" placeholder="Type your message" />
			<div class="submit_button one-third">
				<input type="hidden" name="submit" value="Senden" />
				<input onClick="javascript: postMessage()" type="button" id="send" value="Send" />
			</div>
		</div>
		</div>

<?php
	if(!isset($GLOBALS['is_anonymous'])){
?>

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
		<?php } ?>
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
    global $current_user;
    if($current_user->user_login == "anonymous")
	wp_enqueue_style( 'zawiw_chat_anonymous_style', plugins_url( 'anonymous.css', __FILE__ ) );
    wp_enqueue_style( 'font_awesome4.2', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
    wp_enqueue_style( 'lato_font', 'https://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' );
	wp_enqueue_style( 'datetimepickercss', plugins_url( 'datetimepicker/jquery.datetimepicker.css', __FILE__ ) );

}
function zawiw_chat_queue_script()
{
	global $post;
	if(!has_shortcode($post->post_content, 'zawiw_chat'))	//Loads stylesheets only if shortcode exists
		return;
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'zawiw_chat_script', plugins_url( 'helper.js', __FILE__ ) );
    wp_enqueue_script( 'websocket_script', plugins_url( 'websocket.js',__FILE__) );
	wp_enqueue_script( 'datetimepickerjs', plugins_url( 'datetimepicker/jquery.datetimepicker.js', __FILE__ ) );
}
?>
