<?php
require_once dirname(__FILE__)."/DAMS.DAI_advanced.class.php";

class DBInstaller{
	public $dai, $tables, $defTabs, $addTabs;
	public $defTaxonomies, $defFormats, $defAttributes;

	public function __construct(){
		global $DEFAULT_OTHER_TABLES, $ADDITIONAL_TABLES, $DEFAULT_TAXONOMIES, $DEFAULT_FORMATS, $DEFAULT_ATTRIBUTES;
		$this->defTabs = $DEFAULT_OTHER_TABLES;
		$this->addTabs = $ADDITIONAL_TABLES;
		$this->defTaxonomies = $DEFAULT_TAXONOMIES;
		$this->defFormats = $DEFAULT_FORMATS;
		$this->defAttributes = $DEFAULT_ATTRIBUTES;
		$this->dai = new DAI_advanced();
		$tables = array();
		array_push( $tables, new Table(DAI::$DOC_TABLE, $this->arrToSQL(DAI::$DOC_FIELDS), DAI::$DOC_PK) );
		array_push( $tables, new Table(DAI::$INS_TABLE, $this->arrToSQL(DAI::$INS_FIELDS), DAI::$INS_PK) );
		array_push( $tables, new Table(DAI::$ATT_TABLE, $this->arrToSQL(DAI::$ATT_FIELDS), DAI::$ATT_PK) );
		foreach( $this->defTabs as $table ){
			array_push( $tables, new Table( $table["title"], $table["sql"], $table["index"]) );
		}
		foreach( $this->addTabs as $table ){
			array_push( $tables, new Table( $table["title"], $table["sql"], $table["index"]) );
		}
		$this->tables = $tables;
	}

	public function install(){
		foreach($this->tables as $table){
			echo "CREATE : ".$table->title."\n";
			$this->dai->createTable( $table->title, $table->sql );
			if( $table->index != "" ) $this->dai->alterIndex( $table->title, $table->index );
		}
		foreach( $this->defTaxonomies as $taxonomy ){
			$this->dai->appendTaxonomy( $taxonomy["label"], $taxonomy["parent"] );
		}
		foreach( $this->defFormats as $format ){
			$this->dai->appendFormat( $format["label"] );
		}
		foreach( $this->defAttributes as $attribute ){
			$this->dai->appendAttribute( $attribute["label"] );
		}
	}

	public function import( $csv_path ){
		echo "IMPORT : $csv_path\n";
		$file = new SplFileObject( $csv_path );
		$file->setFlags(SplFileObject::READ_CSV);
		$lines = array();
		foreach($file as $line){
			if(!is_null($line[0])){
				array_push($lines, $line);
			}
		}
		$records = array();
		for( $i=3; $i<count($lines); $i++ ){
			$record = array(
				"document"=>array(),
				"instance"=>array(),
				"document_property"=>array(),
				"instance_property"=>array(),
				"file"=>array()
			);
			for( $j=0; $j<count($lines[$i]); $j++ ){
				$vals = explode(";", $lines[$i][$j]);
				foreach( $vals as $val ){
					$record[$lines[1][$j]] = array_merge( $record[$lines[1][$j]], array( $lines[0][$j]=>$val) );
				}
			}
			array_push( $records, $record );
		}
		foreach( $records as $record ){
			$document_id = $this->dai->appendDocument( $record["document"] );
			$instance_id = $this->dai->appendInstance( $document_id, $record["instance"] );
			foreach( $record["document_property"] as $key=>$val ){
				$property_id = $this->dai->appendProperty( $val, $key );
				$this->dai->connectDocumentProperty( $document_id, $property_id );
			}
			foreach( $record["instance_property"] as $key=>$val ){
				$property_id = $this->dai->appendProperty( $val, $key );
				$this->dai->connectInstanceProperty( $instance_id, $property_id );
			}
			foreach( $record["file"] as $file ){
				$srcDir = dirname(__FILE__)."/../../srcImage";
				if( !file_exists($srcDir."/".$file) ){
					echo "not found : $file\n";
					continue;
				}
				$attachment = $this->dai->uploadAttachmentImageByInstller( $instance_id, $srcDir."/".$file );
				$this->dai->connectInstanceProperty( $instance_id, $attachment["id"] );
			}
		}
	}

	private function arrToSQL( $arr ){
		$sql_string = "";
		for( $i=0; $i<count($arr); $i++ ){
			$sql_string .= $arr[$i]["label"]." ".$arr[$i]["dataType"]." ".$arr[$i]["option"];
			if( $i<count($arr)-1 ){
				$sql_string .= ", ";
			}
		}
		return $sql_string;
	}
}

class Table{
	public $title, $sql, $index;
	public function __construct( $title, $sql, $index ){
		$this->title = $title;
		$this->sql = $sql;
		$this->index = $index;
	}
}