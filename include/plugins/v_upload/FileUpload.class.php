<?php

class FileUpload {
	private $db;
	private $localization;
	private $vavok;

	function __construct() {

		global $db, $localization, $vavok;

		$this->db = $db;
		$this->lang_admin = $localization->show_strings();
		$this->vavok = $vavok;
	}


	public function get_header_data() {

$data = <<<'HEAD'
		<script type="text/javascript" src="../include/plugins/v_upload/js/jquery.form.min.js"></script>

		<script type="text/javascript">
		$(document).ready(function() { 

			var progressbox     = $('#progressbox');
			var progressbar     = $('#progressbar');
			var statustxt       = $('#statustxt');
			var completed       = '0%';
			
			var options = { 
					target:   '#output',   // target element(s) to be updated with server response 
					beforeSubmit:  beforeSubmit,  // pre-submit callback 
					uploadProgress: OnProgress,
					success:       afterSuccess,  // post-submit callback 
					resetForm: true        // reset the form after successful submit 
				}; 
				
			 $('#MyUploadForm').submit(function() { 
					$(this).ajaxSubmit(options);  			
					// return false to prevent standard browser submit and page navigation 
					return false; 
				});
			
		//when upload progresses	
		function OnProgress(event, position, total, percentComplete)
		{
			//Progress bar
			progressbar.width(percentComplete + '%') //update progressbar percent complete
			statustxt.html(percentComplete + '%'); //update status text
			if(percentComplete>50)
				{
					statustxt.css('color','#fff'); //change status text to white after 50%
				}
		}

		//after succesful upload
		function afterSuccess()
		{
			$('#submit-btn').show(); //hide submit button
			$('#loading-img').hide(); //hide submit button

		}

		//function to check browser and progres
		function beforeSubmit(){
		    //check whether browser fully supports all File API
		   if (window.File && window.FileReader && window.FileList && window.Blob)
			{

				if( !$('#imageInput').val()) //check empty input filed
				{
					$("#output").html("Are you kidding me?");
					return false
				}
				
				//Progress bar
				progressbox.show(); //show progressbar
				progressbar.width(completed); //initial value 0% of progressbar
				statustxt.html(completed); //set status text
				statustxt.css('color','#000'); //initial color of status text

						
				$('#submit-btn').hide(); //hide submit button
				$('#loading-img').show(); //hide submit button
				$("#output").html("");  
			}
			else
			{
				//Output error to older unsupported browsers that doesn't support HTML5 File API
				$("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
				return false;
			}
		}

		//function to format bites
		function bytesToSize(bytes) {
		   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		   if (bytes == 0) return '0 Bytes';
		   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
		   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
		}

		}); 

		</script>

		<style type="text/css">
		#upload-wrapper {
			width: 50%;
			margin-right: auto;
			margin-left: auto;
			margin-top: 50px;
			background: #F5F5F5;
			padding: 50px;
			border-radius: 10px;
			box-shadow: 1px 1px 3px #AAA;
		}
		#upload-wrapper h3 {
			padding: 0px 0px 10px 0px;
			margin: 0px 0px 20px 0px;
			margin-top: -30px;
			border-bottom: 1px dotted #DDD;
		}
		#upload-wrapper input[type=file] {
			border: 1px solid #DDD;
			padding: 6px;
			background: #FFF;
			border-radius: 5px;
		}
		#upload-wrapper #submit-btn {
			border: none;
			padding: 10px;
			background: #61BAE4;
			border-radius: 5px;
			color: #FFF;
		}
		#output{
			padding: 5px;
			font-size: 12px;
		}
		#output img {
			border: 1px solid #DDD;
			padding: 5px;
		}

		/* progress bar style */
		#progressbox {
			border: 1px solid #92C8DA;
			padding: 1px; 
			position:relative;
			width:400px;
			border-radius: 3px;
			margin: 10px;
			display:none;
			text-align:left;
		}
		#progressbar {
			height:20px;
			border-radius: 3px;
			background-color: #77E0FA;
			width:1%;
		}
		#statustxt {
			top:3px;
			left:50%;
			position:absolute;
			display:inline-block;
			color: #000000;
		}
		</style>
