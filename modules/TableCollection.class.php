<?php
require_once dirname(__FILE__)."/ManifestMaker.class.php";

class TableCollection{
	public $tables;
	
	public function __construct(){
		$this->tables = ManifestMaker::loadManifest();
	}

	public function getTables(){
		return $this->tables;
	}

	public function getTable($tableName){
		foreach($this->tables as $table)
			if($table->name == $tableName) return $table;
		return null;
	}

	public function getPropTable($tableName){
		$propTableName = self::toPropTableName($tableName);
		return $this->getTable($propTableName);
	}

	public function toPropTable($tableName){
		$table = $this->getTable($table);
		return (object)array(
			"name" => self::toPropTableName($table->name),
			"uid" => self::toPropUID($table->uid),
			"uniques" => array($table->uid, "property_id")
		);
	}

	public static function toPropTableName($tableName){
		if(self::isPropTable($tableName)) return $tableName;
		return $tableName."_properties";
	}
	public static function toPropUID($uid){
		return str_replace("_id", "", $uid);
	}
	public static function toTableName($tableName){
		return str_replace("_properties", "", $tableName);
	}
	public static function toUID($propUID){
		return str_replace("_property_id", "", $uid);
	}

	public static function isPropTable($tableName){
		return strpos($tableName,'_properties') !== false;
	}

	public static function tableToSQL($table){
		$sql = "";
		foreach($table->fields as $index=>$field){
			$sql .= $field->name." ".$field->type." ".$field->option;
			if($index<count($table->fields)-1) $sql .= ", ";
		}
		if(count($table->uniques)>0){
			$sql .= ", unique(".self::uniquesToStr($table->uniques).")";
		}
		return $sql;
	}

	public static function uniquesToStr($uniques){
		$str = "";
		foreach($uniques as $index=>$unique){
			$str .= $unique;
			if($index<count($uniques)-1) $str .= ", ";
		}
		return $str;
	}
}