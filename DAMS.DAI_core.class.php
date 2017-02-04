<?php
require_once dirname(__FILE__)."/config.php";

class DAI_core{
	public $pdo;
	
	public function __construct(){
		if( DBMS == "mysql" ){
			try{ $pdo = new PDO( "mysql:host=".HOST.";dbname=".DB_NAME.";charset=utf8;", USER, PASS ); }
			catch( PDOException $e ){ var_dump($e->getMessage()); exit; }
		}else{
			if(!file_exists(dirname(__FILE__)."/../SQLite")){
				mkdir(dirname(__FILE__)."/../SQLite");
			}
			try{
				$pdo = new PDO( "sqlite:".dirname(__FILE__)."/../SQLite/".DB_NAME.".db", "root", "root");
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			} catch( PDOException $e ){ var_dump($e->getMessage()); exit; }
		}
		$this->pdo = $pdo;
	}

	/* ---------------------------------------------
	 * CREATE
	 * --------------------------------------------- */
	public function createTable( $table, $sql ){
		$sql = "CREATE TABLE IF NOT EXISTS $table ($sql)";
		$stmt = $this->pdo -> exec($sql);
	}
	
	public function alterIndex( $table, $column ){
		$sql = "ALTER TABLE $table ADD INDEX $column ($column)";
		$stmt = $this->pdo -> exec($sql);
	}

	public function insert( $table, $primKey, $keys, $vals, $uniques=null ){
		printf("INSERT to %-10s -> ", $table);
		if( $uniques == null ) $uniques = $keys;
		$whereStr = $this->createWhereStrUnique( $keys, $vals, $uniques );
		$id = $this->getValue( $table, $primKey, $whereStr );
		if( $id == "" ){
			$id = $this->_insert( $table, $keys, $vals );
			$id = $this->getValue( $table, $primKey, $whereStr );
			echo "new item, id: $id\n";
			return $id;
		}else{
			echo "already exist, id: $id\n";
			return $id;
		}
	}

	/* ---------------------------------------------
	 * QUERY
	 * --------------------------------------------- */
	public function getField( $table, $field, $where="" ){
		$stmt = $this->select( $table, $field, $where );
		if( $stmt == false ) return null;
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	public function getFields( $table, $field, $where="" ){
		$stmt = $this->select( $table, $field, $where );
		if( $stmt==null ) return null;
		$results = array();
		foreach( $stmt as $line ) array_push($results, $line);
		return $results;
	}
	public function getValue( $table, $val, $where="" ){
		return $this->getField( $table, $val, $where)[$val];
	}
	public function getValues( $table, $val, $where="" ){
		$records = $this->getFields( $table, $val, $where);
		$result = array();
		foreach($records as $record){
			array_push($result, $record[$val]);
		}
		return $result;
	}
	public function getFieldLike( $table, $field, $whereKey, $whereVal ){
		$results = array();
		foreach( $this->like($table, $field, $whereKey, $whereVal) as $line ){
			array_push($results, $line);
		}
		return $results;
	}

	/* ---------------------------------------------
	 * UPDATE
	 * --------------------------------------------- */
	public function update( $table, $keys, $vals, $where_keys, $where_vals ){
		$key_strings = $this->arrToParamStr( $keys, "=:", ", " );
		$where_str = $this->arrToParamStr( $where_keys, "=:", " AND " );
		$sql = "UPDATE $table SET $key_strings WHERE $where_str";
		try {
			$stmt = $this->pdo->prepare($sql);
			for($i=0; $i<count($keys); $i++ ){
				$stmt->bindParam(":".$keys[$i], $vals[$i], PDO::PARAM_STR);
			}
			for($i=0; $i<count($where_keys); $i++ ){
				$stmt->bindParam(":".$where_keys[$i], $where_vals[$i], PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(Exception $e){ echo $e->getMessage(); }
	}

	/* ---------------------------------------------
	 * DELETE
	 * --------------------------------------------- */
	public function delete( $table, $where_keys, $where_vals ){
		$where_str = $this->arrToParamStr( $where_keys, "=:", " AND " );
		$sql = "delete from $table where $where_str";
		try {
			$stmt = $this->pdo->prepare($sql);
			for( $i=0; $i<count($where_keys); $i++ ){
				$stmt->bindParam(":".$where_keys[$i], $where_vals[$i], PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(Exception $e){ echo $e->getMessage(); }
	}

	/* ---------------------------------------------
	 * PRIVATE
	 * --------------------------------------------- */
	private function select( $table, $field, $where="" ){
		$sql = "select $field from ".$table." ".$where;
		$stmt = $this->pdo->query($sql);
		if(!$stmt) return null;
		return $stmt;
	}
	private function like( $table, $field, $wherekey, $whereval ){
		$sql = "select $field from $table where $wherekey like :key";
		$stmt = $this->pdo->prepare($sql);
		$like = '%'."$whereval".'%';
		$stmt->bindParam(":key", $like, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt;
	}
	private function _insert( $table, $keys, $vals ){
		$key_strings = $this->arrToStr( $keys, "", ", " );
		$val_strings = $this->arrToStr( $keys, ":", ", " );
		$sql = "insert into $table ( $key_strings ) value ( $val_strings )";
		try {
			$stmt = $this->pdo->prepare($sql);
			for( $i=0; $i<count($keys); $i++ ){
				$stmt->bindParam(":".$keys[$i], $vals[$i], PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(Exception $e){ echo $e->getMessage(); }
	}
	
	private function arrToStr( $arr, $bindSymbol, $connectSymbol ){
		$str = "";
		for( $i=0; $i<count($arr); $i++ ){
			$str.= $bindSymbol.$arr[$i];
			if( $i<count($arr)-1 ) $str.= $connectSymbol;
		}
		return $str;
	}
	private function arrToParamStr( $arr, $bindSymbol, $connectSymbol ){
		$str = "";
		for( $i=0; $i<count($arr); $i++ ){
			$str.= $arr[$i].$bindSymbol.$arr[$i];
			if( $i<count($arr)-1 ) $str.= $connectSymbol;
		}
		return $str;
	}
	private function createWhereStrUnique( $keys, $vals, $uniques ){
		if( count($uniques)==0 ) return "";
		$where_str = " where ";
		for( $iu=0; $iu<count($uniques); $iu++ ){
			for( $ik=0; $ik<count($keys); $ik++ ){
				if($keys[$ik]==$uniques[$iu]){
					$where_str .= $keys[$ik]."='".$vals[$ik]."'";
					if($iu<count($uniques)-1) $where_str .= " AND ";
				}
			}
		}
		return $where_str;
	}
}
