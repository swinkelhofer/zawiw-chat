<?php

define('WP_INSTALLING', true);
ini_set('mysqli.reconnect', 1);
#ini_set('mysqli.allow_persistent', 1);
require_once(dirname(__FILE__).'/websockets.php');

// MySQL database connection to wordpress
$db = mysqli_connect("0.0.0.0", "web10", "FD26Ur2k", "usr_web10_1");
// Check connection
if (mysqli_connect_errno())
  {
  #echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

class BroadcastWebSocketServer extends WebSocketServer
{
	protected $users = array();
	protected function process($user, $message)
	{
		echo $message . "\n";
		$prefix = preg_filter('/<prefix>(.*?)<\/prefix>.*$/', "$1", utf8_decode($message));
		$message = utf8_decode(preg_replace('/<prefix>.*?<\/prefix>/', "", $message));

		//ist benutzer in anwärter array? check cookies ....
		if(!$user->authentified)
		{
			if($this->user_authentified($user, $message) === FALSE)
			{
				$this->disconnect($user->socket);
			}
			else
			{
				$user->authentified = true;
				global $db;
				//echo "Users: " . count($this->users) . "\n";
				mysqli_ping($db);
				array_push($this->users, $user);
				$sql = "SELECT * FROM " . $prefix . "zawiw_chat_data ORDER BY createDT ASC";
				#echo $sql . "\n";
				$result = mysqli_query($db, $sql);

				while($row = mysqli_fetch_array($result)) 
				{
					$this->send($user, $this->build_message($row['createDT'], $row['userId'], $row['message']));
				}
			}
		}
		else
		{
			$this->broadcast($message, $prefix);
		}
	}

	/* builds message for view */
	protected function build_message($createDT, $userID, $message)
	{
		$userdata = get_user_by( 'id', $userID);
		$msg=
		"<div class='msg_container'><div><a href=\"" . bp_core_get_user_domain($userdata->ID) . "\" class='zawiw-chat-avatar-user'>". bp_core_fetch_avatar(array( 'item_id' => $userdata->ID, 'type' => 'full', 'width' => '32px')) ."<span class='zawiw-chat-user'>" .  $userdata->display_name . "</span></a></div>" . 
		"<div class='zawiw-chat-datetime'><span>" . date_format( date_create($createDT), 'd.m.Y H:i'). "</span></div>" . 
		"<div class='zawiw-chat-message'><span>" . $message . "</span></div></div>";
		return $msg;
	}

	protected function user_authentified($user, $message)
	{
		$theCookies = array();
		$theCookies = explode(';', $message, -1);
		
		foreach($theCookies as $keks)
		{
			$key_value = array();
			$key_value = explode(':', $keks);
			$_COOKIE[$key_value[0]] = $key_value[1];
		}
		global $wpdb;
		require_once(dirname(__FILE__)."/../../html/wp-load.php");

		/* if user logged in set user as authentified*/
		if(is_user_logged_in())
		{
			$key_value = array();
			$key_value = explode(':', $keks);
			$user->authentified = true;
	        $theCookies = array();
        	$theCookies = explode(';', $message, -1);

            foreach($theCookies as $keks)
	        {
    	        $key_value = array();
           	 	$key_value = explode(':', $keks);
            	unset($_COOKIE[$key_value[0]]);
        		#echo $key_value[0];
			}
			$HTTP_COOKIE_VARS = "";
			global $current_user;
			$current_user=null;
			return true;

		}
		else
		{
			$theCookies = array();
            $theCookies = explode(';', $message, -1);

            foreach($theCookies as $keks)
            {
                $key_value = array();
                $key_value = explode(':', $keks);
                unset($_COOKIE[$key_value[0]]);
                #echo $key_value[0];
            }
            $HTTP_COOKIE_VARS = "";
			global $current_user;
                        $current_user=null;
			return false;
		}
	}

	protected function connected($user)
	{
		
	}
	
	protected function closed($user)
	{
		$num = array_search($user, $this->users);
		#print_r($this->users);
		if($num)
			unset($this->users[$num]);
	}
	
	protected function broadcast($message, $prefix)
	{
		$prefix = preg_replace('/<userId>.*?<\/userId>/', "", $prefix);
		$id = preg_filter('/<userId>(.*?)<\/userId>.*$/', "$1", utf8_decode($message));
		$message = utf8_decode(preg_replace('/<userId>.*?<\/userId>/', "", $message));
		$timezone = new DateTimeZone('Europe/Berlin');
		$timestamp = date_format(date_create("now", $timezone),'Y-m-d H:i:s');

		global $db;
		$sql = "INSERT INTO " . $prefix . "zawiw_chat_data (createDT, userId, message) VALUES ('" . $timestamp . "', '". $id . "', '". $message . "')";
		#echo $sql;
		mysqli_query($db, $sql);
		$msg = $this->build_message($timestamp, $id, $message);
		$almostsent = array();
		foreach($this->users as $user)
		{
			if(array_search($user, $almostsent) === FALSE)
			{
				$this->send($user,$msg);
				array_push($almostsent, $user);
			}
		}
		$this->zawiw_chat_backup_db($prefix);
	}

	/*
 	* moves chatmessages wich are older than 7 days to backupdatabase
	*/
	protected function zawiw_chat_backup_db($prefix)
	{
		global $db;
		$sql = "SELECT * FROM " . $prefix . "zawiw_chat_data WHERE createDT < (NOW() - INTERVAL 7 DAY) ORDER BY createDT ASC";
		echo $sql . "\n";
		$result = mysqli_query($db, $sql);

		$sql = "INSERT INTO " . $prefix . "zawiw_chat_backup (createDT, userID, message) VALUES ";
		while($row = mysqli_fetch_array($result)) 
		{
			$sql .= "('" . $row['createDT'] . "', '" . $row['userId'] . "', '" . $row['message'] . "'),";
		}
			$sql = substr($sql, 0, -1);
			echo $sql. "\n";
			mysqli_query($db, $sql);
			$sql = "DELETE FROM " . $prefix . "zawiw_chat_data WHERE createDT < (NOW() - INTERVAL 7 DAY)";
			echo $sql. "\n";
			mysqli_query($db, $sql);	
	}
}

$webserver = new BroadcastWebSocketServer("0.0.0.0","9999");

try
{
	$webserver->run();
}
catch (Exception $e)
{
	$webserver->stdout($e->getMessage());
}
