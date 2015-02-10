<?php

define('WP_INSTALLING', true);
ini_set('mysqli.reconnect', 1);
require_once(dirname(__FILE__).'/websockets.php');

// MySQL database connection to wrppress
$db = mysqli_connect("0.0.0.0", "web10", "FD26Ur2k", "usr_web10_1");
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
//$db = sqlite_open('db.sqlite');###########
//$q = sqlite_query($db, 'CREATE TABLE messages (id int, msg TEXT)');###########

class BroadcastWebSocketServer extends WebSocketServer
{
	protected $users = array();
	protected function process($user, $message)
	{
		$prefix = preg_filter('/<prefix>(.*?)<\/prefix>.*$/', "$1", utf8_decode($message));
		$message = utf8_decode(preg_replace('/<prefix>.*?<\/prefix>/', "", $message));

		//ist benutzer in anwärter array? check cookies ....
		if(!$user->authentified)
		{
			if(!$this->user_authentified($user, $message))
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
				echo $sql . "\n";
				$result = mysqli_query($db, $sql);

				while($row = mysqli_fetch_array($result)) 
				{
					$this->send($user, $this->build_message($row['createDT'], $row['userId'], $row['message']));
				}
				/*
				$q = sqlite_query($db, 'SELECT * FROM messages');###########
				while($entry = sqlite_fetch_array($q))###########
				$this->send($user, $entry['msg']);###########
				*/
			}
		}
		else
		{
			$this->broadcast($message, $prefix);
		}

	}

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

		if(is_user_logged_in())
		{
			$key_value = array();
			$key_value = explode(':', $keks);
			//print_r($_COOKIE);
			$user->authentified = true;

			foreach($_COOKIE as $keks => $keks_value)
			{
				unset($_COOKIE[$keks]);
			}
			return true;

		}
		else
		{
			foreach($_COOKIE as $keks)
			{
				unset($_COOKIE[$keks]);
			}
			print_r($_COOKIE);
			return false;
		}
	}

	protected function connected($user)
	{
		
	}
	
	protected function closed($user)
	{
		$num = array_search($user, $this->users);
		if($num)
			unset($this->users[$num]);
	}
	protected function broadcast($message, $prefix)
	{
		//global $wpdb;
		$prefix = preg_replace('/<userId>.*?<\/userId>/', "", $prefix);
		$id = preg_filter('/<userId>(.*?)<\/userId>.*$/', "$1", utf8_decode($message));
		$message = utf8_decode(preg_replace('/<userId>.*?<\/userId>/', "", $message));
		$timezone = new DateTimeZone('Europe/Berlin');
		$timestamp = date_format(date_create("now", $timezone),'Y-m-d H:i:s');

		global $db;
		$sql = "INSERT INTO " . $prefix . "zawiw_chat_data (createDT, userId, message) VALUES ('" . $timestamp . "', '". $id . "', '". $message . "')";
		echo $sql;
		mysqli_query($db, $sql);
		//$q = sqlite_query($db, "INSERT INTO messages (msg) VALUES ('$message')");###########
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
