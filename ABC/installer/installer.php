<?php
require_once dirname(__FILE__)."/../modules/installer.class.php";
require_once dirname(__FILE__)."/../modules/Console.class.php";

Console::logln("--BEGIN--");
Installer::install();
Console::logln("--BEGIN--");