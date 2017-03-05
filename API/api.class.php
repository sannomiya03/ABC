<?php
require_once dirname(__FILE__)."/../ABC/modules/Console.class.php";
require_once dirname(__FILE__)."/../ABC/modules/TableCollection.class.php";
require_once dirname(__FILE__)."/../ABC/modules/FileUploader.class.php";
require_once dirname(__FILE__)."/../ABC/DBI/DBI.class.php";

class API{
	public static $SP_SYMBOL = "_____";
	public static $SP_SYMBOL_SEC= "____";
	public $dbi, $uploader;

	public function __construct(){
		$this->dbi = new DBI();
		$this->uploader = new FileUploader();
	}

	public function append($tableName, $keys, $values){
		return $this->dbi->append($tableName, $keys, $values);
	}

	public function put($tableName, $uid, $keys, $vals){
		$this->dbi->updateRecord($tableName, $uid, $keys, $vals);
	}

	public function delete($tableName, $uid){
		$this->dbi->drop($tableName, $uid);
	}

	public function upload($filepath, $filename){
		return $this->uploader->uploadImage($filepath, $filename);
	}

	public function get($option){
		$tableSQL = $this->generateTableSQL($option->table, $option->include);
		$selectSQL = $this->generateSelectSQL($option->fields, $option->table, $option->include);
		$whereSQL = $this->generateWhereSQL($option->filters);
		$optionSQL = $this->generateOrderSQL($option->table, $option->order);
		$limitSQL = $this->generateLimitSQL($option->limit*3);
		if($option->page!=-1) $limitSQL = "";
		$optionalSQL = "$whereSQL $optionSQL $limitSQL";
		// Console::logln($tableSQL, "Purple");
		// Console::logln($selectSQL, "Purple");
		// Console::logln($whereSQL, "Purple");
		$records = $this->dbi->getRecords($tableSQL, $selectSQL, $optionalSQL);
		$records = $this->parseRecordsToResult($records);
		$records = $this->toOptimizedResults($records, $option->table, $option->limit, $option->page);
		// var_dump($results);
		$results = array();
		if($option->page!=-1){
			for($i=$option->page*$option->limit; $i<($option->page+1)*$option->limit; $i++){
				if($i<count($records)-1) array_push($results, $records[$i]);
			}
		}else{
			return $records;
		}
		return $results;
	}

	private function parseRecordsToResult($records){
		$results = array();
		foreach($records as $record){
			$tables = array();
			foreach($record as $key=>$value){
				$field = explode(self::$SP_SYMBOL, $key);
				if(count($field)<2) continue;
				$tableName = $field[0];
				$fieldName = $field[1];
				if(!isset($tables[$tableName])) $tables[$tableName] = array();
				$secondLayer = explode(self::$SP_SYMBOL_SEC, $fieldName);
				if(count($secondLayer)<2){
					$tables[$tableName][$fieldName] = $value;
				}else{
					$secLayerKey = $secondLayer[0];
					$secLayerValue = $secondLayer[1];
					if(!isset($tables[$tableName][$secLayerKey])) $tables[$tableName][$secLayerKey] = array();
					$tables[$tableName][$secLayerKey][$secLayerValue] = $value;
				}
				// Console::logln("$fieldName : $value");
			}
			array_push($results, $tables);
		}
		return $results;
	}

	private function toOptimizedResults($records, $mainTableName, $limit, $page){
		$optimized = array();
		$mainTable = $this->dbi->collection->getTable($mainTableName);
		$prevTableUID = -1;
		$table = array();
		foreach($records as $index=>$record){
			// Console::logln($record);
			$currentTableUID = $record[$mainTable->name][$mainTable->uid];
			if($currentTableUID != $prevTableUID){
				$table = array();
				foreach($record as $tableName=>$fields){
					// Console::logln($tableName, "LightGray", 2, true);
					$table[$tableName] = array();
					foreach($fields as $key=>$value){
						if(gettype($value)=="string") $table[$tableName][$key] = $value;
						if($key == "properties"){
							$table["properties"] = array();
							array_push($table["properties"], array(
								"id" => $value["property_id"],
								"name" => $value["property"],
								"taxonomy" => $fields["taxonomies"]["taxonomy"],
								"dependency" => $tableName
							));
						}
					}
				}
			}else{
				foreach($record as $tableName=>$fields){
					foreach($fields as $key=>$value){
						if($key == "properties"){
							array_push($table["properties"], array(
								"id" => $value["property_id"],
								"name" => $value["property"],
								"taxonomy" => $fields["taxonomies"]["taxonomy"],
								"dependency" => $tableName
							));
						}
					}
				}
			}
			// Console::log($record[$mainTable->name][$mainTable->uid]);
			$prevTableUID = $currentTableUID;
			if($index < count($records)-1){
				$nextTableUID = $records[$index+1][$mainTable->name][$mainTable->uid];
				// Console::logln("next: ".$nextTableUID."/ current: $currentTableUID");
				if($currentTableUID != $nextTableUID){
					array_push($optimized, $table);
				}
			}else{
				array_push($optimized, $table);
			}
			if($page==-1 && count($optimized)==$limit) return $optimized;
		}
		return $optimized;
	}

