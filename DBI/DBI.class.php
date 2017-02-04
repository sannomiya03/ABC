<?php
require_once dirname(__FILE__)."/Core.class.php";
require_once dirname(__FILE__)."/modules/FileUploader.class.php";
require_once dirname(__FILE__)."/modules/Console.class.php";


class DBI extends DBICore{
	public $uploader;
	
	public function __construct(){
		$this->uploader = new FileUploader();
		parent::__construct();
	}

	public function append($table, $uid, $keys, $vals, $uniques==null){
		array_push( $keys, "created" );
		array_push( $vals, null );
		return $this->addRecord($table, $uid, $keys, $vals, $uniques);
	}

	public function appendProperty($table, $property, $taxonomy){
		if($property==null || $property==""){
			Console::logln("\$PROPETY is EMPTY!","Red");
			return;
		}if($taxonomy==null || $taxonomy==""){
			Console::logln("\$TAXONOMY is EMPTY!","Red");
			return;
		}
		$taxonomy_id = $this->getValue( "taxonomies", "taxonomy_id", "where taxonomy='$taxonomy'" );
		if( $taxonomy_id == null ){
			Console::log("ADD Taxonomy [$taxonomy]...","Green");
			$taxonomy_id = $this->appendTaxonomy($taxonomy);
			Console::logln("ID: $taxonomy_id !","Green");
		}
		$tableName = $table."_propeties";
		$uid = $table."_property_id";
		$keys = array( "property", "taxonomy_id" );
		$vals = array( $property, $taxonomy_id );
		return $this->append($tableName, $uid, $keys, $vals);
	}
	
	public function appendTaxonomy($taxonomy, $parent=""){
		if($taxonomy==null || $taxonomy==""){
			Console::logln("\$TAXONOMY is EMPTY!","Red");
			return;
		}
		$keys = array("taxonomy");
		$vals = array($taxonomy);
		if($parent != ""){
			$parent_id = $this->getValue("taxonomies", "taxonomy_id", "where taxonomy='$parent'");
			if($parent_id==null){
				Console::log("ADD Parent taxonomy [$parent]...","Green");
				$parent_id = $this->appendTaxonomy($parent);
				Console::logln("ID: $parent_id !","Green");
			}
			array_push($keys, "parent_id");
			array_push($vals, $parent_id);
		}
		return $this->append("taxonomies", "taxonomy_id", $keys, $vals);
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