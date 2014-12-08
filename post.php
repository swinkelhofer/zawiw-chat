<?php
add_action('template_redirect', 'zawiw_chat_post');
function escape($str)
{
	$str = str_replace("'", "&#39;", $str);
	$str = str_replace("\"", "&#34;", $str);
	$str = str_replace("`", "&#180;", $str);
	return $str;
}
function unset_post_escape()
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while (list($key, $val) = each($process)) {
	    foreach ($val as $k => $v) {
	        unset($process[$key][$k]);
	        if (is_array($v)) {
	            $process[$key][stripslashes($k)] = $v;
	            $process[] = &$process[$key][stripslashes($k)];
	        } else {
	            $process[$key][stripslashes($k)] = stripslashes($v);
	        }
	    }
	}
	unset($process);
}
function zawiw_chat_post()
{
	unset_post_escape();
	mb_internal_encoding("UTF-8");
	if(isset($_POST['submit']) && strlen($_POST['msg']) && check_admin_referer('zawiw_chat'))
	{
		write_db();
	}
	else if(isset($_POST['download']) && check_admin_referer('zawiw_chat') && strlen($_POST['from']) && strlen($_POST['to']))
	{
		require("download.php");
	}
	zawiw_chat_backup_db();
	deleteOldPdfs();
}

function write_db()
{
	global $wpdb;
	$timezone = new DateTimeZone('Europe/Berlin');
	$dbdata['createDT'] = date_format(date_create("now", $timezone),'Y-m-d H:i:s');
	$dbdata['userId'] = get_current_user_id();
	$dbdata['message'] = isset( $_POST['msg'] ) ? sanitize_text_field(utf8_encode(escape($_POST['msg']))) : '';
	$wpdb->insert( $wpdb->get_blog_prefix().'zawiw_chat_data', $dbdata);
}

function zawiw_chat_backup_db()
{
	global $wpdb;
	$zawiw_chat_query = 'SELECT * FROM ';
	$zawiw_chat_query .= $wpdb->get_blog_prefix() . 'zawiw_chat_data ';
	$zawiw_chat_query .= 'WHERE createDT < (NOW() - INTERVAL 7 DAY) ORDER BY createDT ASC';
	$zawiw_chat_backup = $wpdb->get_results( $zawiw_chat_query, ARRAY_A );

	foreach ($zawiw_chat_backup as $zawiw_chat_backup_item) {
		$wpdb->insert( $wpdb->get_blog_prefix().'zawiw_chat_backup', $zawiw_chat_backup_item );
		$wpdb->delete( $wpdb->get_blog_prefix().'zawiw_chat_data', $zawiw_chat_backup_item);
	}
}

function deleteOldPdfs()
{
	$folderName = dirname( __FILE__ ) . "/pdfs";
	if (file_exists($folderName)) 
	{
	    foreach (new DirectoryIterator($folderName) as $fileInfo) 
	    {
	        if ($fileInfo->isDot()) 
	        {
	        	continue;
	        }
	        if (time() - $fileInfo->getCTime() >= 5*60)  //5 Minutes
	        {
	            unlink($fileInfo->getRealPath());
	        }
	    }
	}
}


?>
