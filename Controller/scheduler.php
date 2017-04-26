<div id="control_panel" class="control_panel">
<div id="resize_control_panel" class="open resize_control_panel">
<div class="drag_handles_w"><p></p><p></p><p></p></div>
<div id="minimize_control_panel" class="minimize_control_panel"><i class="fa fa-chevron-down"></i></div>


<?php

$email = $_SESSION['email']; 
//$email = "salachniewicz@sandiego.org";
?>
<form action="send_to_ET.php" method="post">




<h3>Scheduler</h3>

<h4>Select Lists</h4>
<?php

$username = explode("@",$email);
$username = $username[0];

$json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/settings/lists.json");
$lists = json_decode($json);
foreach($user->list_access as $list){
  echo '<div><input type="checkbox" name="lists[]" value="'.$lists->{$list}[1].'"><label>'.$lists->{$list}[0].'</label></div>';
}
?>

<div><label>Enter your own ID:</label><input type="text" name="lists[]"></div>

Enter Send Date: <input id="datepicker" type="text" name="send_date" /><br>



  <div style="margin:20px 0 20px;" id="slider"></div>
  <input type="text" name="time" id="time" />




<input style="clear:both;" type="submit" value="Send Email">

</form>




</div>
</div>
