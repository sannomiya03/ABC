<?php
require_once dirname(__FILE__)."/installer.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";

Console::logln("--BEGIN--");
Installer::install();

// foreach($files as $file){
// 	$ext = File::getExt($file);
// 	echo "	[$file] ext: $ext\n";
// }
// $installer = new DBInstaller();
// $installer->install();
Console::logln("--BEGIN--");