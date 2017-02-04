<?php
require_once "./../modules/FileIO.class.php";
require_once "./../modules/File.class.php";
require_once "./../modules/Console.class.php";

class DBArcMaker{
	public static $ARC_DIR = "/architecture";
	public static $DEFAULT_ARC_DIR= "/default";
	public static $USERS_ARC_DIR = "/user";

	public static function makeManifest(){
		$arc = self::loadArchitecture();
		foreach($arc as $index=>$table){
			$arc[$index] = (object)$arc[$index];
			$arc[$index]->fields = array();
			// Console::log($table);
		}
		Console::logln($arc,"Blue");
		// $files = FileIO::loadDir(dirname(__FILE__).self::$ARC_DIR.self::$DEFAULT_ARC_DIR);
		// foreach($files as $file){
		// 	$ext = File::getExt($file);
		// 	if($ext=="json"){
		// 		Console::log("	LOAD [JSON] ","Green");
		// 		Console::logln($file);
		// 	}
		// }
		// Console::logln($files,"Cyan");
	}
	// public static function loadManifest($path){
	// }

	private static function loadArchitecture(){
		$defaultArcPath = dirname(__FILE__).self::$ARC_DIR.self::$DEFAULT_ARC_DIR."/architecture.json";
		$urersArcPath = dirname(__FILE__).self::$ARC_DIR.self::$USERS_ARC_DIR."/architecture.json";
		Console::log("	LOAD [JSON] ","Green");
		Console::logln($defaultArcPath);
		Console::log("	LOAD [JSON] ","Green");
		Console::logln($urersArcPath);
		$arc = array_merge(FileIO::loadJSON($defaultArcPath),FileIO::loadJSON($urersArcPath));
		// Console::logln($arc,"Blue");
		return $arc;
	}
}