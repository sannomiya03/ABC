<?php
require_once dirname(__FILE__)."/api.class.php";
require_once dirname(__FILE__)."/../ABC/modules/Console.class.php";

$tableName = "papers";
$uid = "";
$keys = array("name");
$values = array("uasdias");

$api = new API();
if($uid!="") $api->put($tableName, $uid, $keys, $values);
else{
	$id = $api->append($tableName, $keys, $values);
	echo $id;
}