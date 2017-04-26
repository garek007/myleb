<?php
session_start();




$pageTitle = "Shareable Content Complete Email Builder"; 
$date = date('md');
$year = date('Y-m-');
$user = $_SESSION['email'];
//need to get template name here from SESSION or launchpad
$template = $_GET['tmp'];
include $_SERVER['DOCUMENT_ROOT']."/header.php";
?>

<div class="first-row row">
<h2><?php echo $pageTitle; ?></h2>

</div>
<div id="cropbox" class="cropbox">
<i class="fa fa-check upload-result fa-4x" aria-hidden="true"></i>
<i class="fa fa-times upload-cancel fa-4x" aria-hidden="true"></i>
<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>

<div id="viewport_height" class="viewport_height"></div>
      Height
    <div class="viewport_height_value">--</div>
</div>

<?php
if($user == "jpatiag@sandiego.org"){
  //include "forms/frm-research.php";
}else{
  //include "forms/frm-sharethis.php";
}





if($_POST){


  if(isset($_POST['username'])){

    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/settings/users.json");

    $users = json_decode($json);
    $user = $users->$_POST['username'];
  }


  include $template . "/get-sharethis.php";

  
  if($user->email == 'smason@sandiego.org'){
    include $template . '/lyt-sharethis-internal.php';
  }else{
    include $template . '/lyt-sharethis.php';
  }


  $_SESSION['html'] = $html;
  $_SESSION['subject'] = stripslashes($_POST['et_subject']);
  $_SESSION['email_name'] = $_POST['et_email_name'];
  $_SESSION['from_name'] = $user->name;
  $_SESSION['from_email'] = $user->email;


  include $_SERVER['DOCUMENT_ROOT'].'/Controller/scheduler.php';

  ?>

  <div class="row">
  <div class="first-col cols_12-12">
  <?php
  echo $html;
  ?>
  </div></div>
  <?php



}else{
  $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/settings/templates.json");
  $templates = json_decode($json);
  $fields = $templates->$template->fields;
  //echo "<pre>";
  //var_dump($fields);
  //echo "</pre>";

  include $_SERVER['DOCUMENT_ROOT'] . "/Controller/generate-fields.php";
  include $template . "/frm-".$template.".php";

echo $html;


}


/*
foreach($_POST as $key => $value){
  echo "<pre>";
 echo $key . ' has a value of ' . $value;
  echo "</pre>";
}
*/


include $_SERVER['DOCUMENT_ROOT']."/footer.php";

?>
