<?php

///FILE UPLOADER CLASS////

class FileUploader{
			
	/** Define class variables**/			
	private $filePassed;
	private $uploadPath;
	private $allowedExtArr = array();
	private $sizeUnit = "Mb";
	private $oneByteToUnit = (1024 * 1024);
	private $sizeLimit;
	private $widthLimit;
	private $heightLimit;
	private $fileTerm = 'file';
	private $uploadType = 'single';
	private $maxUploads = 0; // Not limited by default
	private $uploadCounts;
	private $maxUploadsComment = 'article';
	private $uok = true;
	private $errors = '';
	private $uploadsAcc = array();
	private $mpref = '';
	private $msuf = '';
	private $allowOverwrite = false;
	private $allowRename = false;
	private $useAlphaRename = false;
	private $useNumRename = false;
	private $renameLen = 10;
	private $uploadedFilesPathArr = array();
	private $uploadedFilesTmpNameArr = array();
	private $originalFilesArr = array();
	private $errPreTxt = '<b>ERRORS:</b><br/>';
	
	public function __construct($htmlName='', $uploadPath='', $allowedExtArr=array(), $sizeLimit='', 
			$widthLimit='', $heightLimit='', $fileTerm=''){
		
		$this->filePassed = (isset($_FILES[$htmlName]))? $_FILES[$htmlName] : '';
		$this->uploadPath = $uploadPath;
		$this->allowedExtArr = $allowedExtArr;
		$this->sizeLimit = $sizeLimit;
		$this->widthLimit = $widthLimit;
		$this->heightLimit = $heightLimit;		
		$fileTerm? ($this->fileTerm = $fileTerm) : '';
		
	}
	
	
	
	/** Method for checking if an item file is actually selected for upload **/	
	public function fileIsSelected(){
				
		/****ERROR WILL HAVE VALUE 4 IF NO FILE IS SELECTED AND VALUE 0 IF FILE IS SELECTED*******/
		return (isset($this->filePassed["error"][0]) && $this->filePassed["error"][0] == 0) || (isset($this->filePassed["error"]) && $this->filePassed["error"] == 0);
		
	}
	
	
	
	/** Method for fetching errors logged by the class **/			
	public function getErrors(){
				
		return $this->errors;
				
	}
	
	
	
	/** Method for fetching the status of uploaded items **/			
	public function getUploadStatus(){
				
		return $this->uok;
				
	}
	
	
	
	/** Method for fetching the file names of uploaded items **/			
	public function getUploadedFiles($retArr=false){
						
		if($retArr)
			return $this->uploadsAcc;
							
		else
			return implode("|", $this->uploadsAcc);

	}
	
	
	
	/** Method for setting the maximum length of strings allowed for file rename **/
	public function setRenameLen($l){
				
		$this->renameLen = $l;
				
	}
	
	
	/** Method for fetching the maximum length of strings allowed for file rename **/				
	public function getRenameLen(){
				
		return $this->renameLen;
				
	}
	
	
	/** Method for setting the boolean value state for file name overwrite **/	
	public function setOverwrite($bool){
		
		$this->allowOverwrite = boolval($bool);
	}
	
	
	
	
	/** Method for fetching the boolean value state for file name overwrite **/						
	public function getOverwrite(){
				
		return $this->allowOverwrite;
				
	}
	
	
	
	
	/** Method for setting the boolean value state for file rename **/			
	public function setRename($ren, $numOnly=false, $alphaOnly=false){
				
		$this->allowRename = boolval($ren);
		$this->useNumRename = boolval($numOnly);
		$this->useAlphaRename = boolval($alphaOnly);
				
	}
	
	
	
	/** Method for fetching the boolean value state for file rename **/			
	public function getRename(){
				
		return $this->allowRename;
				
	}			
	
	
	
	/** Method for setting the items passed for uploading **/	
	public function setFile($f){
				
		$this->filePassed = $f;
				
	}
	
	
	
