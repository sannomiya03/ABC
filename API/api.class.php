<?php
require_once dirname(__FILE__)."/../modules/Console.class.php";
require_once dirname(__FILE__)."/../DBI/DBI.class.php";

class API{
	public $dbi;

	public function __construct(){
		$this->dbi = new DBI();
	}

	public function get($option){
		$tableSQL = $this->generateTableSQL($option->table, $option->include);
		$selectSQL = $this->generateSelectSQL($option->fields, $option->table, $option->include);
		$whereSQL = $this->generateWhereSQL($option->filters);
		$optionSQL = $this->generateOrderSQL($option->order);
		$limitSQL = $this->generateLimitSQL($option->limit);
		Console::logln($tableSQL, "Purple");
		Console::logln($selectSQL, "Purple");
		Console::logln($whereSQL, "Purple");
		$records = $this->dbi->getRecords($tableSQL, $selectSQL, "$whereSQL $optionSQL $limitSQL");
		$results = $this->parseRecordsToResult($records);
		return $results;
	}

	private function parseRecordsToResult($records){
		$results = array();
		foreach($records as $record){
			$tables = array();
			foreach($record as $key=>$value){
				$field = explode("___", $key);
				if(count($field)<2) continue;
				$tableName = $field[0];
				$fieldName = $field[1];
				if(!isset($tables[$tableName])) $tables[$tableName] = array();
				$tables[$tableName][$fieldName] = $value;
			}
			array_push($results, $tables);
		}
		return $results;
	}

	private function generateTableSQL($tableName, $include){
		$sql = "$tableName";
		$table = $this->dbi->collection->getTable($tableName);
		foreach($include as $index=>$subTableName){
			$subTable = $this->dbi->collection->getTable($subTableName);
			$sql .= " LEFT OUTER JOIN $subTableName ON ( $tableName.".$table->uid." = $subTableName.".$subTable->uid." )";
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
			foreach($table->fields as $field){
				if(count($fields) == 0){
					$sql .= $table->name.".".$field->name." AS ".$table->name."___".$field->name.", ";
				}else{
					foreach($fields as $targetField){
						if($field->name == $targetField){
							$sql .= $table->name.".".$field->name.", ";
						}
					}
				}
			}
		}
		return rtrim($sql, ", ");
	}

	private function generateWhereSQL($filters){
		if(count($filters) == 0) return "";
		$sql = "where ";
		foreach($filters as $filter){
			$sql .= "$filter AND ";
		}
		return rtrim($sql, " AND ");
	}

	private function generateOrderSQL($order){
		if($order == null ) return "";
		else return "ORDER BY $order";
	}

	private function generateLimitSQL($limit){
		if($limit == -1) return "";
		return "LIMIT $limit";
	}
}