<?php
require_once('config.php');
if($_POST){

$email = $_POST['email_address'];
$userpass = $_POST['password'];

$query = "SELECT * from users where
	Email='$email' and Password=PASSWORD('$userpass')";

//$project_name = $_POST['project_name'];
//$author = "Brent";
  
    
        
        
$result=mysqli_query($con,$query);
  //var_dump($result);   
        
	if(mysqli_num_rows($result)==1)
	{
		session_start();
		$_SESSION['auth']='true';
		$_SESSION['email']=$email;
    while ($row = mysqli_fetch_assoc($result)) {
      $_SESSION['role'] = $row['role'];
    }
		header('location:launchpad.php');
	}else{
		echo "Error: Wrong username or password. Remember, your username is your email address." .$sql . "<br>" . $con->error;
	}
}
?>
<?php include "00-Includes/footer.php"; ?>