<?php
require_once dirname(__FILE__)."/DAMS.DAI.class.php";

class DAI_advanced extends DAI{
	public function __construct(){
		parent::__construct();
	}
	/* ---------------------------------------------
	 * GETTER
	 * --------------------------------------------- */
	public function getDocuments( $properties=null ){
		if( $properties == null || count($properties) == 0 ) return parent::getDocuments();
		$select = "doc.".self::$DOC_PK;
		return $this->getDocumentsByProperties( $properties, $select );
	}
	public function getInstances( $properties=null ){
		if( $properties == null || count($properties) == 0 ) return parent::getInstances();
		$select =
			"doc.".self::$DOC_PK.", ins.".self::$INS_PK.",".
			"CONCAT(doc.name, ' ', ins.name ) as name,".
			"model_number, thickness, width, height, price, thumbnail";
		return $this->getInstancesByProperties( $properties, $select );
	}

	/* ---------------------------------------------
	 * PRIVATE
	 * --------------------------------------------- */
	private function getDocumentsByProperty( $taxonomy, $property, $select ){
		$property_id = $this->getPropertyID( $taxonomy, $property );
		$table = self::$DOC_TABLE." LEFT OUTER JOIN document_properties ON (".self::$DOC_TABLE.".".self::$DOC_PK."=document_properties.".self::$DOC_PK.")";
		$where = "where property_id = '$property_id'"; 
		return $this->getFields( $table, $select, $where );
	}
	private function getInstancesByProperty( $taxonomy, $property, $select ){
		$property_id = $this->getPropertyID( $taxonomy, $property );
		$table = self::$DOC_TABLE." AS doc LEFT OUTER JOIN document_properties AS dp ON (doc.".self::$DOC_PK."=dp.".self::$DOC_PK.")";
		$where = "where doc.property_id = '$property_id'";
		return $this->getFields( $table, $select, $where );
	}
	private function getDocumentsByProperties( $properties, $select ){
		$table = self::$DOC_TABLE." AS doc LEFT OUTER JOIN document_properties AS dp ON (doc.".self::$DOC_PK."=dp.".self::$DOC_PK.")";
		$where = "where ";
		$index = 0;
		foreach( $properties as $tax=>$prop ){
			$propID = $this->getPropertyID( $tax, $prop );
			$where .= "dp.property_id = '$propID' ";
			if( $index < count($properties)-1 ) $where.="AND ";
			$index++;
		}
		return $this->getFields( $table, $select, $where );
	}
	private function getInstancesByProperties( $properties, $select ){
		$table =  self::$DOC_TABLE." AS doc ";
		$table .= "LEFT OUTER JOIN ".self::$INS_TABLE." AS ins ON (doc.".self::$DOC_PK."=ins.".self::$DOC_PK.")";
		$table .= "LEFT OUTER JOIN document_properties AS dp ON (doc.".self::$DOC_PK."=dp.".self::$DOC_PK.")";
		$table .= "LEFT OUTER JOIN instance_properties AS ip ON (ins.".self::$INS_PK."=ip.".self::$INS_PK.")";
		$where = "where ";
		$index = 0;
		foreach( $properties as $tax=>$prop ){
			$propID = $this->getPropertyID( $tax, $prop );
			$where .= "( dp.property_id = '$propID' OR ip.property_id = '$propID') ";
			if( $index < count($properties)-1 ) $where.="AND ";
			$index++;
		}
		return $this->getFields( $table, $select, $where );
	}
	private function getPropertyID( $taxonomy, $property ){
		$taxonomy_id = $this->getValue("taxonomies","taxonomy_id","where taxonomy='$taxonomy'");
		$property_id = $this->getValue("properties","property_id","where taxonomy_id='$taxonomy_id' AND property='$property'");
		return $property_id;
	}
}