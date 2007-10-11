<?php
if(!isset($_POST['acc_id']))
	die('Invalid request');

require_once('../../../include.php');
Epesi::init();
if(!Acl::is_user()) return;

ini_set('include_path',dirname(__FILE__).'/PEAR'.PATH_SEPARATOR.ini_get('include_path'));

$id = $_POST['acc_id'];
$account = DB::GetAll('SELECT * FROM apps_mailclient_accounts WHERE id=%d AND user_login_id=%d',array($id,Base_UserCommon::get_my_user_id()));
if(!$account) continue;
$account = $account[0];
	
$host = explode(':',$account['incoming_server']);
if(isset($host[1])) $port=$host[1];
	else $port = null;
$host = $host[0];
$user = $account['login'];
$pass = $account['password'];
$ssl = $account['incoming_ssl'];

if($account['incoming_protocol']==0) { //pop3
	require_once('Net/POP3.php');
	$pop3 = new Net_POP3();
	
	if($port==null) {
		if($ssl) $port=995;
		else $port=110;
	}
	if(PEAR::isError( $ret= $pop3->connect(($ssl?'ssl://':'').$host , $port) )){
		print($ret->getMessage());
	}
		
	$method = $account['pop3_method']!='auto'?$account['pop3_method']:null;
	if(PEAR::isError( $ret= $pop3->login($user , $pass, $method))){
		print($ret->getMessage());
	}
	$num_msgs = $pop3->numMsg();
	$pop3->disconnect();
} else { //imap
	require_once('Net/IMAP.php');
	if($port==null) {
		if($ssl) $port=993;
		else $port=143;
	}
	$imap = new Net_IMAP(($ssl?'ssl://':'').$host,$port);
	if(PEAR::isError( $ret= $imap->login($user , $pass))){
		print($ret->getMessage());
	}
	
	$imap->selectMailbox('inbox');
	$num_msgs = $imap->getNumberOfMessages();
	$imap->disconnect();
}
print($num_msgs);
?>