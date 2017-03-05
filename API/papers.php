<?php
require_once dirname(__FILE__)."/api.class.php";
require_once dirname(__FILE__)."/../ABC/modules/Console.class.php";

$option = new stdClass;
$option->limit = (isset($_GET["limit"]))? $_GET["limit"]: 30;
$option->page = (isset($_GET["page"]))? $_GET["page"]: 0;
$option->table = (isset($_GET["table"]))? $_GET["table"]: "papers";
$option->order = (isset($_GET["order"]))? $_GET["order"]: array();
$option->include = (isset($_GET["include"]))? $_GET["include"]: array();
$option->fields = (isset($_GET["fields"]))? $_GET["fields"]: array();
$option->filters = (isset($_GET["filters"]))? $_GET["filters"]: array();

// SET OPTION LIKE THIS
// $option->limit = 30;
// $option->table = "papers";
$option->order = "paper_id";
$option->page = 0;
$option->include = array("paper_groups", "paper_groups_properties");
// $option->filters = array("paper_id>10", "paper_id<20");

$api = new API();
$records = $api->get($option);
Console::logln("[SIZE] ".count($records),"Green");
if(count($records)>0) Console::logln($records[0]);

// echo raw_json_encode($records);

function raw_json_encode($input) {
    return preg_replace_callback(
        '/\\\\u([0-9a-zA-Z]{4})/',
        function ($matches) {
            return mb_convert_encoding(pack('H*',$matches[1]),'UTF-8','UTF-16');
        },
        json_encode($input)
    );
}