<?php
	require_once("../../../wp-load.php");
	header('Content-Type: text/html; charset=utf-8');
	mb_internal_encoding("UTF-8");
	if(!is_user_logged_in())
	{
		echo "<div id='zawiw-chat-message'>Sie müssen angemeldet sein, um diese Funktion zu nutzen</div>";
		return;
	}
	$zawiw_chat_query = 'SELECT * FROM ';
	$zawiw_chat_query .= $wpdb->get_blog_prefix() . 'zawiw_chat_data ';
	$zawiw_chat_query .= 'WHERE createDT > \'' . $_POST['lastpost'] . '\' ORDER BY createDT ASC';
	$zawiw_chat_item = $wpdb->get_results( $zawiw_chat_query, ARRAY_A );
	foreach ($zawiw_chat_item as $chat_item)
	{
		$userdata = get_user_by( 'id', $chat_item['userId'] );
		if($userdata->user_login != "anonymous")
		{
			echo "<div class=\"msg_container\"><div><a href=\"". bp_core_get_user_domain($userdata->ID) . "\" class=\"zawiw-chat-avatar-user\">" . bp_core_fetch_avatar(array( 'item_id' => $userdata->ID, 'type' => 'full', 'width' => '32px')) . "<span class=\"zawiw-chat-user\">" .  $userdata->display_name . "</span></a></div>";
			echo "<div class=\"zawiw-chat-datetime\"><span>" . date_format( date_create($chat_item['createDT']), 'd.m.Y H:i'). "</span></div>";
			
			echo "<div class=\"zawiw-chat-message ". (($userdata->ID == get_current_user_id())?"":"not_own") . "\"><span>" . utf8_decode($chat_item['message']) . "<span></div></div>";
		}
		else
		{
			$pseudonym = preg_filter('/<pseudonym>(.*?)<\/pseudonym>.*$/', "$1", utf8_decode($chat_item['message']));
			echo "<div class=\"msg_container\"><div><a href=\"". bp_core_get_user_domain($userdata->ID) . "\" style=\"pointer-events: none;\" class=\"zawiw-chat-avatar-user\">" . bp_core_fetch_avatar(array( 'item_id' => $userdata->ID, 'type' => 'full', 'width' => '32px')) . "<span class=\"zawiw-chat-user\">" .  $pseudonym . "</span></a></div>";
			echo "<div class=\"zawiw-chat-datetime\"><span>" . date_format( date_create($chat_item['createDT']), 'd.m.Y H:i'). "</span></div>";
			
			echo "<div class=\"zawiw-chat-message\"><span>" . utf8_decode(preg_replace('/<pseudonym>.*?<\/pseudonym>/', "", $chat_item['message'])) . "</span></div></div>";
		}
	}
	if (sizeof($zawiw_chat_item) == 0)
		echo "<input type=\"hidden\" name=\"timestamp\" value=\"" . $_POST['lastpost'] . "\">";
	else
	{
		$last_date_item = $zawiw_chat_item[sizeof($zawiw_chat_item) - 1];
		$time = $last_date_item['createDT'];
		echo "<input type=\"hidden\" name=\"timestamp\" value=\"" . $time . "\" />";	
	}

?>

