<?php
require_once dirname(__FILE__)."/DAMS.DB_installer.class.php";

echo "--BEGIN--\n";
$installer = new DBInstaller();
$installer->install();
$installer->import("./import/marushige_paper.csv");
$installer->import("./import/BN_paper.csv");
echo "--END--\n";