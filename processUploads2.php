<?php
header("Access-Control-Allow-Origin: *");
//$uploadedFiles = $_FILES['file']['tmp_name'];
//echo $uploadedFiles;
//move_uploaded_file($uploadedFiles, "http://www.enjoysandiego.com/upload/new_whale.jpg");

//echo "100";
//$now = date("YmdHis");
      $data = $_POST['file'];
			$filename = $_POST['imagename'];
			$uploadLocation = $_POST['uploadLocation'];
			list($type, $data) = explode(';', $data);
			list(, $data)      = explode(',', $data);
			
				
//			$data = str_replace(' ','+',$data);
			
			if($uploadLocation == 'exacttarget'){
				$data = base64_decode($data);
				file_put_contents("upload/".$filename, $data);
				$ET_file = "http://www.mylittleemailbuilder.com/upload/".$filename;
				include 'puttoportfolio.php';
			}else if($uploadLocation == 'myleb_folder'){
        $today = date('Y_m');
        
        if(!file_exists("upload/".$today)){
          mkdir("upload/".$today,0777,true);
        }
	      $data = base64_decode($data);
				file_put_contents("upload/".$today."/".$filename, $data);         
        
        $myvar = 	array(
          'url'=> 'http://www.mylittleemailbuilder.com/upload/'.$today."/".$filename
        );
        echo json_encode($myvar);
        
      }else{
				$filename = explode(".",$filename);
				$filename = $filename[0];
				include '00-Includes/putCloudinary.php';

				//include 'putCloudinary.php';
				
			}
			
			

?>