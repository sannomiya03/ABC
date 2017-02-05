<?php
require_once dirname(__FILE__)."/../DBI/DBI.class.php";
require_once dirname(__FILE__)."/ManifestMaker.class.php";
require_once dirname(__FILE__)."/TableCollection.class.php";
require_once dirname(__FILE__)."/Console.class.php";
require_once dirname(__FILE__)."/FileIO.class.php";
require_once dirname(__FILE__)."/File.class.php";

class installer{
	public static $SEED_DIR = "/../installer/seeds";

	public static function install(){
		Console::logln("LOAD [MANIFEST]", "Green", 2,true);
		$tables = ManifestMaker::makeManifest();
		$dbi = new DBI();
		self::createTables($dbi, $tables);
		self::loadSeeds($dbi, $tables);
	}

	private static function createTables($dbi, $tables){
		foreach($tables as $table){
			$sql = TableCollection::tableToSQL($table);
			$dbi->dropTable($table->name);
			$dbi->createTable($table->name, $sql);
			// $dbi->alterIndex($table->name, $table->index);
		}
	}

	private static function loadSeeds($dbi, $tables){
		$dir = dirname(__FILE__).self::$SEED_DIR;
		$files = FileIO::loadDir($dir);
		foreach($files as $file){
			$ext = File::getExt($file);
			if($ext=="csv"){
				Console::logln("LOAD [$ext] $file", "Green", 4, true);
				self::loadCSVSeeds($dir."/".$file, $dbi);
			}
		}
	}

	private static function loadCSVSeeds($path, $dbi){
		$list = FileIO::loadCSV($path);
		foreach($list as $rowIndex=>$line){
			if($rowIndex<3) continue;
			$seedObjects = self::generateSeedObject($list, $line);
			foreach($dbi->collection->getTables() as $targetTable){
				if(!$dbi->collection->isPropTable($targetTable->name)){
					self::importSeeds($seedObjects, $targetTable, $dbi);
				}else{
					self::importPropSeeds($seedObjects, $targetTable, $dbi);
				}
			}
		}
	}

	private static function importSeeds($seedObjects, $targetTable, $dbi){
		foreach($seedObjects as $tableName=>$seedObject){
			if($targetTable->name == $tableName){
				$seedObject = self::addDependingField($seedObject, $targetTable, $dbi);
				$insertObj = self::parseSeedObjToInsertObj($seedObject);
				$dbi->append($targetTable->name, $insertObj->keys, $insertObj->values);
			}
		}
	}

	private static function importPropSeeds($seedObjects, $targetPropTable, $dbi){
		foreach($seedObjects as $tableName=>$seedObject){
			if($targetPropTable->name == $tableName){
				foreach($seedObject as $taxonomy=>$property){
					$dependingTableName = $dbi->collection->toTableName($targetPropTable->name);
					$table = $dbi->collection->getTable($dependingTableName);
					$where = self::parseSeedObjToWhere($seedObjects[$table->name], $table->uniques);
					$fieldID = $dbi->getID($table->name, $where);
					$dbi->appendProperty($table->name, $fieldID, $property, $taxonomy);
				}
			}
		}
	}

	private static function addDependingField($seedObject, $targetTable, $dbi){
		foreach($targetTable->dependencies as $dependency){
			$dependingTable = $dbi->collection->getTable($dependency);
			$arr = (array)$seedObject;
			if(!isset($arr[$dependingTable->uid])){
				$where = self::parseSeedObjToWhere($seedObject, $targetTable->uniques);
				$id = $dbi->getValue($dependingTable->name, $dependingTable->uid, $where);
				$arr[$dependingTable->uid] = null;
				$seedObject = (object)$arr;
			}
		}
		return $seedObject;
	}

	/*-------------------------------*/
	private static function parseSeedObjToWhere($seedObject, $uniques){
		$where = "where ";
		foreach($uniques as $unique){
			foreach($seedObject as $key=>$value){
				if($key == $unique) $where .= "$unique = '$value', ";
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

	private static function generateSeedObject($list, $line){
		$seedObjects = array();
		foreach($line as $colIndex=>$value){
			$key = $list[0][$colIndex];
			$table = $list[1][$colIndex];
			$seedObjects[$table][$key] = $value;
		}
		return $seedObjects;
	}
}

