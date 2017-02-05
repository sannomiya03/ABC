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

	/* ---------------------------------------------
	 * QUERY
	 * --------------------------------------------- */
	public function getID($tableName, $where){
		$table = $this->collection->getTable($tableName);
		return $this->getValue($tableName, $table->uid, $where);
	}

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
	
	/* ---------------------------------------------
	 * UPDATE
	 * --------------------------------------------- */
	public function updateInfo($table, $uid, $keys, $vals){
		$uidName = "";
		$this->update($table, $keys, $vals, array($uidName), array($uid));
	}
}