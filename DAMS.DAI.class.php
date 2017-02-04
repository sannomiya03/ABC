<?php
require_once dirname(__FILE__)."/DAMS.DAI_core.class.php";
require_once dirname(__FILE__)."/DAMS.FileUploader.class.php";

class DAI extends DAI_core{
	public $uploader;
	public static $DOC_TABLE, $INS_TABLE, $ATT_TABLE;
	public static $DOC_PK, $INS_PK, $ATT_PK;
	public static $DOC_FIELDS, $INS_FIELDS, $ATT_FIELDS;
	
	public function __construct(){
		global $DOCUMENT_TABLE, $INSTANCE_TABLE, $ATTACHMENT_TABLE;
		global $DOCUMENT_PRIMARY_KEY, $INSTANCE_PRIMARY_KEY, $ATTACHMENT_PRIMARY_KEY;
		global $DOCUMENT_TABLE_FIELDS, $INSTANCE_TABLE_FIELDS, $ATTACHMENT_TABLE_FIELDS;
		self::$DOC_TABLE = $DOCUMENT_TABLE;
		self::$INS_TABLE = $INSTANCE_TABLE;
		self::$ATT_TABLE = $ATTACHMENT_TABLE;
		self::$DOC_PK = $DOCUMENT_PRIMARY_KEY;
		self::$INS_PK = $INSTANCE_PRIMARY_KEY;
		self::$ATT_PK = $ATTACHMENT_PRIMARY_KEY;
		self::$DOC_FIELDS = $DOCUMENT_TABLE_FIELDS;
		self::$INS_FIELDS = $INSTANCE_TABLE_FIELDS;
		self::$ATT_FIELDS = $ATTACHMENT_TABLE_FIELDS;
		$this->uploader = new FileUploader();
		parent::__construct();
	}

