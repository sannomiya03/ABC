<?php
require_once dirname(__FILE__)."/DBArcMaker.class.php";
require_once dirname(__FILE__)."/../DBI/DBI.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";

class installer{
	public static function install(){
		Console::logln("LOAD [MANIFEST]", "Green", 2,true);
		$tables = DBArcMaker::makeManifest();
		$dbi = new DBI();
		foreach($tables as $table){
			$sql = self::parseTableToSQL($table);
			$dbi->dropTable($table->name);
			$dbi->createTable($table->name, $sql);
			// $dbi->alterIndex($table->name, $table->index);
		}
	}

	private static function parseTableToSQL($table){
		$sql = "";
		foreach($table->fields as $index=>$field){
			$sql .= $field->name." ".$field->type." ".$field->option;
			if($index<count($table->fields)-1) $sql .= ", ";
		}
		if(count($table->uniques)>0){
			$sql .= ", unique(".self::parseUniquesToStr($table->uniques).")";
		}
		return $sql;
	}

	private static function parseUniquesToStr($uniques){
		$str = "";
		foreach($uniques as $index=>$unique){
			$str .= $unique;
			if($index<count($uniques)-1) $str .= ", ";
		}
		return $str;
	}
}

