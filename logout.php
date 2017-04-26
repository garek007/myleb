<?php
session_start();
session_unset();
session_destroy();
//unset($_SESSION['auth']);
$_SESSION = array();
header('location:index.php');

?>
