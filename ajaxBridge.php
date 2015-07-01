<?php
require_once("../../../wp-load.php");
ini_set('display_errors', '1');
ini_set('error_reporting', 'E_ALL');
mb_internal_encoding("UTF-8");
class TestWebSocketClient
{
	private $socket;
	public function __construct()
	{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	}
	public function connect()
	{
		$host = "88.80.205.25";
		$port = 10000;
		if(!socket_connect($this->socket, $host, $port))
            die("Error while connecting to " . $host . ":" . $port);
		$header = "GET / HTTP/1.1\r\n";
		$header.= "Host: ".$host."\r\n";
		$header.= "Upgrade: WebSocket\r\n";
		$header.= "Connection: Upgrade\r\n";
        $header.= "Sec-WebSocket-Extensions:permessage-deflate; client_max_window_bits\r\n";
		$header.= "Sec-WebSocket-Version: 13\r\n";
        $header.= "Sec-WebSocket-Protocol: wamp\r\n";
		$header.= "Sec-WebSocket-Key: joTziHwyn2Lqn3BtxwlfXw==\r\n";
		$header.= "Origin: \r\n\r\n";
		if(!socket_send($this->socket, $header, strlen($header), 0) !== FALSE)
			die("HEADERS NOT SENT!!");
        socket_recv($this->socket, $data, 1000, 0);
        if(!(preg_match('/Sec-WebSocket-Accept/', $data) === 1 && preg_match('/HTTP\/1\.1 101 Switching Protocols/', $data) === 1))
            die("NO CONNECTION ESTABLISHED!!");
        $this->sendAuth();
	}

    public function disconnect()
    {
        if($socket_send($this->socket, "\xFF\x00", 3, 0) === FALSE)
            die("NO DISCONNECT!!");
        sleep(1);
        socket_close($this->socket);
    }
    private function sendAuth()
    {
        global $wpdb;
        $authData = "<prefix>" . $wpdb->get_blog_prefix() . "</prefix>";
        foreach ($_COOKIE as $keks => $keksValue)
            $authData .= $keks . ":" . $keksValue . ";";
        $this->send($authData);
    }
    public function send($msg)
    {
        $msg =  $this->frameWebSocket($msg);
        if(!(socket_send($this->socket, $msg, strlen($msg), 0) !== FALSE))
            die("FAILED TO SEND DATA!!");
    }
    public function postData2Msg($postData)
    {
        global $wpdb;
        if(isset($postData['msg']) && $postData['msg'] != "" && isset($postData['submit']) && $postData['submit'] != "")
            return "<userId>" . get_current_user_id() . "</userId><prefix>" . $wpdb->get_blog_prefix() . "</prefix>" . $postData['msg'];
        else
            die("Error with your POST data!!");
    }
	private function frameWebSocket($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);
        switch ($type) 
        {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;
            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;
            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;
            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }
        if ($payloadLength > 65535) 
        {
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 7; $i >= 0; $i--)
                $frameHead[$i + 2] = ($payloadLength >> ((7 - $i)*8)) & 0xff;
            if ($frameHead[2] > 127)
            {
                $this->close(1004);
                return false;
            }
        }
        elseif ($payloadLength > 125)
        {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = ($payloadLength >> 8) & 0xff;
            $frameHead[3] = $payloadLength & 0xff;
        }
        else
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        foreach (array_keys($frameHead) as $i)
            $frameHead[$i] = chr($frameHead[$i]);
        if ($masked === true) {
            $mask = array();
            for ($i = 0; $i < 4; $i++)
            {
                $mask[$i] = chr(rand(0, 255));
                array_push($frameHead, $mask[$i]);
            }
        }
        $frame = implode('', $frameHead);
        for ($i = 0; $i < $payloadLength; $i++)
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        return $frame;
	}
    private function deframeWebSocket($msg)
    {
        if (empty($msg))
            return null;
        $bytes = $msg;
        $msgLength = '';
        $mask = '';
        $coded_msg = '';
        $decodedmsg = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $msgLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);
        if ($masked === true)
        {
            if ($msgLength === 126) 
            {
                $mask = substr($bytes, 4, 4);
                $coded_msg = substr($bytes, 8);
            }
            elseif ($msgLength === 127)
            {
                $mask = substr($bytes, 10, 4);
                $coded_msg = substr($bytes, 14);
            }
            else
            {
                $mask = substr($bytes, 2, 4);
                $coded_msg = substr($bytes, 6);
            }
            for ($i = 0; $i < strlen($coded_msg); $i++)
                $decodedmsg .= $coded_msg[$i] ^ $mask[$i % 4];
        }
        else
            if ($msgLength === 126)
                $decodedmsg = substr($bytes, 4);
            elseif ($msgLength === 127)
                $decodedmsg = substr($bytes, 10);
            else
                $decodedmsg = substr($bytes, 2);
        return $decodedmsg;
    }

}
$client = new TestWebSocketClient;
$msg = $client->postData2Msg($_POST);
$client->connect();
$client->send($msg);
echo "Success";
$client->disconnect();


?>
