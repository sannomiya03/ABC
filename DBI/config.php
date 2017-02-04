<?php
require dirname(__FILE__)."/modules/FileIO.class.php";

$setting = FileIO::loadJSON(dirname(__FILE__)."/../setting.json");

define( "HOST", $setting->host );
define( "DB_NAME", $setting->db_name );
define( "USER", $setting->user );
define( "PASS", $setting->pass );
define( "DBMS", $setting->dbms );

// define( "HOST", $config["host"] );
// define( "DB_NAME", $config["db_name"] );
// define( "USER", $config["user"] );
// define( "PASS", $config["pass"] );
// define( "DBMS", $config["dbms"] );

// $DOCUMENT_TABLE = $config["document_table"];
// $INSTANCE_TABLE = $config["instance_table"];
// $ATTACHMENT_TABLE = $config["attachment_table"];

// $DOCUMENT_PRIMARY_KEY = $config["document_primary_key"];
// $INSTANCE_PRIMARY_KEY = $config["instance_primary_key"];
// $ATTACHMENT_PRIMARY_KEY = $config["attachment_primary_key"];

// $DOCUMENT_TABLE_FIELDS = $config["document_table_fields"];
// $INSTANCE_TABLE_FIELDS = $config["instance_table_fields"];
// $ATTACHMENT_TABLE_FIELDS = $config["attachment_table_fields"];

// $ADDITIONAL_TABLES    = $config["additional_tables"];
// $DEFAULT_TAXONOMIES   = $config["default_taxonomies"];
// $DEFAULT_FORMATS      = $config["default_formats"];
// $DEFAULT_ATTRIBUTES   = $config["default_attributes"];

// array_unshift( $DOCUMENT_TABLE_FIELDS, array("label"=>$DOCUMENT_PRIMARY_KEY, "dataType"=>"int(255)", "option"=>"not null auto_increment primary key") );
// array_unshift( $INSTANCE_TABLE_FIELDS, array("label"=>$INSTANCE_PRIMARY_KEY, "dataType"=>"int(255)", "option"=>"not null auto_increment primary key") );
// array_unshift( $INSTANCE_TABLE_FIELDS, array("label"=>$DOCUMENT_PRIMARY_KEY, "dataType"=>"int(255)", "option"=>"not null") );
// array_unshift( $ATTACHMENT_TABLE_FIELDS, array("label"=>$ATTACHMENT_PRIMARY_KEY, "dataType"=>"int(255)", "option"=>"not null auto_increment primary key") );
// array_unshift( $ATTACHMENT_TABLE_FIELDS, array("label"=>$INSTANCE_PRIMARY_KEY, "dataType"=>"int(255)", "option"=>"not null") );

// array_push( $DOCUMENT_TABLE_FIELDS, array("label"=>"created", "dataType"=>"timestamp", "option"=>"not null DEFAULT '0000-00-00 00:00:00'"));
// array_push( $DOCUMENT_TABLE_FIELDS, array("label"=>"modified", "dataType"=>"timestamp", "option"=>"not null DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"));
// array_push( $INSTANCE_TABLE_FIELDS, array("label"=>"created", "dataType"=>"timestamp", "option"=>"not null DEFAULT '0000-00-00 00:00:00'"));
// array_push( $INSTANCE_TABLE_FIELDS, array("label"=>"modified", "dataType"=>"timestamp", "option"=>"not null DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"));
// array_push( $ATTACHMENT_TABLE_FIELDS, array("label"=>"created", "dataType"=>"timestamp", "option"=>"not null DEFAULT '0000-00-00 00:00:00'"));
// array_push( $ATTACHMENT_TABLE_FIELDS, array("label"=>"modified", "dataType"=>"timestamp", "option"=>"not null DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"));

// $DEFAULT_OTHER_TABLES = array();
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "formats",
// 	"sql"   => "format_id int(255) not null auto_increment primary key,
// 				format varchar(255) not null unique",
// 	"index" => "format_id"));
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "attributes",
// 	"sql"   => "attribute_id int(255) not null auto_increment primary key,
// 				attribute varchar(255) not null unique",
// 	"index" => "attribute_id"));
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "classes",
// 	"sql"   => "class_id int(255) not null auto_increment primary key,
// 				class varchar(255) not null unique",
// 	"index" => "class_id"));
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "taxonomies",
// 	"sql"   => "taxonomy_id int(255) not null auto_increment primary key,
// 				taxonomy varchar(255) not null unique,
// 				parent_id int(255)",
// 	"index" => "taxonomy_id"));
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "properties",
// 	"sql"   => "property_id int(255) not null auto_increment primary key,
// 				property varchar(255) not null,
// 				taxonomy_id int(255) not null,
// 				unique( property, taxonomy_id )",
// 	"index" => "property_id"));
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "document_properties",
// 	"sql"   => "document_property_id int(255) not null auto_increment primary key,
// 		$DOCUMENT_PRIMARY_KEY int(255) not null,
// 		property_id int(255) not null,
// 		unique( $DOCUMENT_PRIMARY_KEY, property_id )",
// 	"index" => $DOCUMENT_PRIMARY_KEY));
// array_push( $DEFAULT_OTHER_TABLES, array(
// 	"title" => "instance_properties",
// 	"sql"   => "instance_property_id int(255) not null auto_increment primary key,
// 		$INSTANCE_PRIMARY_KEY int(255) not null,
// 		property_id int(255) not null,
// 		unique( $INSTANCE_PRIMARY_KEY, property_id )",
// 	"index" => $INSTANCE_PRIMARY_KEY));