	/* ---------------------------------------------
	 * GNERATE
	 * --------------------------------------------- */
	public function appendDocument( $params ){
		if( count($params)==0 ) return;
		$keys = $this->getKeyArr( self::$DOC_FIELDS, $params, self::$DOC_PK );
		$vals = $this->getValArr( self::$DOC_FIELDS, $params, self::$DOC_PK );
		array_push( $keys, "created" );
		array_push( $vals, null );
		$uniques = $this->getUniqueArr(self::$DOC_FIELDS);
		return $this->insert( self::$DOC_TABLE, self::$DOC_PK, $keys, $vals, $uniques );
	}
	public function appendInstance( $document_id, $params ){
		if( $document_id==null || $document_id=="" || count($params)==0 ) return;
		$keys = $this->getKeyArr( self::$INS_FIELDS, $params, self::$INS_PK );
		$vals = $this->getValArr( self::$INS_FIELDS, $params, self::$INS_PK );
		array_push( $keys, "created" );
		array_push( $vals, null );
		array_push( $keys, self::$DOC_PK );
		array_push( $vals, $document_id );
		$uniques = $this->getUniqueArr(self::$INS_FIELDS);
		return $this->insert( self::$INS_TABLE, self::$INS_PK, $keys, $vals, $uniques );
	}
	public function appendAttachment( $instance_id, $format, $params, $class="", $attribute="" ){
		if( $instance_id==null || $instance_id=="" || $format==null || $format=="" || count($params)==0 ) return;
		$keys = $this->getKeyArr( self::$ATT_FIELDS, $params, self::$ATT_PK );
		$vals = $this->getValArr( self::$ATT_FIELDS, $params, self::$ATT_PK );
		array_push( $keys, "created" );
		array_push( $vals, null );
		array_push( $keys, self::$INS_PK );
		array_push( $vals, $instance_id );
		$format_id = $this->appendFormat($format);
		array_push( $keys, "format_id" );
		array_push( $vals, $format_id );
		if( $class!="" ){
			$class_id =  $this->appendClass($class);
			array_push( $keys, "class_id" );
			array_push( $vals, $class_id );
		}
		if( $attribute!="" ){
			$attribute_id =  $this->appendAttribute($attribute);
			array_push( $keys, "attribute_id" );
			array_push( $vals, $attribute_id );
		}
		$uniques = $this->getUniqueArr(self::$ATT_FIELDS);
		return $this->insert( self::$ATT_TABLE, self::$ATT_PK, $keys, $vals, $uniques );
	}
	public function appendProperty( $property, $taxonomy ){
		if( $property==null || $property=="" || $taxonomy==null || $taxonomy=="" ) return;
		$taxonomy_id = $this->getValue( "taxonomies", "taxonomy_id", "where taxonomy='$taxonomy'" );
		if( $taxonomy_id == null ) $taxonomy_id = $this->appendTaxonomy( $taxonomy );
		$keys = array( "property", "taxonomy_id" );
		$vals = array( $property, $taxonomy_id );
		return $this->insert( "properties", "property_id", $keys, $vals );
	}
	public function appendTaxonomy( $taxonomy, $parent="" ){
		if( $taxonomy==null || $taxonomy=="" ) return;
		$keys = array( "taxonomy" );
		$vals = array( $taxonomy );
		if( $parent != "" ){
			$parent_id = $this->getValue( "taxonomies", "taxonomy_id", "where taxonomy='$parent'" );
			array_push($keys,"parent_id");
			array_push($vals,$parent_id);
		}
		return $this->insert( "taxonomies", "taxonomy_id", $keys, $vals );
	}
	public function appendFormat( $format ){
		if( $format==null || $format=="" ) return;
		return $this->insert( "formats", "format_id", array("format"), array($format) );
	}
	public function appendClass( $class ){
		if( $class==null || $class=="" ) return;
		return $this->insert( "classes", "class_id", array("class"), array($class) );
	}
	public function appendAttribute( $attribute ){
		if( $attribute==null || $attribute=="" ) return;
		return $this->insert( "attributes", "attribute_id", array("attribute"), array($attribute) );
	}
	public function connectDocumentProperty( $document_id, $property_id ){
		if( $document_id==null || $document_id=="" || $property_id==null || $property_id=="" ) return;
		$keys = array( self::$DOC_PK, "property_id" );
		$vals = array( $document_id, $property_id );
		return $this->insert( "document_properties", "document_property_id", $keys, $vals );
	}
	public function connectInstanceProperty( $instance_id, $property_id ){
		if( $instance_id==null || $instance_id=="" || $property_id==null || $property_id=="" ) return;
		$keys = array( self::$INS_PK, "property_id" );
		$vals = array( $instance_id, $property_id );
		return $this->insert( "instance_properties", "instance_property_id", $keys, $vals );
	}
	public function uploadAttachmentImage( $instance_id, $file ){
		$uploadImage = $this->uploader->uploadImage( $file );
		$this->updateInstanceInfo( $instance_id, array("thumbnail"), array($uploadImage) );
		$params = array( "name"=>$uploadImage, "file_path"=>$uploadImage );
		$attachmentID = $this->appendAttachment( $instance_id, "image", $params );
		return array( "name"=>$uploadImage, "id"=>$attachmentID );
	}
	public function uploadAttachmentImageByInstller( $instance_id, $file ){
		$uploadImage = $this->uploader->uploadImageOnlyOneItem( $file );
		$this->updateInstanceInfo( $instance_id, array("thumbnail"), array($uploadImage) );
		$params = array( "name"=>$uploadImage, "file_path"=>$uploadImage );
		$attachmentID = $this->appendAttachment( $instance_id, "image", $params );
		return array( "name"=>$uploadImage, "id"=>$attachmentID );
	}

	/* ---------------------------------------------
	 * QUERY
	 * --------------------------------------------- */
	public function getDocuments(){
		$table = self::$DOC_TABLE;
		$select = "*";
		return $this->getFields( $table, $select );
	}
	public function getInstances(){
		$table = self::$INS_TABLE;
		$select = "*";
		return $this->getFields( $table, $select );
	}
	function getDocumentDetail( $instance_id ){
		$instance = $this->getField( self::$INS_TABLE, "*", "WHERE $insPK = '$this->instance_id'");
		$document = $this->getField( self::$DOC_TABLE, "*", "WHERE $docPK = '".$this->instance[$docPK]."'");
		$attachments = getFields( self::$ATT_TABLE, "*", "WHERE $insPK = '$this->instance_id'" );
		$result = array(
			"document_id" => $document[$docPK],
			"instance_id" => $instance[$insPK],
		);
		return $result;
	}
	function getInstanceIDByName( $docName, $insName ){
		$table = self::$DOC_TABLE." AS doc LEFT OUTER JOIN ".self::$INS_TABLE." AS ins ON (doc.".self::$DOC_PK."=ins.".self::$DOC_PK.")";
		$where = "where doc.name = '".$docName."'";
		if( $insName != "" ) $where .= " AND ins.name = '".$insName."'";
		return $this->getField( $table, "ins.".self::$INS_PK, $where )[self::$INS_PK];
	}
	function getProperties(){
		return $this->getFields( "properties LEFT OUTER JOIN taxonomies ON ( properties.taxonomy_id = taxonomies.taxonomy_id ) ORDER BY property_id", "*" );
	}
	function getTaxonomies(){
		return $this->getFields( "taxonomies ORDER BY taxonomy_id", "*" );
	}

