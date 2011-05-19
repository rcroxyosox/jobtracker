<?php
// ==============
// Configuration
// ==============


class fileUpload{

var $uploaddir = '/'; //folder/images/ Where you want the files to upload to - Important: Make sure this folders permissions is 0777!
var $allowedExts;// array "pdf, jpg, etc" These are the allowed extensions of the files that are uploaded
var $allowedTypes; // array "image, application, etc" These are the allowed extensions of the files that are uploaded
var $maxSize; // 50000 is the same as 50kb
var $maxHeight; // This is in pixels - Leave this field empty if you don't want to upload images
var $maxWidth; // This is in pixels - Leave this field empty if you don't want to upload images 
var $uploadedFile;
var $renameFileTo;
var $errors; // array

function FileUpload(){

	$this->maxSize = 20; // in mbs ($_FILES['quotefile']['size'])/1000000)
	$this->maxHeight = 10000;
	$this->maxWidth = 10000;
}
function getImploded($delim, $arr){
	if(is_array($arr)){
		return implode($delim, $arr);
	}else{
		return '';	
	}
}

function setUploadedFile($uploadedFile){
	$this->uploadedFile = $uploadedFile;
	$this->renameFileTo = $uploadedFile['name'];
}

function getUploadedFile(){
	return $this->uploadedFile;
}

function setUploadDir($uploaddir){
	$this->uploaddir = $uploaddir;
}

function getUploadDir(){
	return $this->uploaddir;
}

function setallowedExts($allowedExts){
	$this->allowedExts = $allowedExts;
}

function getallowedExts(){
	$arr = $this->allowedExts;
	asort($arr);
	return $arr;
}
function setAllowedTypes($allowedTypes){
	$this->allowedTypes = $allowedTypes;
}

function getAllowedTypes(){
	return $this->allowedTypes;
}

function getMaxSize(){
	return $this->maxSize;
}

function setMaxSize($maxSize){
	$this->maxSize = $maxSize;
}

function setMaxHeight($maxHeight){
	$this->maxHeight = $maxHeight;
}

function getMaxHeight(){
	return $this->maxHeight;
}

function setMaxWidth($maxWidth){
	$this->$maxWidth = $maxWidth;
}

function getMaxWidth(){
	return $this->maxWidth;
}

function setRenameFileTo($renameFileTo){
	$file = $this->uploadedFile;
	$ext = substr($file['name'], strrpos($file['name'], '.'));
	$this->renameFileTo = $renameFileTo.$ext;
}

function getRenameFileTo(){
	$file = $this->uploadedFile;
	if(isset($this->renameFileTo) && strlen($this->renameFileTo) > 3){
		return $this->renameFileTo;
	}else{
		return $file['name'];
	}
}

function getRelFilePath(){
	return $this->uploaddir.$this->getRenameFileTo();	
}

function wasUploaded(){
	return is_file($this->getRelFilePath());	
}

function setMaxWidthAndHeight($maxWidth,$maxHeight){
	$this->$maxWidth = $maxWidth;
	$this->maxHeight = $maxHeight;
}

function getFileSize(){
	$file = $this->uploadedFile;
	return $file['size'];
}

function getExt(){
	$file = $this->uploadedFile;
	$filename = strtolower($file['name']) ; 
	$exts = split("[/\\.]", $filename) ; 
	$n = count($exts)-1; 
	$exts = $exts[$n]; 
	return $exts; 
}

function getType(){
	$file = $this->uploadedFile;	
	return $file['type'];
}

function checkExtensions(){
	$ok = 'fail';
	
	if(is_array($this->allowedExts)){
		if(in_array($this->getExt(), $this->allowedExts) || count($this->allowedExts)==0){
			$ok = 'pass';
		}
	}else{
		$ok = 'pass';	
	}
	
	return $ok;
}

function checkType(){
	$ok = 'fail';
	$file = $this->uploadedFile;
	$types = $this->getType();
	
	if(is_array($this->allowedTypes)){
		if(in_array($types, $this->allowedTypes) || count($this->allowedTypes)==0){
			$ok = 'pass';
		}
	}else{
		$ok = 'pass';	
	}
	
	return $ok;
}


// Check File Size
function checkFileSize(){
	$ok = 'fail';
	$file = $this->uploadedFile;
	$filesizeinmbs = $file['size']/1000000;
	if ($this->checkExtensions()) {
		if($filesizeinmbs <= $this->maxSize && $file['error']!=1){
			$ok = 'pass';
		}
	}
	return $ok;
}

// Check Height & Width
function checkHeightWidth(){
	$ok = 'fail';
	$file = $this->uploadedFile;
	
		list($width, $height, $type, $w) = getimagesize($file['tmp_name']);
		if(strtoupper($this->allowedExts) == "IMAGE"){
			if($width <= $this->maxWidth || $height <= $this->maxHeight){
				$ok = 'pass';
			}
		}else{
			$ok = 'pass';
		}
	
	return $ok;
}

function uploadErrors(){
	
	
	//$errors['dimensions'] = array($this->checkHeightWidth(),'');
	$this->errors['size'] = array($this->checkFileSize(),
		' | Actual : '.($file['size']/1000000).', Set required : '.$this->maxSize);
	$this->errors['extension'] = array($this->checkExtensions(), 
		' | Actual : '.$this->getExt().', Set required : '.$this->getImploded(',',$this->allowedExts));
	$this->errors['type'] = array($this->checkType(), 
		' | Actual : '.$this->getType().', Set required : '.$this->getImploded(',',$this->allowedTypes));
	return $this->errors;
}


function hasErrors(){
	$has = false;
	
	if(isset($this->uploadedFile)){
	foreach($this->uploadErrors() as $varr){
		if($varr[0] == 'fail'){
			$has = true; break;
		}
	}
	}
	
	return $has;
}

//return errors as a php array
function getErrors(){
	$errors = array();
	if($this->hasErrors()){
		
		
		$errorstr = array(
			'move'=>'The file could not be moved',
			'uploaded'=>'The file could not be uploaded',
			'dimensions'=>'Check the demensions of the file upload',
			'size'=>'The maximum upload size of '.$this->maxSize.'mb has been exceeded',
			'extension'=>'Allowed extensions are: '.$this->getImploded(', ',$this->getallowedExts()),
			'type'=>'Allowed MIME types are: '.$this->getImploded(', ',$this->allowedTypes)
		);
		
		foreach($this->uploadErrors() as $errortype => $varr){
			if($varr[0] == 'fail'){
				$errors[]=$errorstr[$errortype];
			}
		}	
	}
	return $errors;
}

function displayErrors($ulid = "ulerrors"){
	
	$ret = '';
	if($this->hasErrors()){
		$ret .= '<ul id="'.$ulid.'">';
		foreach($this->getErrors() as $err){
			$ret .= '<li>'.$err.'</li>';
		}	
		$ret .= '</ul>';	
	}
	
	return $ret;
}


// The Upload Part
function uploadFile(){
	
	$file = $this->uploadedFile;
	
	if(!$this->hasErrors()){
		
		if(is_uploaded_file($file['tmp_name'])){
			if(!move_uploaded_file($file['tmp_name'],$this->uploaddir.'/'.$this->renameFileTo)){
				$this->errors['move'] = array('fail','');
			}		
		}else{
			$this->errors['uploaded'] = array('fail','');
		}
	}
	
	
	
}



}
?>