	/** Method for fetching the items passed for uploading **/				
	public function getFile(){
				
		return $this->filePassed;
				
	}	
	
	
	
	/** Method for setting the destination upload path of item being uploaded **/				
	public function setUploadPath($up){
				
		$this->uploadPath = $up;
				
	}
	
	
	
	/** Method for fetching the destination upload path of item being uploaded **/			
	public function getUploadPath(){
				
		return $this->uploadPath;
				
	}
	
	
	
	/** Method for setting the allowed extensions of item being uploaded **/			
	public function setAllowedExt($extArr){
				
		$this->allowedExtArr = ((is_array($extArr))? $extArr : (array)$extArr);

	}
	
	
	
	/** Method for fetching the allowed extensions of item being uploaded **/			
	public function getAllowedExt(){
				
		$extArr = $this->allowedExtArr;
		return (is_array($extArr)? implode(",", $extArr) : $extArr);
				
	}
	
	
	
	/** Method for setting the maximum size allowed per item being uploaded **/
	public function setMaxSize($maxSize){
		
		$this->sizeLimit = $maxSize;
				
	}
	
	
	
	/** Method for fetching the maximum size allowed per item being uploaded **/			
	public function getMaxSize(){
				
		return $this->sizeLimit;
				
	}
	
	
	
	/** Method for setting the maximum width of image allowed when uploading images **/	
	public function setMaxImageWidth($w){
				
		$this->widthLimit = $w;
				
	}
	
	
	
	/** Method for fetching the maximum width of image allowed when uploading images **/				
	public function getMaxImageWidth(){
				
		return $this->widthLimit;
				
	}
	
	
	
	/** Method for setting the maximum height of image allowed when uploading images **/	
	public function setMaxImageHeight($h){
				
			$this->heightLimit = $h;
				
	}
	
	
	
	/** Method for fetching the maximum height of images allowed when uploading images **/			
	public function getMaxImageHeight(){
				
		return $this->heightLimit;
				
	}
	
	
	
	/** Method for setting the type of upload(e.g single, multiple) although the class will implicitly decode this by itself **/
	public function setUploadType($t){
				
		$this->uploadType = $t;
				
	}
	
	
	
	/** Method for fetching the type of upload(e.g single, multiple) although the class will implicitly decode this by itself **/				
	public function getUploadType(){
				
		return $this->uploadType;
				
	}
	
	
	
	/** Method for setting the size unit of the item being uploaded(e.g Kb, Mb, G etc) **/	
	public function setSizeUnit($u){
				
		$this->sizeUnit = $u;
				
	}
	
	
	/** Method for fetching the size unit of the item being uploaded(e.g Kb, Mb, G etc) **/							
	public function getSizeUnit(){
				
		return $this->sizeUnit;
				
	}
	
	
	/** Method for setting the byte value used to convert the item being uploaded to its size unit(e.g 1024 for byte to Kb size unit) **/				
	public function setByte2SizeUnit($eq){
				
		$this->oneByteToUnit = $eq;
				
	}
	
	
	
	/** Method for fetching the byte value used to convert the item being uploaded to its size unit(e.g 1024 for byte to Kb size unit) **/				
	public function getByte2SizeUnit(){
				
		return $this->oneByteToUnit;
				
	}
	
	
	
