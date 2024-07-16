<?php

require_once ('page-common-headers.php');
	
if($alertUser = $ENGINE->get_large_upload_limit_error()){

	if($GLOBAL_isAjax){

		$res["res"] = $alertUser;
		$res["rdr"] = '';
		echo json_encode($res);
		exit();

	}

}

//////HANDLE FILE UPLOAD BY AJAX AND MANUAL///////
if(isset($_POST["upload_file"]) || isset($_FILES["files"])){	
	
	$path = $GLOBAL_mediaRootCloudXCL;
	$path2file = $GLOBAL_mediaRootCloud;

	///folder path///
	if(isset($_POST[$K="folder_path"])){

		$pathSelected = $_POST[$K];

		switch(strtolower($pathSelected)){

			case 'clouds': $path = $path; break;

			case 'favicon': $path =  $GLOBAL_mediaRootFavXCL; $path2file = $GLOBAL_mediaRootFav; break;

			default:  $path = $path;

		}

	}

	///Overwrite/////
	if(isset($_POST["ovw"])){

		$ovw = true;

	}
	
	//////UPLOADING A FILE
	$FU = new FileUploader('files', $path);

	if(isset($ovw))
		$FU->setOverwrite(true);
	
	if($FU->fileIsSelected()){				
		
		if($FU->upload()){
			
			$uploadedFileNames="";$i=1;
			$uploadedFilesArr = $FU->getUploadedFiles(true);
			$len = count($uploadedFilesArr);

			foreach($uploadedFilesArr as $f){

				$uploadedFileNames .= $i.'.&nbsp;&nbsp;'.$path2file.$f.'<br/>';
				$i++;

			}
			
			$alertUser = '<div class="alert alert-success">Your '.(($len > 1)? 'files were' : ' file was ' ).' successfully uploaded<br/>
								To use the uploaded '.(($len > 1)? 'files simply copy the links ' : ' file  simply copy the link ' ).' below<hr/>
								<div class="align-l">'.$uploadedFileNames.'</div>
							</div>';

		}else
			$alertUser = $FU->getErrors();						
											
		
	}else{

		$alertUser = '<span class="alert alert-danger">Please browse and select a file from your device !</span>';
		$noFile = true;

	}
	
	if($GLOBAL_isAjax){

		$res["res"] = $alertUser;
		$res["rdr"] = '';
		$res["noFile"] = isset($noFile)? $noFile : '';
		echo json_encode($res);
		exit();

	}

}


$uploaderHtml = '<div class="panel panel-gray">	
					<h1 class="panel-head page-title">File Uploader</h1>
					<div class="panel-body">
						'.(isset($alertUser)? $alertUser : '').'			
						<div id="ajax-fp-response"></div>
						<div class="progress-bar hide" id="progress">
							<div class="progress-val">
								<div id="progress-val"></div>							
							</div>
							<button id="ajax-fp-cancel" class="btn btn-xs btn-danger" >cancel</button>					
						</div>						
						<form class="ajax-fp-submit" data-response-holder="ajax-fp-response" method="post" action="'.$GLOBAL_page_self_rel.'" enctype="multipart/form-data" >
							<div class="field-ctrl">
								<label>Upload Path</label>
								<select class="field" name="folder_path">
									<option>Clouds</option>
									<option>Favicon</option>						
								</select>
								<label class="red">Overwrite<input title="overwrite file with same name" type="checkbox" name="ovw" class="checkbox" /></label>
							</div>
							<div class="field-ctrl">
								<input type="file" name="files[]" title="Browse File" multiple class="upload-field field" />															
								<button type="submit" class="form-btn btn-ctrl" name="upload_file" ><i class="fas fa-file"></i> Upload</button>
							</div>	
						</form>
					</div>
				</div>';


?>