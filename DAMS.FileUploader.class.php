<?php
require_once dirname(__FILE__)."/utils/ImageProcessing/createThubmnail.php";
require_once dirname(__FILE__)."/utils/ImageProcessing/resize.php";

class FileUploader{
	public $srcDir, $saveDir;

	public function __construct(){
		$this->saveDir = dirname(__FILE__)."/../uploadedFiles";
	}
	
	public function uploadImage( $file ){
		//$newName = date("Y-m-d-H-i-s").basename( $file );
		$newName = $this->getNewName( $file );
		resize( $file, $this->saveDir."/image/resize", $newName );
		trimming( $file, 100, $this->saveDir."/image/trim100", $newName );
		trimming( $file, 200, $this->saveDir."/image/trim200", $newName );
		trimming( $file, 300, $this->saveDir."/image/trim300", $newName );
		return $newName;
	}

	public function uploadImageOnlyOneItem( $file ){
		$newName = basename($file);
		resize( $file, $this->saveDir."/image/resize", $newName );
		trimming( $file, 100, $this->saveDir."/image/trim100", $newName );
		trimming( $file, 200, $this->saveDir."/image/trim200", $newName );
		trimming( $file, 300, $this->saveDir."/image/trim300", $newName );
		return $file;
	}

	public function getNewName( $file, $count=1 ){
		$newName = basename($file);
		$filename = pathinfo($file, PATHINFO_FILENAME);
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if( $count != 1 ){
			$newName = "$filename $count.$ext";
		}
		if( file_exists($this->saveDir."/image/resize/".$newName) ||
			file_exists($this->saveDir."/image/trim100/".$newName) || 
			file_exists($this->saveDir."/image/trim200/".$newName) || 
			file_exists($this->saveDir."/image/trim300/".$newName) ){
			return $this->getNewName( $file, ($count+1) );
		}
		return $newName;
	}
}