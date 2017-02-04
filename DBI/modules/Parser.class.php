<?php
	class Parser{
		public static function uniquesToStr($uniques, $keys, $values){
			if(count($uniques)==0) return "";
			$str = " where ";
			foreach($uniques as $unique){
				foreach($keys as $index=>$key){
					if($key == $unique){
						$value = $values[$index];
						$str .= "$key='$value'";
						if($index<count($uniques)-1) $str .= " AND ";
					}
				}
			}
			return $str;
		}
		
		public static function arrToParamStr($array, $bindingSymbol, $connectionSymbol){
			$str = "";
			foreach($array as $index=>$value){
				$str .= $value.$bindingSymbol.$value;
				if($index<count($array)-1) $str.=$connectionSymbol;
			}
			return $str;
		}
	}