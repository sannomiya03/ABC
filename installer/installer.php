<?php
// require_once dirname(__FILE__)."/DAMS.DB_installer.class.php";
require_once "./modules/FileIO.class.php";
require_once "./modules/File.class.php";

echo "--BEGIN--\n";
$files = FileIO::loadDir("./");
foreach($files as $file){
	$ext = File::getExt($file);
	echo "	[$file] ext: $ext\n";
}
// $installer = new DBInstaller();
// $installer->install();
echo "--END--\n";