	/* ---------------------------------------------
	 * DROP
	 * --------------------------------------------- */
	public function dropDocument( $document_id ){
		$insIDs = $this->getValues( self::$INS_TABLE, self::$INS_PK, "where ".self::$DOC_PK."='$document_id'");
		foreach( $insIDs as $insID ) $this->dropInstance( $insID );
		$this->delete( self::$DOC_TABLE, array(self::$DOC_PK), array($document_id) );
	}
	public function dropInstance( $instance_id ){
		$insIDs = $this->getValues( self::$ATT_TABLE, self::$ATT_PK, "where ".self::$INS_PK."='$instance_id'");
		foreach( $insIDs as $insID ) $this->dropAttachment( $insID );
		$this->delete( self::$INS_TABLE, array(self::$INS_PK), array($instance_id) );
	}
	public function dropAttachment( $attachment_id ){
		$this->delete( self::$ATT_TABLE, array(self::$ATT_PK), array($attachment_id) );
	}
	public function dropProperty( $property ){
		$this->delete( "propertys", array("property"), array($property) );
	}
	public function dropTaxonomy( $taxonomy ){
		$taxonomy_id = $this->getValue( "taxonomies", "taxonomy_id", "where taxonomy='$taxonomy'");
		$children = $this->getValues( "taxonomies", "taxonomy_id", "where parent_id='$taxonomy_id'" );
		$this->delete( "taxonomies", array("taxonomy"), array($taxonomy) );
		foreach( $children as $child ){
			$this->delete( "taxonomies", array("taxonomy_id"), array($child) );
		}
	}
	public function dropFormat( $format ){
		$this->delete( "formats", array("format"), array($format) );
	}
	public function dropClass( $class ){
		$this->delete( "classes", array("class"), array($class) );
	}
	public function dropAttribute( $attribute ){
		$this->delete( "attributes", array("attribute"), array($attribute) );
	}
	public function disconnectDocumentProperty( $document_id, $property_id ){
		$where_keys = array( self::$DOC_PK, "property_id" );
		$where_vals = array( $document_id, $property_id );
		$this->delete( "document_properties", $where_keys, $where_vals );
	}
	public function disconnectInstanceProperty( $instance_id, $property_id ){
		$where_keys = array( self::$INS_PK, "property_id" );
		$where_vals = array( $instance_id, $property_id );
		$this->delete( "instance_properties", $where_keys, $where_vals );
	}
	
	/* ---------------------------------------------
	 * UPDATE
	 * --------------------------------------------- */
	public function updateDocumentInfo( $document_id, $keys, $vals ){
		$this->update( self::$DOC_TABLE, $keys, $vals, array(self::$DOC_PK), array($document_id) );
	}
	public function updateInstanceInfo( $instance_id, $keys, $vals ){
		$this->update( self::$INS_TABLE, $keys, $vals, array(self::$INS_PK), array($instance_id) );
	}
	public function updateAttachmentInfo( $attachment_id, $keys, $vals ){
		$this->update( self::$ATT_PK, $keys, $vals, array(self::$ATT_PK), array($attachment_id) );
	}
	public function updateProperty( $property_id, $newProperty ){
		$this->update( "propertys", array("property"), array($newProperty), array("property_id"), array($property_id) );
	}

	/* ---------------------------------------------
	 * PRIVATE
	 * --------------------------------------------- */
	private function getKeyArr( $fields, $params, $primary_key){
		$keys = array();
		foreach( $params as $pKey=>$pVal ){
			foreach( $fields as $fKey=>$fVal ){
				if( $pKey == $fKey && $pKey != $primary_key ) array_push( $keys, $pKey );
			}
		}
		return $keys;
	}
	private function getValArr( $fields, $params, $primary_key){
		$vals = array();
		foreach( $params as $pKey=>$pVal ){
			foreach( $fields as $fKey=>$fVal ){
				if( $pKey == $fKey && $pKey != $primary_key ) array_push( $vals, $pVal );
			}
		}
		return $vals;
	}
	private function getUniqueArr( $fields ){
		$uniques = array();
		foreach( $fields as $field ){
			if( strpos( $field["option"], "unique(") !== false ){
				$unique_str = $field["option"];
				$unique_str = substr($unique_str,strpos($unique_str,"(")+1);
				$unique_str = substr($unique_str,0,strlen($unique_str)-1);
				foreach( explode(",", $unique_str) as $str ){
					array_push($uniques,str_replace(" ", "", $str));
				}
			}
		}
		if( count($uniques) == 0 ){
			foreach( $fields as $field ){
				if( strpos($field["option"],"unique") !== false ){
					array_push( $uniques, $field["label"] );
				}
			}
		}
		return $uniques;
	}
}