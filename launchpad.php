<?php
session_start();
require_once('config.php');
include "header.php";  

$usertype = $_SESSION['role'];

$email = $_SESSION['email'];
$date = date('Y-m-d');

 
if($_POST){
	$deleteprojects = $_POST['delete'];
	foreach($deleteprojects as $projid){
		//echo $projid;
		$query = "DELETE FROM projects WHERE Project_Number='$projid'";
		$results = mysqli_query($con,$query);	
		
	}
	
}

?>

<div class="first-row row">
  <div class="cols_12-12">
    <?php if($_SESSION['auth']=='true'): ?>
    <h3> Hello <?php echo $email;  ?>! </h3>
    <h4>Start a new project or select from your existing projects.</h4>
  </div>
</div>
<div class="row">
  <div class="first-col cols_12-5">


    
    <h4 style="padding-top:50px;">Templates</h4>
    <strong>
    <p>Shareable Content</p>
    </strong> <a href="templates/index.php?tmp=sharethis"><img src="images/shareable.jpg" width="400" alt=""/> </a> <br>
    <br>
    <strong>
    <p>Sweepstakes Email</p>
    </strong> <a href="layouts/index.php?tmp=sweepstakes"><img src="images/sweepstakes.jpg" width="400" alt=""/> </a>
    <?php else: ?>
    You are not logged in, please go back and login. <a href="index.php"><- Back</a>
    <?php endif; ?>
  </div>
</div>
<?php include "footer.php"; ?>