<?php

require_once("../../../wp-load.php");
function generateRandomString($length = 10)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
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
	        if (time() - $fileInfo->getCTime() >= 5*60 AND $fileInfo->getExtension() == "pdf")  //5 Minutes
	        {
	            unlink($fileInfo->getRealPath());
	        }
	    }
	}
}
mb_internal_encoding("UTF-8");
if(!is_user_logged_in())
{
	echo "<div id='zawiw-chat-message'>Sie müssen angemeldet sein, um diese Funktion zu nutzen</div>";
	exit;
}
if(!isset($_POST['download']) || !isset($_POST['from']) || !isset($_POST['to']) || $_POST['from'] == '' || $_POST['to'] == '')
{
	echo "<div id='zawiw-chat-message'>Sie sollten schon über das Formular downloaden</div>";
	exit;
}
global $wpdb;
$timezone = new DateTimeZone('Europe/Berlin');
$zawiw_chat_query = 'SELECT createDT, userId, message FROM ';
$zawiw_chat_query .= $wpdb->get_blog_prefix() . 'zawiw_chat_data WHERE createDT BETWEEN \'' . date_format(date_create($_POST['from'], $timezone),'Y-m-d H:i:s') . '\' AND \'' . date_format(date_create($_POST['to'], $timezone),'Y-m-d H:i:s')  . '\' ' .
'UNION SELECT createDT, userId, message FROM '. $wpdb->get_blog_prefix() .'zawiw_chat_backup WHERE createDT BETWEEN \'' . date_format(date_create($_POST['from'], $timezone),'Y-m-d H:i:s') . '\' AND \'' . date_format(date_create($_POST['to'], $timezone),'Y-m-d H:i:s') . '\' ORDER BY createDT ASC';
$zawiw_chat_item = $wpdb->get_results( $zawiw_chat_query, ARRAY_A );
include('mpdf/MPDF57/mpdf.php');
$mpdf=new mPDF();
$mpdf->WriteHTML("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body>");
$mpdf->WriteHTML("<span style='float: left'>". $_SERVER['SERVER_NAME'] . "</span>");
$mpdf->WriteHTML("<h1 style='text-align:center; font-family: sans;'>Chat-History</h1><h2 style='text-align:center; font-family: sans; padding-bottom:20px;'>" . $_POST['from'] . " - " . $_POST["to"] . "</h2>");
foreach ($zawiw_chat_item as $chat_item)
{
	$userdata = get_user_by( 'id', $chat_item['userId'] );
	$mpdf->WriteHTML("<div style='padding-left: 8px; font-family: emoji;'>" .  $userdata->display_name . "</div>");
	$mpdf->WriteHTML("<div style='padding-left: 8px; font-family: emoji;'>" . date_format( date_create($chat_item['createDT']), 'd.m.Y H:i') . "</div>");
	$mpdf->WriteHTML("<div style=\"font-size: 16pt; font-family:emoji; background-image: linear-gradient(#D5D5D5 0%, #EEE 100%); border-radius:8px; padding: 8px; margin-bottom: 10px; box-shadow: 2px 2px 2px rgba(50,50,50,.4);\">" . utf8_decode($chat_item['message']) . "</div><br />");
}
$mpdf->WriteHTML("</body></html>");
$file = generateRandomString() . ".pdf";
$mpdf->Output(dirname( __FILE__ ) . "/pdfs/" . $file);
echo "../wp-content/plugins/zawiw-chat/pdfs/" . $file;
deleteOldPdfs();

?>