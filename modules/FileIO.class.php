<?php
class FileIO{
	public static function loadDir($path){
		$files = scanDir($path);
		$arr = array();
		foreach($files as $file){
			if($file[0]==".") continue;
			array_push($arr,$file);
		}
		return $arr;
	}

	public static function loadJSON($path){
		if(!file_exists($path)) return new stdClass;
		$json = file_get_contents($path);
		$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		return json_decode($json,true);
	}

	public static function loadCSV($path){
		if(!file_exists($path)) return array();
		$file = new SplFileObject($path);
		$file->setFlags(SplFileObject::READ_CSV);
		$lines = array();
		foreach($file as $line){
			if(!is_null($line[0])){
				array_push($lines, $line);
			}
		}
		return $lines;
	}

	public static function save($array,$name){
		$json = fopen($name, 'w+b');
		fwrite($json, json_encode($array, JSON_UNESCAPED_UNICODE));
		fclose($json);
	}
}
?>