	/** Method for setting the text used to qualify the item being uploaded(file, image, audio, video etc) **/	
	public function setFileTerm($term){
				
		$this->fileTerm = $term;
				
	}
	
	
	/** Method for fetching the text used to qualify the item being uploaded(file, image, audio, video etc) **/						
	public function getFileTerm(){
				
		return $this->fileTerm;
				
	}	
	
	
	/** Method for setting maximum number of files that can be uploaded at a go when doing multiple uploads **/			
	public function setMaxUploads($max, $uploadCounts, $cmt=''){
				
		$this->maxUploads = (int)$max; 
		$this->uploadCounts = (int)$uploadCounts; 
		$this->maxUploadsComment = $cmt;
				
	}
	
	
	/** Method for fetching maximum number of files that can be uploaded at a go when doing multiple uploads **/			
	public function getMaxUploads($retArr = false){
				
		return $retArr? array($this->maxUploads, $this->uploadCounts, $this->maxUploadsComment) : $this->maxUploads;
				
	}	
	
	
	/** Method for setting suffix used to delimit each file string end point inside a database when multiple files are being saved to one database field or column  **/
	public function setMultiTypeSuffix($s){
				
		$this->msuf = $s;
				
	}
	
	
	/** Method for fetching suffix used to delimit each file string end point inside a database when multiple files are being saved to one database field or column  **/			
	public function getMultiTypeSuffix(){
				
		return $this->msuf;
				
	}
	
	
	/** Method for setting prefix used to delimit each file string start point inside a database when multiple files are being saved to one database field or column  **/
	public function setMultiTypePrefix($p){
				
		$this->mpref = $p;
				
	}
	
	
	/** Method for fetching prefix used to delimit each file string start point inside a database when multiple files are being saved to one database field or column  **/			
	public function getMultiTypePrefix(){
				
		return $this->mpref;
				
	}
	
	
	
