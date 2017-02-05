<?php
require_once dirname(__FILE__)."/api.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";

$option = new stdClass;
$option->limit = 1;
$option->page = 1;
$option->table = "papers";
$option->order = "paper_id";
$option->include = array("paper_groups", "paper_groups_properties");
$option->fields = array();
$option->filters = array("paper_id>10", "paper_id<20");

$api = new API();
$records = $api->get($option);

Console::logln("[SIZE] ".count($records),"Green");
if(count($records)>0) Console::logln($records[0]);

// $records = $dbi->getRecords($table, $select, $where);