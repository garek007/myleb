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
				include '00-Includes/puttoportfolio.php';
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
			
			
			
			
			/*
//if I instead want to save to a mysql database
//saveImage($filename,$data);
function saveImage($name,$image){
	$host = "localhost";
	$user = "riptid9_myleb";
	$pass = "Ver1fyN0w!";
	$db = "riptid9_myleb";
	$con = mysqli_connect($host,$user,$pass,$db);
	//mysqli_select_db("riptid9_myleb",$con);
	$qry="INSERT INTO images (name,image) values ('$name','$image')";
	$result=mysqli_query($con,$qry);
	if($result)
	{
		echo "Image uploaded.";
	}
	else
	{
		echo "Image not uploaded";
		var_dump($result);
	}
	
}
function displayImage(){
	$host = "localhost";
	$user = "riptid9_myleb";
	$pass = "Ver1fyN0w!";
	$db = "riptid9_myleb";
	$con = mysqli_connect($host,$user,$pass,$db);	
	$qry="select * from images";
	$result=mysqli_query($con,$qry);
	while($row = mysqli_fetch_array($result)){
		echo '<img height="300" width="300" src="data:image;base64,'.$row[2].'">';
		
	}
	
	//link to tutorial https://www.youtube.com/watch?v=4ZpqQ3j1o2w
	
}



*/







// OLD CODE
/*

$filename = explode(".",basename($_FILES["file"]["name"]));
$filename = $filename[0];

$fnamelength = strlen($filename);
//echo "basename is ".$filename." and length is ".$fnamelength;

if($fnamelength < 4){
	echo "100";
	return;
}


$target_dir = "upload/";

$target_file = $target_dir . basename($_FILES["file"]["name"]);

//echo 'target file is '.$target_file;

$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image

// Check if file already exists
if (file_exists($target_file)) {
    echo "200";
    $uploadOk = 0;
		return;
}
// Check file size
//if ($_FILES["file"]["size"] > 50000000) {
  //  echo "Sorry, your file is too large.";
    //$uploadOk = 0;
//}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "300";
    $uploadOk = 0;
		return;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
				$DisplayName = basename( $_FILES["file"]["name"]);
				$ET_file = "http://www.enjoysandiego.com/upload/".basename( $_FILES["file"]["name"]);
				include 'apps/mylittleemailbuilder/00-Includes/puttoportfolio.php';
				//add later error handling
				//if status ok, then return image url
				
				
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}



*/

?>