	/** Method for fetching original names of files being uploaded **/
	public function getOriginalFileNames($retArr=false){
				
		if($retArr)
			return $this->originalFilesArr;
							
		else
			return implode("|", $this->originalFilesArr);
			
	}

	
	
	
	
	
	
	
	/** Method for actually doing upload of validated items or files **/
	public function upload(){
		
		return ($this->validateFiles() && $this->moveValidatedFiles2Server());
		
	}
	
	
	
	
	/** Method for validating files for upload **/
	private function validateFiles(){
	
		//////UPLOAD AN IMAGE OR A FILE////////////
				
		$tmpFilePathArr=array();
		$filesPassed = $this->filePassed;
		/////DO A TYPE CAST IF MULTIPLE FILES ARE PASSED BUT SINGLE UPLOAD IS CHOSEN/////
		(isset($filesPassed["error"][0])? ($this->uploadType = 'multiple') : ($this->uploadType = 'single'));					
		$uploadPath = $this->uploadPath;
		$allowedExtArr = $this->allowedExtArr;
		$sizeLimit = $this->sizeLimit;
		$widthLimit = $this->widthLimit;
		$heightLimit = $this->heightLimit;
		$uploadType = $this->uploadType;		
		$allowRename = $this->allowRename;	
		$maxUploads = $this->maxUploads;
		$uploadCounts = $this->uploadCounts;
		$allowOverwrite = $this->allowOverwrite;		
		$mpref = $this->mpref;
		$msuf = $this->msuf;		
		$oneByteToUnit = $this->oneByteToUnit;		
		$sizeUnit = $this->sizeUnit;
		$nums = '0123456789';
		$alphas = 'abcdefghijklmnopqrstuvwxyz-ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$alphaNum = $alphas.$nums;
				
		if($this->useAlphaRename)
			$renXters = $alphas;
				
		elseif($this->useNumRename)
			$renXters = $nums;
				
		else
			$renXters = $alphaNum;
				
		$renXters = str_shuffle($renXters);
		
		$uploadPath = (mb_substr($uploadPath, -1, 1) == "/")? $uploadPath : $uploadPath.'/';
		
		$len = is_array($filesPassed["name"])? count($filesPassed["name"]) : ($filesPassed["name"]? 1 : 0);					
		$uploadType = strtolower($uploadType);
		
		if($maxUploads && $len > ($maxUploads - $uploadCounts)){
				
			$this->errors .= '<span class="red">'.((!$this->errors)? $this->errPreTxt : ' ').'>> You can only upload a maximum of '.$maxUploads.' '.$this->maxUploadsComment.'</span>';
			$this->uok = false;
				
		}else		
			for($i = 0; $i < $len; $i++){
				
				if($uploadType == "multiple"){
					
					if($filesPassed["name"][$i]){	
						
						$fileSize = $filesPassed["size"][$i];
						$file = basename($filesPassed["name"][$i]);
						$this->originalFilesArr[] = $file;
						///////DECLARE THE FILE PATH/////
						$filePath = $uploadPath.$file;
						
						if($allowRename){
					
							////RENAME FILE NAMES////
							
							$fileRenStr = mb_substr($renXters, 0, $this->renameLen);
							
							$fileExtIndex = strrpos($file, ".");								
							$file = $fileRenStr.mb_substr($file, $fileExtIndex);
							
							///////RE-DECLARE THE RENAMED FILE PATH/////				
							$filePath = $uploadPath.$file;
						
						}						
						
					}else{
					
						continue;
								
					}
					
				}elseif($uploadType == "single"){
					
					if($filesPassed["name"]){	
					
						$file = basename($filesPassed["name"]);				
						$fileSize = $filesPassed["size"];	
						$this->originalFilesArr[] = $file;
						///////DECLARE THE FILE PATH/////
						$filePath = $uploadPath.$file;
						
						if($allowRename){		
				
							////RENAME FILE NAMES////
													
							$fileRenStr = mb_substr($renXters, 0, $this->renameLen);						
							
							$fileExtIndex = strrpos($file, ".");								
							$file = $fileRenStr.mb_substr($file, $fileExtIndex);
							
							///////RE-DECLARE THE RENAMED FILE PATH/////				
							$filePath = $uploadPath.$file;		
				
						}
						
					}else{
					
						continue;
								
					}
					
				}
				
				$fileTerm = $this->fileTerm;

				/*****CHECK IMAGE WIDTH AND HEIGHT*******/
				
				if($widthLimit || $heightLimit)	{
					
					if($uploadType == "multiple")
						$fileDetails = getimagesize($filesPassed["tmp_name"][$i]);
					
					elseif($uploadType == "single")
						$fileDetails = getimagesize($filesPassed["tmp_name"]);
					
					if($uploadType == "multiple")
						$fileType = $filesPassed["type"][$i];
					
					elseif($uploadType == "single")
						$fileType = $filesPassed["type"];
						
					if($widthLimit && $heightLimit && mb_substr($fileType, 0, 5) == "image" && ($fileDetails[0] != $widthLimit || $fileDetails[1] != $heightLimit)){
																
						$wErr = ($fileDetails[0] != $widthLimit);
						$hErr = ($fileDetails[1] != $heightLimit);
						$this->errors .= '<span class="red"><b>ERRORS:</b><br/>>> The '.$fileTerm.' dimensions did not conform with the required standard.<br/>
						Please resize your current '.$fileTerm.' dimensions(<span class="red">'.($wErr? 'width:'.$fileDetails[0].'px ' : '').($hErr? 'height:'.$fileDetails[1].'px' : '').'</span>)
						to (<span class="green">'.($wErr? 'width:'.$widthLimit.'px ' : '').($hErr? 'height:'.$heightLimit.'px' : '').'</span>)</span><br/>';
						
						$this->uok = false;
				
					}
					
				
				}
				
				/*******CHECK FILE EXTENSIONS ALLOWED*****/
				if(!empty($allowedExtArr)){
					
					$filetypeext = pathinfo($filePath, PATHINFO_EXTENSION);
					
					/************CONVERT EXT_ARR TO LOWERCASE********/
					foreach($allowedExtArr as $xt){
						
						$allowedExtArrLC[] = strtolower($xt);
					
					}
					
					if(!in_array((strtolower($filetypeext)), $allowedExtArrLC)){

						$this->uok = false;									
						$this->errors .= '<span class="red">'.(!$this->errors? $this->errPreTxt : ' ').'>> Only '.implode(', ', $allowedExtArrLC).'  extensions are allowed.</span><br/>';

					}
					
				}
				
				/*****CHECK FILE SIZE LIMIT*****/
				
				if($sizeLimit && $fileSize > $sizeLimit){

					$this->uok = false;										
					$this->errors .= '<span class="red">'.(!$this->errors? $this->errPreTxt : ' ').'>> '.(($len == 1)? 'The '.$fileTerm : 'One of the '.$fileTerm.'s').' you are trying to upload is '.round($fileSize/($oneByteToUnit),2).' '.$sizeUnit.' in size<br/>Maximum permissible size is '.round($sizeLimit/($oneByteToUnit), 2).' '.$sizeUnit.' for this '.$fileTerm.' operation</span><br/>';			
														
				}
				
				
				/******CHECK IF FILE ALREADY EXIST IN THE INTENDED UPLOAD PATH, THEN RENAME ACCORDINGLY******/
				if((file_exists($filePath)  || in_array($filePath, $tmpFilePathArr)) && $file && !$allowOverwrite){

					$y = 0;

					while(file_exists($filePath)  || in_array($filePath, $tmpFilePathArr)){
														
						//////MAKE THE UPLOAD FOLDER INVISIBLE TO USERS

						$fileNameLastIndex = strrpos($file, ".");
						
						$fn = mb_substr($file, 0, $fileNameLastIndex);
						
						$newfn = $fn.$y.mb_substr($file, $fileNameLastIndex);
											
						$filePath = $uploadPath.$newfn;
						
						$y++;
							
					}
						
					if($uploadType == "multiple")
						$this->uploadsAcc[] =  $mpref.$newfn.$msuf;
					
					elseif($uploadType == "single")
						$this->uploadsAcc[] =  $newfn;

					$filePath = $uploadPath.$newfn;/*********VERY VITAL ESPECIALLY DURING MULTIPLE UPLOADS*******/	
					
				}else{

					//////MAKE THE UPLOAD FOLDER INVISIBLE TO USERS
					
					if($uploadType == "multiple")
						$this->uploadsAcc[] = $mpref.$file.$msuf;
				
					elseif($uploadType == "single")
						$this->uploadsAcc[] = $file;

					$filePath = $uploadPath.$file;/*********VERY VITAL ESPECIALLY DURING MULTIPLE UPLOADS******/	

				}
				
					
				////TRACK MULTIPLE UPLOAD NAME CONFLICT///////
				$tmpFilePathArr[] = $filePath;
				
				///**VERY IMPORTANT**STOP ALL UPLOADS ONCE ONE OF THE FILES ARE NOT OK///////
				if(!$this->uok)
					break;
				
				/**********ACCUMULATE ALL UPLOAD'S FILEPATHS AND TMP_NAME SO LONG AS $UOK AND MOVE EACH TO SERVER BY CALLING UPLOAD() IN A LOOP**********/
				if($uploadType == "multiple")
					$this->uploadedFilesTmpNameArr[] = $filesPassed["tmp_name"][$i];
				  
				elseif($uploadType == "single")
					$this->uploadedFilesTmpNameArr[] = $filesPassed["tmp_name"];  
				
				$this->uploadedFilesPathArr[] = $filePath;/*ACCUMULATE PATHS SINCE THEY VARY FOR EACH FILES***********/

			}		

		
		return $this->uok;
		
	}
	
	
	
	
	
	/** Method for moving validated files to server(uploading) **/
	private function moveValidatedFiles2Server(){
			
		if($this->uok){
			
			$idx=0;
			
			while($idx < count($this->uploadedFilesPathArr) && $this->uok){									
						
				if(move_uploaded_file($this->uploadedFilesTmpNameArr[$idx], $this->uploadedFilesPathArr[$idx])){

					$this->uok = true;

				}else{
					
					$fileTerm = $this->fileTerm;
					$this->uok = false;
					$this->errors .= '<span class="red">'.(!$this->errors? $this->errPreTxt : ' ').'>> There was an error uploading your '.$fileTerm.': '.$this->originalFilesArr[$idx].' please try uploading the '.$fileTerm.' again </span>';
					break;		
				
				}	
				
				$idx++;
				
			}
		
		}
		
		return $this->uok; 		
				
	}
	
}


?>