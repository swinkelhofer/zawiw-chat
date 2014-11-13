<?php
add_action('template_redirect', 'zawiw_chat_post');

function zawiw_chat_post()
{
	global $wpdb;
	// the strlen($_POST['msg']) checks if textinput isn't empty (Georg)
	if(isset($_POST['submit']) && strlen($_POST['msg']) && check_admin_referer('zawiw_chat'))
	{
		$timezone = new DateTimeZone('Europe/Berlin');
		$dbdata['createDT'] = date_format(date_create("now", $timezone),'Y-m-d H:i:s');
		$dbdata['userId'] = get_current_user_id();
		$dbdata['message'] = isset( $_POST['msg'] ) ? sanitize_text_field( $_POST['msg'] ) : '';
		$wpdb->insert( $wpdb->get_blog_prefix().'zawiw_chat_data', $dbdata);
	}
}

?>