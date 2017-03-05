<?php
class File{
	public static function getExt($fileName){
		$info = pathinfo($fileName);
		if(isset($info["extension"])) return $info["extension"];
		else return "";
	}
	public static function getDirName($fileName){
		return $info = pathinfo($fileName)["dirname"];
	}
	public static function getFileName($fileName){
		return $info = pathinfo($fileName)["filename"];
	}
}