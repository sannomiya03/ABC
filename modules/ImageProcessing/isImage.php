<?php
	function imageCreateFromAny( $filePath ){
		$type = exif_imagetype( $filePath );
		$allowedTypes = array(
			1, // [] gif
			2, // [] jpg
			3, // [] png
			6  // [] bmp
		);
		if( !in_array($type, $allowedTypes) ){
			$list = explode(".", $filePath);
			$exif = $list[count($list)-1];
			switch($exif){
				case 'jpg': $im = imageCreateFromJpeg( $filePath ); break;
				case 'JPG': $im = imageCreateFromJpeg( $filePath ); break;
				case 'png': $im = imagecreatefrompng( $filePath ); break;
				case 'gif': $im = imageCreateFromGif( $filePath ); break;
				case 'bmp': $im = imageCreateFromBmp( $filePath ); break;
			}
			return $im;
		}
		switch( $type ){
			case 1 : $im = imageCreateFromGif( $filePath ); break;
			case 2 : $im = imageCreateFromJpeg( $filePath ); break;
			case 3 : $im = imagecreatefrompng( $filePath ); break;
			case 6 : $im = imageCreateFromBmp( $filePath ); break;
		}
		return $im;
	}
?>