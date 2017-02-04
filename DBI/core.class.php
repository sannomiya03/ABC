<?php
require_once dirname(__FILE__)."/Parser.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";
require_once dirname(__FILE__)."/../modules/FileIO.class.php";

class DBICore{
	public static $SETTING_PATH = "../setting.json";
	public $pdo, $host, $dbName, $user, $pass, $dbms;
	
	public function __construct(){
		$setting = FileIO::loadJSON(dirname(__FILE__)."/".self::$SETTING_PATH);
		$this->host = $setting->host;
		$this->dbName = $setting->db_name;
		$this->user = $setting->user;
		$this->pass = $setting->pass;
		$this->dbms = $setting->dbms;

		if( $dbms == "mysql" ){
			try{ $pdo = new PDO( "mysql:host=$host; dbname=$dbName;charset=utf8;", $user, $pass ); }
			catch( PDOException $e ){ var_dump($e->getMessage()); exit; }
		}else{
			if(!file_exists(dirname(__FILE__)."/../SQLite")){
				mkdir(dirname(__FILE__)."/../SQLite");
			}
			try{
				$pdo = new PDO( "sqlite:".dirname(__FILE__)."/../SQLite/$dbName.db", "root", "root");
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			} catch( PDOException $e ){ var_dump($e->getMessage()); exit; }
		}
		$this->pdo = $pdo;
	}

	/* ---------------------------------------------
	 * CREATE
	 * --------------------------------------------- */
	public function createTable($table, $sql){
		$sql = "CREATE TABLE IF NOT EXISTS $table ($sql)";
		Console::logln($sql,"Purple");
		$stmt = $this->pdo->exec($sql);
	}
	
	public function alterIndex($table, $column){
		$sql = "ALTER TABLE $table ADD INDEX $column ($column)";
		$stmt = $this->pdo->exec($sql);
	}

	public function addRecord($table, $uid, $keys, $vals, $uniques=null){
		printf("	INSERT to %-10s -> ", $table);
		if($uniques == null) $uniques = $keys;
		$where = Parser::uniquesToStr($uniques,$keys, $vals);
		$id = $this->getValue($table, $uid, $where);
		if($id == ""){
			$id = $this->insert($table, $keys, $vals);
			$id = $this->getValue($table, $uid, $where);
			echo "[NEW] id: $id\n";
			return $id;
		}else{
			echo "[FOUND] id: $id\n";
			return $id;
		}
	}

	/* ---------------------------------------------
	 * QUERY
	 * --------------------------------------------- */
	public function getRecord($table, $field, $where=""){
		$stmt = $this->select($table, $field, $where);
		if($stmt == false) return null;
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getRecords($table, $field, $where=""){
		$stmt = $this->select($table, $field, $where);
		$results = array();
		if($stmt==null) return $results;
		foreach($stmt as $result) array_push($results, $result);
		return $results;
	}

	public function getValue($table, $value, $where=""){
		$field = $this->getRecord($table, $value, $where);
		if($filed == null) return null;
		return $field[$value];
	}

	public function getValues($table, $value, $where=""){
		$records = $this->getRecords($table, $value, $where);
		$results = array();
		foreach($records as $record) array_push($result, $record[$value]);
		return $results;
	}

	public function getRecordsByLike($table, $field, $whereKey, $whereVal){
		$results = array();
		foreach($this->like($table, $field, $whereKey, $whereVal) as $result){
			array_push($results, $result);
		}
		return $results;
	}

	/* ---------------------------------------------
	 * UPDATE
	 * --------------------------------------------- */
	public function update($table, $keys, $vals, $whereKeys, $whereVals){
		$keyStr = Parser::arrToParamStr($keys, "=:", ", ");
		$where = Parser::arrToParamStr($whereKeys, "=:", " AND ");
		$sql = "UPDATE $table SET $keyStr WHERE $where";
		try{
			$stmt = $this->pdo->prepare($sql);
			foreach($keys as $index=>$key)
				$stmt->bindParam(":".$key, $vals[$index], PDO::PARAM_STR);
			foreach($whereKeys as $index=>$key)
				$stmt->bindParam(":".$key, $whereVals[$index], PDO::PARAM_STR);
			$stmt->execute();
		}catch(Exception $e){ echo $e->getMessage(); }
	}

	/* ---------------------------------------------
	 * DELETE
	 * --------------------------------------------- */
	public function delete($table, $whereKeys, $whereVals){
		$where = Parser::arrToParamStr($whereKeys, "=:", " AND ");
		$sql = "delete from $table where $where";
		try {
			$stmt = $this->pdo->prepare($sql);
			foreach($whereKeys as $index=>$key)
				$stmt->bindParam(":".$key, $whereVals[$index], PDO::PARAM_STR);
			$stmt->execute();
		}catch(Exception $e){ echo $e->getMessage(); }
	}

	/* ---------------------------------------------
	 * PRIVATE
	 * --------------------------------------------- */
	private function select($table, $field, $where=""){
		$sql = "select $field from ".$table." ".$where;
		$stmt = $this->pdo->query($sql);
		if(!$stmt) return null;
		return $stmt;
	}
	private function like($table, $field, $whereKey, $whereVal){
		$sql = "select $field from $table where $whereKey like :key";
		$stmt = $this->pdo->prepare($sql);
		$like = '%'."$whereVal".'%';
		$stmt->bindParam(":key", $like, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt;
	}
	private function insert($table, $keys, $vals){
		$keyStr = Parser::arrToParamStr($keys, "", ", ");
		$valStr = Parser::arrToParamStr($keys, ":", ", ");
		$sql = "insert into $table ( $keyStr ) value ( $valStr )";
		try{
			$stmt = $this->pdo->prepare($sql);
			foreach($keys as $index=>$key)
				$stmt->bindParam(":".$key, $vals[$index], PDO::PARAM_STR);
			$stmt->execute();
		}catch(Exception $e){ echo $e->getMessage(); }
	}
}