HEAD;

		return $data;
	}


	public function upload($directory = '') {


    if (isset($_POST['width']) && !empty($_POST['width'])) {
        $width = $this->vavok->check($_POST['width']);
    }
    if (isset($_POST['rename']) && !empty($_POST['rename'])) {
        $rename = $this->vavok->check($_POST['rename']);
    } 
    if (isset($_POST['lowercase']) && !empty($_POST['lowercase'])) {
        $lowercase = $this->vavok->check($_POST['lowercase']);
    } else {
    	$lowercase = '';
    }

    // Receiving variables
    $upload_Name = $_FILES['upload']['name'];
    $upload_Size = $_FILES['upload']['size'];
    $upload_Temp = $_FILES['upload']['tmp_name'];
    $upload_Mime_Type = $_FILES['upload']['type'];
    $fileInfo = pathinfo($upload_Name);
	$upload_Ext = strtolower($fileInfo["extension"]);

    function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            RecursiveMkdir(dirname($path));
            mkdir($path, 0777);
        } 
    }

    // Validation
    if ($upload_Size == 0) {
        die("<p align='center'><font face='Arial' size='3' color='#FF0000'>Please enter a valid upload</font></p>");
    }

    if ($upload_Size > 10000000) { // 10000000 bytes = 10MB
        // delete file if it is too big
        unlink($upload_Temp);
        
        return array('error' => $this->lang_admin['bigfile']);
    }
    
    /*
    if ($upload_Mime_Type != "image/gif" AND $upload_Mime_Type != "image/png" AND $upload_Mime_Type != "image/jpeg" AND $upload_Mime_Type != "text/" AND $upload_Mime_Type != "text/calendar" AND $upload_Mime_Type != "text/css" AND $upload_Mime_Type != "text/directory" AND $upload_Mime_Type != "text/enriched" AND $upload_Mime_Type != "text/html" AND $upload_Mime_Type != "text/parityfec" AND $upload_Mime_Type != "text/plain") {
        unlink($upload_Temp);
        die("<p align='center'><font face='Arial' size='3' color='#FF0000'>This file extension is not allowed</font></p>");
    } 
    */

    if (!empty($rename)) {
    	$upload_Name = $rename . '.' . $upload_Ext;
    }
    $upload_Name = str_replace(' ', '-', $upload_Name);
	$upload_Name = str_replace('--', '-', $upload_Name);

	if ($lowercase == 'lower') {
	$upload_Name = mb_strtolower($upload_Name, 'UTF-8');
	}

		
    $first_dir = date('y');
    if (!is_dir("../fls/" . $first_dir . "")) {
    @mkdir(BASEDIR . "fls/" . $first_dir . "", 0777);
    @touch(BASEDIR . "fls/" . $first_dir . "/index.php");
    } 
    $second_dir = date('n');
    if (!is_dir(BASEDIR . "fls/" . $first_dir . "/" . $second_dir . "")) {
    @mkdir(BASEDIR . "fls/" . $first_dir . "/" . $second_dir . "", 0777);
    @touch(BASEDIR . "fls/" . $first_dir . "/" . $second_dir . "/index.php");
    @copy(BASEDIR . "fls/.htaccess", "../fls/" . $first_dir . "/" . $second_dir . "/.htaccess");
    } 

    $uploadFile = BASEDIR . "fls/" . $first_dir . "/" . $second_dir . "/" . $upload_Name;

    if (!empty($directory)) { $uploadFile = $directory . $upload_Name; }

    if (file_exists($uploadFile)) {
        return array('error' => $this->lang_admin['fileexists']);
    } else {
        if (!is_dir(dirname($uploadFile))) {
            RecursiveMkdir(dirname($uploadFile));
        } else {
            chmod(dirname($uploadFile), 0777);
        } 
        
        if (!empty($width) && $width > 0) {
            $image = new SimpleImage();
            $image->load($upload_Temp);
            $image->resizeToWidth($width);
            $image->save($uploadFile);
        } else {
            
            move_uploaded_file($upload_Temp , $uploadFile);
        } 

        // Insert data to database if we are uploading to default upload directory
        if (empty($directory)) {
	        chmod($uploadFile, 0644);

	        $upload_URL = "/fls/" . $first_dir . "/" . $second_dir . "/" . $upload_Name;
	        $ext = substr($upload_Name, strrpos($upload_Name, '.') + 1);
	        $ext = strtolower($ext);

			$values = array(
				'name' => $upload_Name,
				'date' => time(),
				'ext' => $ext,
				'fulldir' => $upload_URL
			);

			$this->db->insert_data('uplfiles', $values);

	    } else {
	    	$upload_URL = $directory . $upload_Name;
	    }
	}

    return array('file_address' => $this->vavok->website_home_address() . $upload_URL);

	}
}


?>