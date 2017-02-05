<?php
require_once dirname(__FILE__)."/../modules/Console.class.php";
require_once dirname(__FILE__)."/../modules/TableCollection.class.php";
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
		// Console::logln($selectSQL, "Purple");
		// Console::logln($whereSQL, "Purple");
		$records = $this->dbi->getRecords($tableSQL, $selectSQL, "$whereSQL $optionSQL $limitSQL");
		$results = $this->parseRecordsToResult($records);
		return $results;
	}

	private function parseRecordsToResult($records){
		$results = array();
		foreach($records as $record){
			$tables = array();
			foreach($record as $key=>$value){
				$field = explode("____", $key);
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

	// private function addProperties($results, $option){
	// 	foreach($option->include as $tableName){
	// 		if(TableCollection::isPropTable($tableName)){
	// 			$propTable = $this->dbi->collection->getTable($tableName);
	// 			$dependingTable = $this->dbi->collection->getTable(TableCollection::toTableName($tableName));
	// 			$properties = array();
				
	// 			$tableSQL = $propTable->name." LEFT OUTER JOIN properties ON ( ".propTable->name.".".$propTable->uid." = $subTableName.".$subTable->uid." )";
	// 			if(TableCollection::isPropTable($subTableName)){
	// 				$propTable = $this->dbi->collection->getTable("properties");
	// 				$sql .= " LEFT OUTER JOIN ".$propTable->name." ON ( ".$subTableName.".".$propTable->uid." = ".$propTable->name.".".$propTable->uid." )";
	// 				$taxTable = $this->dbi->collection->getTable("taxonomies");
	// 				$sql .= " LEFT OUTER JOIN ".$taxTable->name." ON ( properties.taxonomy_id = taxonomies.taxonomy_id )";
	// 			}
	// 			$tableSQL = $this->generateTableSQL($option->table, $option->include);
	// 			$selectSQL = $this->generateSelectSQL($option->fields, $option->table, $option->include);
	// 			$whereSQL = $this->generateWhereSQL($option->filters);
	// 			$optionSQL = $this->generateOrderSQL($option->order);
	// 			$limitSQL = $this->generateLimitSQL($option->limit);
	// 			$propRecords = $this->dbi->getRecords($table->name, "*", "");
	// 		}
	// 	}
	// 	return $results;
	// }

	/*-----------------------------------------*/
	private function generateTableSQL($tableName, $include){
		$sql = "$tableName";
		$table = $this->dbi->collection->getTable($tableName);
		foreach($include as $index=>$subTableName){
			$subTable = $this->dbi->collection->getTable($subTableName);
			if(!TableCollection::isPropTable($subTableName)){
				$sql .= " LEFT OUTER JOIN $subTableName ON ( $tableName.".$subTable->uid." = $subTableName.".$subTable->uid." )";
			}else{
				// $dependingTableName = TableCollection::toTableName($subTableName);
				// $dependingTable = $this->dbi->collection->getTable($dependingTableName);
				// $sql .= " LEFT OUTER JOIN $subTableName ON ( ".$dependingTable->name.".".$dependingTable->uid." = $subTableName.".$subTable->uid." )";
				// $propTable = $this->dbi->collection->getTable("properties");
				// $sql .= " LEFT OUTER JOIN ".$propTable->name." ON ( ".$subTable->name.".".$dependingTable->uid." = ".$propTable->name.".".$dependingTable->uid." )";
				// $taxTable = $this->dbi->collection->getTable("taxonomies");
				// $sql .= " LEFT OUTER JOIN ".$taxTable->name." ON ( properties.taxonomy_id = taxonomies.taxonomy_id )";
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
						$sql .= $table->name.".".$field->name." AS ".$table->name."____".$field->name.", ";
					}else{
						foreach($fields as $targetField){
							if($field->name == $targetField){
								$sql .= $table->name.".".$field->name.", ";
							}
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