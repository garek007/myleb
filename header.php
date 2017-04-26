<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Little Email Builder</title>
<!-- Latest compiled and minified CSS -->


<link href="/css/main.css" type="text/css" rel="stylesheet">
<link href="/css/controls.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
<link rel="stylesheet" href="/css/croppie.css" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
</head>

<body>
<div id="headercontainer" class="headercontainer">
<div class="row" style="padding-bottom:0;">
<header id="header">
<a href="index.php">
<img id="sitelogo" style="float:left;" src="/images/logo3.png" width="245" height="180" alt=""/></a>

<?php if($_SESSION['auth']): ?>


  <div style="float:right;color:#fff;">
  <a style="color:#fff;text-decoration:none;" href="launchpad.php">Launchpad</a>
  &nbsp;&nbsp;|&nbsp;&nbsp;<a style="color:#fff;text-decoration:none;" href="logout.php">Logout</a>


  <?php endif; ?>




  </div>



</header>
</div>
</div>
