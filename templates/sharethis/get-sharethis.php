<?php


//ini_set('display_errors', 0);
//loop to see how many fields are filled out

//find campaign name if any

$header = isset($_POST['header']) ? strtoupper($_POST['header']) : '';
$description = isset($_POST['description']) ? nl2br(trim($_POST['description'])) : '';
$sharelink = $_POST['share_link'];
$twitter_text = htmlspecialchars_decode($_POST['twitter_text']);
//$twitter_text = stripslashes(htmlentities($_POST['twitter_text']));


$linkedin_title = $_POST['linkedin_title'];
$linkedin_source = isset($_POST['linkedin_source']) ? $_POST['linkedin_source'] : '';
$linkedin_summary = isset($_POST['linkedin_summary']) ? $_POST['linkedin_summary'] : '';
$Iurl = $_POST['Iurl'];


	//$campaign = trim($_POST[str_replace(' ','%20','custom_campaign_name')]);
$truncated_link = explode('//', $sharelink);

$twitter_link = 'https://twitter.com/home?status='.str_replace(' ','%20',$twitter_text).'%0Ahttp%3A//'.$truncated_link[1];
$twitter_link = str_replace('#','%23',$twitter_link);
$facebook_link = 'https://www.facebook.com/sharer/sharer.php?u=http%3A//'.$truncated_link[1];
$linkedin_link = 'https://www.linkedin.com/shareArticle?mini=true&url=http%3A//'.$truncated_link[1].'&title='.str_replace(' ','%20',$linkedin_title).'&summary='.$linkedin_summary.'&source='.$linkedin_source;

$googleplus_link = 'https://plus.google.com/share?url=http%3A//'.$truncated_link[1];
//https://twitter.com/home?status=Hey%20check%20out%20my%20twitter%20link%0Ahttp%3A//www.sandiego.org/events
//https://www.facebook.com/sharer/sharer.php?u=http%3A//www.sandiego.org/events

//https://www.facebook.com/sharer/sharer.php?u=http%3A//
//https://www.linkedin.com/shareArticle?mini=true&url=http%3A//www.sandiego.org/events&title=My%20sweet%20LinkedIn%20Link&summary=&source=




 ?>