	/*-----------------------------------------*/
	private function generateTableSQL($tableName, $include){
		$sql = "$tableName";
		$table = $this->dbi->collection->getTable($tableName);
		foreach($include as $index=>$subTableName){
			$subTable = $this->dbi->collection->getTable($subTableName);
			if(!TableCollection::isPropTable($subTableName)){
				$panrentTable = TableCollection::parent($table, $subTable);
				$sql .= " LEFT OUTER JOIN $subTableName ON ( $tableName.".$panrentTable->uid." = $subTableName.".$panrentTable->uid." )";
			}else{
				$dependingTableName = TableCollection::toTableName($subTableName);
				$dependingTable = $this->dbi->collection->getTable($dependingTableName);
				$sql .= " LEFT OUTER JOIN $subTableName ON ( ".$dependingTable->name.".".$dependingTable->uid." = $subTableName.".$dependingTable->uid." )";
				$propTable = $this->dbi->collection->getTable("properties");
				$sql .= " LEFT OUTER JOIN ".$propTable->name." ON ( ".$subTable->name.".property_id = properties.property_id )";
				$taxTable = $this->dbi->collection->getTable("taxonomies");
				$sql .= " LEFT OUTER JOIN ".$taxTable->name." ON ( properties.taxonomy_id = taxonomies.taxonomy_id )";
			}
		}
		return $sql;
	}

	private function generateSelectSQL($fields, $tableName, $include){
		$sql = "";
		$tables = array($this->dbi->collection->getTable($tableName));
		foreach($include as $subTableName){
			array_push($tables, $this->dbi->collection->getTable($subTableName));
		}
		foreach($tables as $table){
			if(!TableCollection::isPropTable($table->name)){
				foreach($table->fields as $field){
					if(count($fields) == 0){
						$sql .= $table->name.".".$field->name." AS ".$table->name.self::$SP_SYMBOL.$field->name.", ";
					}else{
						foreach($fields as $targetField){
							if($field->name == $targetField){
								$sql .= $table->name.".".$field->name." AS ".$table->name.self::$SP_SYMBOL.$field->name.", ";
							}
						}
						if($field->name == $table->uid){
							$sql .= $table->name.".".$field->name." AS ".$table->name.self::$SP_SYMBOL.$field->name.", ";
						}
					}
				}
			}else{
				$dependingTable = $this->dbi->collection->getTable(TableCollection::toTableName($table->name));
				$propTable = $this->dbi->collection->getTable("properties");
				$taxTable = $this->dbi->collection->getTable("taxonomies");
				$sql .= $this->fieldToSQL($fields, $table, $dependingTable->name);
				$sql .= $this->fieldToSQL($fields, $propTable, $dependingTable->name);
				$sql .= $this->fieldToSQL($fields, $taxTable, $dependingTable->name);
			}
		}
		return rtrim($sql, ", ");
	}

	private function fieldToSQL($fields, $table, $parentTableName){
		$sql = "";
		foreach($table->fields as $field){
			if(count($fields) == 0){
				$sql .= $table->name.".".$field->name." AS ".$parentTableName.self::$SP_SYMBOL.$table->name.self::$SP_SYMBOL_SEC.$field->name.", ";
			}else{
				foreach($fields as $targetField){
					if($field->name == $targetField){
						$sql .= $table->name.".".$field->name.", ";
					}
				}
			}
		}
		return $sql;
	}

	private function generateWhereSQL($filters){
		if(count($filters) == 0) return "";
		$sql = "where ";
		foreach($filters as $filter){
			$sql .= "$filter AND ";
		}
		return rtrim($sql, " AND ");
	}

	private function generateOrderSQL($mainTableName, $order){
		if($order == null ) return "";
		else return "ORDER BY $mainTableName"."."."$order";
	}

	private function generateLimitSQL($limit){
		if($limit == -1) return "";
		return "LIMIT $limit";
	}
}