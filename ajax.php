<?php
	require_once("../../../wp-load.php");
	if(!is_user_logged_in())
	{
		echo "<div id='zawiw-chat-message'>Sie müssen angemeldet sein, um diese Funktion zu nutzen</div>";
		return;
	}
	$zawiw_chat_query = 'SELECT * FROM ';
	$zawiw_chat_query .= $wpdb->get_blog_prefix() . 'zawiw_chat_data ORDER BY createDT ASC';
	$zawiw_chat_item = $wpdb->get_results( $zawiw_chat_query, ARRAY_A );
	foreach ($zawiw_chat_item as $chat_item)
	{
		$userdata = get_user_by( 'id', $chat_item['userId'] );//get_userdata($chat_item['userId']);
		echo "<div class=\"zawiw-chat-datetime\">" . date_format( date_create($chat_item['createDT']), 'm.d.Y H:i');
		echo "</div><div class=\"zawiw-chat-user\">";
		if(strlen($userdata->user_firstname) AND strlen($userdata->user_lastname))
			echo ($userdata->user_firstname . " ". $userdata->user_lastname);
		else
			echo $userdata->user_nicename;
		echo "</div><div class=\"zawiw-chat-message\">" . $chat_item['message'] . "</div><br />";
	}
?>