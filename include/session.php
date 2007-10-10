<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @version 1.0
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @license SPL
 * @package epesi-base
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

require_once('database.php');

class DBSession {
    private static $lifetime;
    private static $name;

    public static function open($path, $name) {
        self::$lifetime = ini_get("session.gc_maxlifetime");
        return true;
    }

    public static function close() {
        self::gc(self::$lifetime);
        return true;
    }
    
    public static function read($name) {
    	$data = DB::GetOne('SELECT data FROM session WHERE name = %s AND expires > %s', array($name, time()-self::$lifetime));
        return $data;
    }

    public static function write($name, $data) {
        $ret = DB::Replace('session',array('expires'=>time(),'data'=>$data,'name'=>$name),'name',true);
        return ($ret>0)?true:false;
    }

    public static function destroy($name) {
    	DB::Execute('DELETE FROM history WHERE session_name=%s',array($name));
    	DB::Execute('DELETE FROM session WHERE name=%s',array($name));
    	return true;
    }

    public static function gc($lifetime) {
    	$t = time()-$lifetime;
	DB::Execute('DELETE FROM history WHERE session_name IN (SELECT name FROM session WHERE expires < %d)',array($t));
   	DB::Execute('DELETE FROM session WHERE expires < %d',array($t));
        return true;
    }
}

session_set_save_handler(array('DBSession','open'),
                             array('DBSession','close'),
                             array('DBSession','read'),
                             array('DBSession','write'),
                             array('DBSession','destroy'),
                             array('DBSession','gc'));

$subdir = substr(getcwd(),strlen(dirname(dirname(__FILE__))));
$fulldir = dirname($_SERVER['SCRIPT_NAME']);
$document_root = substr($fulldir,0,strlen($fulldir)-strlen($subdir));
$document_root = trim($document_root,'/');
if($document_root) $document_root = '/'.$document_root.'/';
	else $document_root = '/';

session_set_cookie_params(0,$document_root);
session_start();
?>
