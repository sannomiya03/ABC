<?php
require_once dirname(__FILE__)."/api.class.php";
require_once dirname(__FILE__)."/../ABC/modules/Console.class.php";

// $file = dirname(__FILE__)."/../uploaded/original/26909765.jpg";
$file = $_FILES['file']['tmp_name'];

$api = new API();
$api->upload($file, $_FILES['file']['name']);