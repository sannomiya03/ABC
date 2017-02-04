<?php
require_once dirname(__FILE__)."/DBArcMaker.class.php";
require_once dirname(__FILE__)."/../DBI/DBI.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";
require_once dirname(__FILE__)."/../modules/FileIO.class.php";
require_once dirname(__FILE__)."/../modules/File.class.php";

class installer{
	public static function install(){
		Console::logln("LOAD [MANIFEST]", "Green", 2,true);
		$tables = DBArcMaker::makeManifest();
		$dbi = new DBI();
		// self::createTables($dbi, $tables);
		self::loadSeeds($dbi, $tables);
	}

	private static function createTables($dbi, $tables){
		foreach($tables as $table){
			$sql = self::parseTableToSQL($table);
			$dbi->dropTable($table->name);
			$dbi->createTable($table->name, $sql);
			// $dbi->alterIndex($table->name, $table->index);
		}
	}

	private static function loadSeeds($dbi, $tables){
		$dir = dirname(__FILE__)."/seeds";
		$files = FileIO::loadDir($dir);
		foreach($files as $file){
			$ext = File::getExt($file);
			if($ext=="csv"){
				Console::logln("LOAD [$ext] $file", "Green", 4, true);
				self::loadCSVSeeds($dir."/".$file, $dbi, $tables);
			}
		}
	}

	private static function loadCSVSeeds($path, $dbi, $tables){
		$list = FileIO::loadCSV($path);
		foreach($list as $rowIndex=>$line){
			if($rowIndex<3) continue;
			$seedObjects = self::makeSeedObjects($list, $line);

			foreach($tables as $table){
				if(self::isPropRelationTable($table->name)) continue;
				foreach($seedObjects as $tableName=>$seedObject){
					if($tableName != $table->name) continue;
					// Console::log("INSERT > ", "Black", 5, true);
					// Console::logln($table->name, "Brown");
					$seedObject = self::addDependingField($seedObject, $table, $tables, $dbi);
					$insertObj = self::parseSeedObjToInsertObj($seedObject);
					$dbi->append($table->name, $table->uid, $insertObj->keys, $insertObj->values, $table->uniques);
					// Console::logln($insertObj);
				}
			}
		}
	}

	private static function addDependingField($seedObject, $table, $tables, $dbi){
		foreach($table->dependencies as $dependency){
			$dependingTable = self::getTable($tables, $dependency);
			$arr = (array)$seedObject;
			if(!isset($arr[$dependingTable->uid])){
				$where = self::parseSeedObjToWhere($seedObject, $table, $tables, $dependingTable->name);
				$id = $dbi->getValue($dependingTable->name, $dependingTable->uid, $where);
				$arr[$dependingTable->uid] = null;
				$seedObject = (object)$arr;
			}
		}
		return $seedObject;
	}

	private static function parseSeedObjToWhere($seedObject, $table, $tables, $dependingTableName){
		$where = "where ";
		foreach($table->uniques as $unique){
			foreach($seedObject as $key=>$value){
				if($key == $unique){
					$where .= "$unique == '$value', ";
				}
			}
		}
		return rtrim($where, ", ");
	}

	private static function parseSeedObjToInsertObj($seedObject){
		$keys = array();
		$values = array();
		foreach($seedObject as $key=>$value){
			array_push($keys, $key);
			array_push($values, trim($value));
		}
		return (object)array("keys"=>$keys, "values"=>$values);
	}

	private static function getTable($tables, $target){
		foreach($tables as $table)
			if($table->name == $target) return $table;
		Console::logln("[NOT FOUND TABLE!] $target", "Red", 5, true);
	}

	private static function makeSeedObjects($list, $line){
		$seedObjects = array();
		foreach($line as $colIndex=>$value){
			$key = $list[0][$colIndex];
			$table = $list[1][$colIndex];
			$seedObjects[$table][$key] = $value;
		}
		return $seedObjects;
	}

	private static function isPropRelationTable($table){
		return strpos($table,'_properties') !== false;
	}

	private static function hasValue($array, $value){
		foreach($array as $item)
			if($item == $value) return true;
		return false;
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

