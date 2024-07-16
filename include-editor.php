<?php

require_once ('page-common-headers.php');


?>
<style>

.progress-bar > *{

	display: inline-block

}

.progress-val{

	width: 100%;
	max-width: 500px;	
	min-height: 25px;	
	border-radius: 4px;	
	margin: 8px auto;
	text-align: center;
	background: #ff0000

}

#progress-val{
	
	background: #1E90FF;
	min-height: 25px; 
	max-height: 25px; 
	width: 0
}

.progress-percent{

	color: #fff;

}

</style>
<script src="<?php echo $domainHtmlFileRoot.$assetPre; ?>plugins/tinymce/tinymce.min.js"></script>
<script   type="text/javascript" src="<?php echo $domainHtmlFileRoot.$assetPre; ?>plugins/dropzone/dropzone.min.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo $domainHtmlFileRoot.$assetPre; ?>plugins/dropzone/dropzone.min.css" />

<script>

tinymce.init({

	selector:'#mce-editor', 
	branding:false,
	elementpath:true,
	//content_css:'/static/styles/main/css/7982017-657892788-38879932-red7342bfh-tech_forum_style_sheet_final.css',
	resize:'both',
	/*body_class:'img=tiny-img-ov',	
	automatic_uploads:false,
	image_upload_url:'/article-manager',*/
	media_live_embeds:true,
	paste_data_images:false,
	video_template_callback:function(data){

		return '<video  class="content-media dsk-platform-dpn" width="' + data.width + '" height="' + data.height + '"' + (data.poster? ' poster="' + data.poster + '"' : '') + ' controls="controls">\
				\n' + '<source src="' + data.source1 + '"' + (data.source1mime? ' type="' + data.source1mime + '"' : '') + ' />\n\
				\n' + (data.source2? '<source src="' + data.source2 + '"' + (data.source2mime? ' type="' + data.source2mime + '"' : '') + ' />\n' : '') + '</video>';

	},
	audio_template_callback:function(data){

		return '<audio class="content-media dsk-platform-dpn" controls="controls">\n' + '<source src="' + data.source1 + '"' + (data.source1mime? ' type="' + data.source1mime + '"' : '') + ' />\n\
				\n' + (data.source2? '<source src="' + data.source2 + '"' + (data.source2mime? ' type="' + data.source2mime + '"' : '') + ' />\n' : '') + '</audio>';

	},
	formats:{

		//aligncenter:{selector : 'audio', classes : 'center-media'}

	},
	height:600,
	statusbar:true,
	menubar:'file edit view insert format table help',
	plugins:'code link hr paste save table lists help textcolor image media searchreplace advlist', 
	toolbar:'strikethrough  underline blockquote | undo redo | styleselect | italic bold | fontselect fontsizeselect forecolor backcolor | alignleft aligncenter | alignright alignjustify | link bullist numlist outdent indent | image media',
	mobile:{

		theme: 'mobile',
		plugins: ['autosave ', 'list', 'autolink'],
		toolbar: ['undo', 'bold', 'italic', 'styleselect']

	}
	
	});
	
	Dropzone.options.dropZone = {

		paramName: 'files',			
		uploadMultiple:true,
		addRemoveLinks:true,
		dictCancelUpload:'cancel',
		dictCancelUploadConfirmation:'Do you really want to cancel upload?',
		//dictRemoveFile:'Remove',
		dictRemoveFileConfirmationx:'Do you really want to remove this file?',		
		dictFileTooBig:'Maximum file size allowed is 100Mb',
		//dictMaxFilesExceeded:'server size limit exceeded',
		//dictFallbackText:'server size limit exceeded',
		//dictResponseError:'server error',
		//forceFallback:0,
		//maxFiles:null,
		//maxFilesize:null,
		//clickable:true,
		init:function(){

			this.on("success", function(e, res){console.log(res.res)});
			this.on("error", function(e, res){console.log(res.res)});

			/*this.on("thumbnail", function(file){

				if(file.width != 200 || file.height != 300)
					file.dimOk();

				else
					file.dimErr();

			});*/

		},
		accept:function(file, done){

			//file.dimOk = done;
			///file.dimErr = done("Resize to 200 x 300 px");
			done();

		},
		dictDefaultMessage:'<span class="sky-blue">Drop files here to upload<br/> or </br/> click to browse a file.</span>'
		
	}

</script>