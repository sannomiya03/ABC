<?php
require_once "./../modules/FileIO.class.php";
require_once "./../modules/File.class.php";
require_once "./../modules/Console.class.php";

class DBArcMaker{
	public static $ARC_DIR = "/architecture";
	public static $DEF_ARC_DIR= "/default";
	public static $USER_ARC_DIR = "/user";


	/* ------------------------------------------ */
	public static function loadManifest(){
		return FileIO::loadJSON(dirname(__FILE__).self::$ARC_DIR."/manifest.json");
	}

	public static function makeManifest(){
		$tables = self::loadTables();
		$tables = self::initTables($tables);
		$tables = self::addPropatyRelationTables($tables);
		FileIO::save($tables, dirname(__FILE__).self::$ARC_DIR."/manifest.json");
		return $tables;
	}

	/* ------------------------------------------ */
	private static function loadTables(){
		$defArcPath = dirname(__FILE__).self::$ARC_DIR.self::$DEF_ARC_DIR."/architecture.json";
		$urerArcPath = dirname(__FILE__).self::$ARC_DIR.self::$USER_ARC_DIR."/architecture.json";
		Console::log("	LOAD [JSON] ","Green");
		Console::logln($defArcPath);
		Console::log("	LOAD [JSON] ","Green");
		Console::logln($urerArcPath);
		$tables = array();
		foreach(FileIO::loadJSON($defArcPath) as $table){
			$table->owner = "default";
			array_push($tables, $table);
		}
		foreach(FileIO::loadJSON($urerArcPath) as $table){
			$table->owner = "user";
			array_push($tables, $table);
		}
		return $tables;
	}

	private static function loadFields($table){
		$dir = dirname(__FILE__).self::$ARC_DIR;
		if($table->owner == "default") $dir .= self::$DEF_ARC_DIR;
		else $dir .= self::$USER_ARC_DIR;
		$filePath = $dir."/".$table->name.".json";
		if(file_exists($filePath)){
			foreach(FileIO::loadJSON($filePath) as $field) array_push($table->fields, $field);
		}
		return $table->fields;
	}

	private static function initTables($tables){
		foreach($tables as $table){
			$table = self::initTable($table);
			$table->fields = self::initField($table, $tables);
		}
		return $tables;	
	}

	private static function initTable($table){
		if(!property_exists($table,"owner")) $table->owner = null;
		if(!property_exists($table,"dependencies")) $table->dependencies = array();
		if(!property_exists($table,"fields")) $table->fields = array();
		if(!property_exists($table,"useProperty")) $table->useProperty = false;
		$table->fields = self::loadFields($table);
		return $table;
	}

	private static function initField($table, $tables){
		foreach($table->dependencies as $dependency){
			$dependedTable = self::getTable($dependency, $tables);
			$dependedField = self::newField($dependedTable->uid, "int", "not null");
			array_unshift($table->fields, $dependedField);
		}
		$primaryField = self::newField($table->uid, "int", "not null auto_increment primary key");
		$createdField = self::newField("created", "timestamp", "not null DEFAULT '0000-00-00 00:00:00'");
		$modifiedField = self::newField("modified", "timestamp", "not null DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
		array_unshift($table->fields, $primaryField);
		array_push($table->fields, $createdField);
		array_push($table->fields, $modifiedField);
		foreach($table->fields as $field){
			$field->type = self::parseType($field->type);
		}
		return $table->fields;
	}

	private static function addPropatyRelationTables($tables){
		foreach($tables as $table){
			if($table->useProperty){
				$propRelTable = new stdClass;
				$propRelTable->name = $table->name."_properties";
				$propRelTable->uid = str_replace("_id", "", $table->uid)."_property_id";
				$propRelTable->dependencies = array($table->name, "properties");
				$propRelTable->uniques = array($table->uid, "property_id");
				$propRelTable = self::initTable($propRelTable);
				$propRelTable->fields = self::initField($propRelTable, $tables);
				array_push($tables, $propRelTable);
			}
		}
		return $tables;
	}


	/* ------------------------------------------ */
	private static function newField($name, $type, $option){
		$field = new stdClass;
		$field->name = $name;
		$field->type = $type;
		$field->option = $option;
		return $field;
	}

	private static function getTable($name, $tables){
		foreach($tables as $table){
			if($table->name == $name) return $table;
		}
		Console::log("NOT FOUND > [$name] in tables!!","Red");
		return null;
	}

	private static function parseType($type){
		if($type == "string") return "varchar(255)";
		if($type == "int") return "int(255)";
		return $type;
	}
}