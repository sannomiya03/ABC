<?php
class Console{
	public static function log($var, $color="LightGray", $simple=false){
		switch(gettype($var)){
			case "string":
			case "integer":
				echo "\033[".self::getColorCode($color)."m".$var."\033[0m";
				break;
			case "boolean":
				if($var) echo "\033[".self::getColorCode("Green")."m"."true"."\033[0m";
				else echo "\033[".self::getColorCode("LightRed")."m"."false"."\033[0m";
				break;
			case "array":
			case "object":
				if(!$simple){
					$index = 0;
					foreach($var as $key=>$val){
						self::log("	[".$key."]","Cyan");
						if($index<count($var)-1) self::logln(" ".$val,$color);
						else self::log(" ".$val,$color);
						$index++;
					}
				}else{
					if(count($var)>0){
						foreach($var as $key=>$val){
							self::log("	[".$key."]","Blue");
							self::log(" ".$val,$color);
							return;
						}
					}
				}
				break;
			case "NULL":
				echo self::log("null","Purple");
				break;
			default:
				echo self::log("unknown type","Red");
				break;
		}
	}

	public static function logln($var, $color="LightGray",$simple=false){
		echo self::log($var, $color, $simple)."\n";
	}

	private function getColorCode($color){
		switch($color){
			case "Black": return "030";
			case "Blue": return "0;34";
			case "Green": return "0;32";
			case "Cyan": return "0;36";
			case "Red": return "0;31"; 
			case "Purple": return "0;35"; 
			case "Brown": return "0;33"; 
			case "LightGray": return "0;37"; 
			case "DarkGray": return "1;30";
			case "LightBlue": return "1;34";
			case "LightGreen": return "1;32";
			case "LightCyan": return "1;36";
			case "LightRed": return "1;31";
			case "LightPurple": return "1;35";
			case "Yellow": return "1;33";
			case "White": return "1;37";
			default: return "1;30";
		}
	}
}
?>