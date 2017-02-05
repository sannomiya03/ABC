<?php
require_once dirname(__FILE__)."/Core.class.php";
require_once dirname(__FILE__)."/../modules/TableCollection.class.php";
require_once dirname(__FILE__)."/../modules/FileUploader.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";
require_once dirname(__FILE__)."/../modules/ManifestMaker.class.php";

class DBI extends DBICore{
	public $uploader, $collection;
	
	public function __construct(){
		parent::__construct();
		$this->collection = new TableCollection();
		$this->uploader = new FileUploader();
	}

	public function append($tableName, $keys, $vals){
		$table = $this->collection->getTable($tableName);
		array_push( $keys, "created" );
		array_push( $vals, null );
		return $this->addRecord($tableName, $table->uid, $keys, $vals, $table->uniques);
	}

	public function appendProperty($tableName, $fieldID, $property, $taxonomy){
		if($property==null || $property==""){ Console::logln("[ATTENTION!] \$PROPETY is EMPTY!","Red"); return; }
		if($taxonomy==null || $taxonomy==""){ Console::logln("[ATTENTION!] \$TAXONOMY is EMPTY!","Red"); return; }
		$table = $this->collection->getTable($tableName);
		$propTable = $this->collection->getPropTable($tableName);
		$property_id = $this->addProperty($property, $taxonomy);
		$keys = array($table->uid, "property_id");
		$vals = array($fieldID, $property_id);
		return $this->append($propTable->name, $keys, $vals);
	}

	public function addProperty($property, $taxonomy){
		if($property==null || $property==""){ Console::logln("\$PROPETY is EMPTY!","Red"); return; }
		if($taxonomy==null || $taxonomy==""){ Console::logln("\$TAXONOMY is EMPTY!","Red"); return; }
		$taxonomy_id = $this->addTaxonomy($taxonomy);
		$property_id = $this->getID("properties", "where property='$property' AND taxonomy_id='$taxonomy_id'");
		if($property_id != "") return $property_id;
		$keys = array( "property", "taxonomy_id" );
		$vals = array( $property, $taxonomy_id );
		return $this->append("properties", $keys, $vals);
	}
	
	public function addTaxonomy($taxonomy, $parent=""){
		if($taxonomy==null || $taxonomy==""){ Console::logln("\$TAXONOMY is EMPTY!","Red"); return; }
		$taxonomy_id = $this->getID("taxonomies", "where taxonomy='$taxonomy'");
		if($taxonomy_id != "") return $taxonomy_id;
		$keys = array("taxonomy");
		$vals = array($taxonomy);
		if($parent != ""){
			$parent_id = $this->getID("taxonomies", "where taxonomy='$parent'");
			if($parent_id==null){
				Console::log("ADD Parent taxonomy [$parent]...","Green");
				$parent_id = $this->addTaxonomy($parent);
				Console::logln("ID: $parent_id !","Green");
			}
			array_push($keys, "parent_id");
			array_push($vals, $parent_id);
		}
		$uniques = $keys;
		return $this->append("taxonomies", $keys, $vals);
	}

	// public function uploadImage($instance_id, $file){
	// 	$uploadImage = $this->uploader->uploadImage($file);
	// 	$this->updateInstanceInfo( $instance_id, array("thumbnail"), array($uploadImage) );
	// 	$params = array( "name"=>$uploadImage, "file_path"=>$uploadImage );
	// 	$attachmentID = $this->appendAttachment( $instance_id, "image", $params );
	// 	return array( "name"=>$uploadImage, "id"=>$attachmentID );
	// }
	// public function uploadAttachmentImageByInstller( $instance_id, $file ){
	// 	$uploadImage = $this->uploader->uploadImageOnlyOneItem( $file );
	// 	$this->updateInstanceInfo( $instance_id, array("thumbnail"), array($uploadImage) );
	// 	$params = array( "name"=>$uploadImage, "file_path"=>$uploadImage );
	// 	$attachmentID = $this->appendAttachment( $instance_id, "image", $params );
	// 	return array( "name"=>$uploadImage, "id"=>$attachmentID );
	// }

	/* ---------------------------------------------
	 * QUERY
	 * --------------------------------------------- */
	public function getID($tableName, $where){
		$table = $this->collection->getTable($tableName);
		return $this->getValue($tableName, $table->uid, $where);
	}

	public function get(){
		// $table = self::$DOC_TABLE;
		// $select = "*";
		// return $this->getRecords( $table, $select );
	}

	// function getDocumentDetail( $instance_id ){
	// 	$instance = $this->getField( self::$INS_TABLE, "*", "WHERE $insPK = '$this->instance_id'");
	// 	$document = $this->getField( self::$DOC_TABLE, "*", "WHERE $docPK = '".$this->instance[$docPK]."'");
	// 	$attachments = getRecords( self::$ATT_TABLE, "*", "WHERE $insPK = '$this->instance_id'" );
	// 	$result = array(
	// 		"document_id" => $document[$docPK],
	// 		"instance_id" => $instance[$insPK],
	// 	);
	// 	return $result;
	// }
	// function getInstanceIDByName( $docName, $insName ){
	// 	$table = self::$DOC_TABLE." AS doc LEFT OUTER JOIN ".self::$INS_TABLE." AS ins ON (doc.".self::$DOC_PK."=ins.".self::$DOC_PK.")";
	// 	$where = "where doc.name = '".$docName."'";
	// 	if( $insName != "" ) $where .= " AND ins.name = '".$insName."'";
	// 	return $this->getField( $table, "ins.".self::$INS_PK, $where )[self::$INS_PK];
	// }
	function getProperties(){
		return $this->getRecords( "properties LEFT OUTER JOIN taxonomies ON ( properties.taxonomy_id = taxonomies.taxonomy_id ) ORDER BY property_id", "*" );
	}
	function getTaxonomies(){
		return $this->getRecords( "taxonomies ORDER BY taxonomy_id", "*" );
	}

	/* ---------------------------------------------
	 * DROP
	 * --------------------------------------------- */
	public function drop($table, $uid){
		$uidName = "";
		// $id = $this->getValues($table, $id, "where $uidName='$uid'");
		// foreach( $insIDs as $insID ) $this->dropInstance( $insID );
		$this->delete($table, array($uidName), array($uid));
	}
	
	// public function dropProperty($table, $uid, $property_id){
	// 	$where_keys = array(self::$DOC_PK, "property_id" );
	// 	$where_vals = array($document_id, $property_id );
	// 	$this->delete( "document_properties", $where_keys, $where_vals );
	// }
	
	/* ---------------------------------------------
	 * UPDATE
	 * --------------------------------------------- */
	public function updateInfo($table, $uid, $keys, $vals){
		$uidName = "";
		$this->update($table, $keys, $vals, array($uidName), array($uid));
	}
}