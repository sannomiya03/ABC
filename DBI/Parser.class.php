<?php
	class Parser{
		public static function uniquesToWhere($uniques, $keys, $values){
			if(count($uniques)==0) return "";
			$where = "where ";
			foreach($uniques as $unique){
				foreach($keys as $index=>$key){
					if($key == $unique){
						$value = $values[$index];
						$where .= "$key='$value' AND ";
					}
				}
			}
			return rtrim($where, " AND ");
			return $where;
		}
		
		public static function keysToParamStr($keys, $bindingSymbol, $connectionSymbol){
			$str = "";
			foreach($keys as $index=>$key){
				$str .= $key.$bindingSymbol;
				if($index<count($keys)-1) $str.=$connectionSymbol;
			}
			return $str;
		}

		public static function keysToBindParamStr($keys, $bindingSymbol, $connectionSymbol){
			$str = "";
			foreach($keys as $index=>$key){
				$str .= $bindingSymbol.$key;
				if($index<count($keys)-1) $str.=$connectionSymbol;
			}
			return $str;
		}
	}