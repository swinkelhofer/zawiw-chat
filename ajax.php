<?php

	mb_internal_encoding("UTF-8");
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
		$userdata = get_user_by( 'id', $chat_item['userId'] );
		echo "<div class=\"zawiw-chat-datetime\">" . date_format( date_create($chat_item['createDT']), 'd.m.Y H:i'). "</div>";
		echo "<div><a href=\"". bp_core_get_user_domain($userdata->ID) . "\" class=\"zawiw-chat-avatar-user\">" . bp_core_fetch_avatar(array( 'item_id' => $userdata->ID, 'type' => 'full', 'width' => '32px')) . "<span class=\"zawiw-chat-user\">" .  $userdata->display_name . "</span></a></div>";
		echo "</a></div><div class=\"zawiw-chat-message\">" . utf8_decode($chat_item['message']) . "</div><hr />";
	}
?>

