<?php 
ob_start();
session_start();
require_once('config.php');
include $_SERVER['DOCUMENT_ROOT']."/header.php";

if($_SESSION['auth']=='true'){header('location:launchpad.php');}

		
 
 ?>

<div class="first-row row">

<h1>Welcome, San Diego Tourism Authority</h1>
</div>


<div class="row">
<?php


?>
<form action="login.php" method="post">


<input type="text" name="email_address" placeholder="Email Address"/>
<input type="text" name="password" placeholder="password"/>


<input type="submit" value="Sign In" />

</form>




</div>










</div>







</form>





  

<?php include $_SERVER['DOCUMENT_ROOT']."/footer.php";

ob_flush(); ?>
