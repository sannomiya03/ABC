<?php
class OptionParser{
	public static function order($orderField){
		return "ORDER BY $orderField";
	}
	public static function table($table, $include){
		$str = "$table ";
		foreach($include as $subTable){
			$str .= " LEFT OUTER JOIN $subTable ON ( properties.taxonomy_id = taxonomies.taxonomy_id )"
		}
	}
}