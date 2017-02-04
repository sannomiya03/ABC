<?php
	require_once dirname(__FILE__)."/isImage.php";
	ini_set('memory_limit', '512M');

	function resize( $filePath, $saveDir, $saveName ){
		$image = imageCreateFromAny( $filePath );
		if( !$image ) return false;

		list( $img_w, $img_h ) = getimagesize( $filePath );
		$longer_wide = $img_w > $img_h;
		$new_w = 0;
		$new_h = 0;
		$max_w = 1200;
		$max_h = 1200;

		if( $img_h < $max_h && $img_w < $max_w ){
			$new_w = $img_w;
			$new_h = $img_h;
		}else{
			if( $longer_wide ){
				$new_w = $max_w;
				$new_h = $img_h*($max_w/$img_w);
			}else{
				$new_w = $img_w*($max_h/$img_h);
				$new_h = $max_h;
			}
		}

		$canvas = imagecreatetruecolor( $new_w, $new_h );
		imagecopyresampled(
			$canvas,
			$image,
			0,
			0,
			0,
			0,
			$new_w,
			$new_h,
			$img_w,
			$img_h
		);

		$savePath = $saveDir."/".$saveName;

		if( !file_exists($saveDir) ) mkdir( $saveDir, 0777 );
		if( file_exists($savePath) ) return $savePath;

		imagejpeg(
			$canvas,
			$savePath,
			100
		);

		imagedestroy($image);
		imagedestroy($canvas);

		return $savePath;
	}
?>