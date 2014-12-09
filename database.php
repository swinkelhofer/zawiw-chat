<?php

register_activation_hook( dirname( __FILE__ ).'/zawiw-chat.php', 'zawiw_chat_activation');

function zawiw_chat_activation()
{
	global $wpdb;
	if(is_multisite())
	{
		if(!empty($_GET['networkwide']))
		{
			$start_blog = $wpdb->blogid;
			$blog_list = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
			foreach($blog_list as $blog)
			{
				switch_to_blog($blog);
				zawiw_chat_create_db($wpdb->get_blog_prefix());
				zawiw_chat_create_backup_db($wpdb->get_blog_prefix());
			}
			switch_to_blog($start_blog);
			return;
		}
	}
	zawiw_chat_create_db($wpdb->get_blog_prefix());
	zawiw_chat_create_backup_db($wpdb->get_blog_prefix());
}

function zawiw_chat_create_db($prefix)
{
	$creation_query = 'CREATE TABLE ' . $prefix . "zawiw_chat_data (
      id int(20) NOT NULL AUTO_INCREMENT,
      createDT datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      userId int(20) NOT NULL,
      message TEXT NOT NULL,
      UNIQUE KEY id (id)
      ) DEFAULT CHARACTER SET=utf8;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $creation_query );
}

function zawiw_chat_create_backup_db($prefix)
{	
	$creation_query = 'CREATE TABLE ' . $prefix . "zawiw_chat_backup (
      id int(20) NOT NULL AUTO_INCREMENT,
      createDT datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      userId int(20) NOT NULL,
      message TEXT NOT NULL,
      UNIQUE KEY id (id)
      ) DEFAULT CHARACTER SET=utf8;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $creation_query );
}


?>