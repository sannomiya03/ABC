<?php
require_once dirname(__FILE__)."/api.class.php";
require_once dirname(__FILE__)."/../ABC/modules/Console.class.php";

$tableName = "papers";
$uid = "3";

$api = new API();
$api->delete($tableName, $uid);