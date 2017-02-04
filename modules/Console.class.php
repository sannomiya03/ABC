<?php
// PLESE USE LIKE THIS
// Console::logln("hello!");
// Console::logln(true);
// Console::logln(false);
// Console::logln(array("apple","banana"),"Cyan");
// Console::logln((object)array("apple"=>100,"banana"=>200),"Blue",true);
// Console::logln(null);

class Console{
	public static $SHOW_LOG = true;
	
	public static function log($var, $color="LightGray", $layer=0, $tab=false){
		if(!self::$SHOW_LOG) return;
		switch(gettype($var)){
			case "string":
			case "integer":
				$space = "";
				if($layer>0 && $tab){
					for($i=0; $i<$layer-1; $i++){
						$space.="    ";
					}
				}
				echo "\033[".self::getColorCode($color)."m".$space.$var."\033[0m";
				break;
			case "boolean":
				if($var) echo "\033[".self::getColorCode("Green")."m"."true"."\033[0m";
				else echo "\033[".self::getColorCode("LightRed")."m"."false"."\033[0m";
				break;
			case "array":
			case "object":
				$index = 0;
				$layer++;
				foreach($var as $key=>$val){
					self::log("[$key]", "Cyan", $layer, true);
					self::log(" ", $color);
					if(gettype($val)=="array"||gettype($val)=="object") echo "\n";
					self::log($val, $color, $layer);
					if((gettype($val)=="array"||gettype($val)=="object") && count((array)$val)==0) self::log("[empty]", $color, $layer+1, true);
					if($index<count((array)$var)-1) echo "\n";
					$index++;
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

	public static function logln($var, $color="LightGray", $layer=0, $tab=false){
		if(!self::$SHOW_LOG) return;
		echo self::log($var, $color, $layer, $tab)."\n";
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