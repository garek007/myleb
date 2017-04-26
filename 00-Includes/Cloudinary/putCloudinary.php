<?php
require('Cloudinary.php');
require('Uploader.php');
require('Api.php');


\Cloudinary::config(array( 
  "cloud_name" => "[you get this when you sign up for a Cloudinary account it is in the upper right]", 
  "api_key" => "[obtain an api key and put here]", 
  "api_secret" => "[obtain an api secret and put here]" 
));



try {
	 //file_put_contents("upload/".$filename, $data);
	 
	\Cloudinary\Uploader::upload("data:image/jpg;base64,".$data, array("public_id" => $filename));
	//Cloudinary::Uploader.upload("data:image/png;base64,".$data);

	$myvar = 	array(
		'url'=> cloudinary_url($filename)
	);
	echo json_encode($myvar);
		
} catch (SoapFault $e) {
	echo '<pre>';
    print_r($e);
		echo '</pre>';
}

?>