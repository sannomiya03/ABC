<?php
require_once "./../modules/FileIO.class.php";
require_once "./../modules/File.class.php";
require_once "./../modules/Console.class.php";

class DBArcMaker{
	public static $ARC_DIR = "/architecture";
	public static $DEFAULT_ARC = "/architecture/default";
	public static $USERS_ARC = "/architecture/user";

	public static function makeManifest(){
		$files = FileIO::loadDir(dirname(__FILE__).self::$DEFAULT_ARC);
		foreach($files as $file){
			$ext = File::getExt($file);
			if($ext=="json"){
				Console::log("	LOAD [JSON] ","Green");
				Console::logln($file);
			}
		}
		Console::logln($files,"Cyan");
	}
	// public static function loadManifest($path){
	// }
}