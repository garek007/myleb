<?php

class sharethis{
  public function processFields($f3)
  {

    //$loggedin_user = $f3->get('SERVER.PHP_AUTH_USER');
    $loggedin_user = $f3->get('SESSION.user');
    $data = $f3->get('POST');


    $sharelink = $_POST['share_link'];
    $twitter_text = htmlspecialchars_decode($_POST['twitter_text']);
    //$twitter_text = stripslashes(htmlentities($_POST['twitter_text']));

    //twitter description needs very delicate handling
    $truncated_link = explode('//', $data['share_link']);

    $twitter_link = 'https://twitter.com/home?status='.str_replace(' ','%20',$data['twitter_text']).'%0Ahttp%3A//'.$truncated_link[1];
    $twitter_link = str_replace('#','%23',$twitter_link);

    $data['twitter_link']=$twitter_link;


    $data['facebook_link'] = 'https://www.facebook.com/sharer/sharer.php?u=http%3A//'.$truncated_link[1];
    $data['linkedin_link'] = 'https://www.linkedin.com/shareArticle?mini=true&url=http%3A//'.$truncated_link[1].'&title='.str_replace(' ','%20',$data['linkedin_title']).'&summary='.$data['linkedin_sum'].'&source='.$data['linkedin_src'];

    $data['googleplus_link'] = 'https://plus.google.com/share?url=http%3A//'.$truncated_link[1];
    //https://twitter.com/home?status=Hey%20check%20out%20my%20twitter%20link%0Ahttp%3A//www.sandiego.org/events
    //https://www.facebook.com/sharer/sharer.php?u=http%3A//www.sandiego.org/events

    //https://www.facebook.com/sharer/sharer.php?u=http%3A//
    //https://www.linkedin.com/shareArticle?mini=true&url=http%3A//www.sandiego.org/events&title=My%20sweet%20LinkedIn%20Link&summary=&source=


    $users = $f3->read('settings/users.json');
    $users = json_decode($users);
    $user = $users->$loggedin_user;

    $data['user_name'] = $user->name;
    $data['user_title'] = $user->title;
    $data['user_email'] = $user->email;
    $data['user_phone'] = $user->phone;
    $data['user_twitterhash'] = $user->twitterhash;
    $data['user_twitterlink'] = $user->twitterlink;
    $data['folder_id'] = 338240;//consider adding this to templates.json//this is where the email gets saved in ET

    $json = $f3->read('settings/lists.json');
    $lists = json_decode($json);
    $listArray = array();
    foreach($user->list_access as $list){
      $listArray[] = '<div><input type="checkbox" name="lists[]" value="'.$lists->{$list}[1].'"><label>'.$lists->{$list}[0].'</label></div>';
    }



    //$f3->set('content','sharethis-frm.htm');
		//echo Template::instance()->render('base.htm');
    $f3->set( 'lists' , $listArray );
    $f3->set( 'fields' , $data );
    $f3->set( 'content' , 'sharethis/sharethis.htm' );
		echo Template::instance()->render('email_template_base.htm');






  }
}//end of class



function makeField($fname,$f,$ftype){
$field='<label for="'.$fname.'">'.$f[1].'</label>';
if(!empty($f[2])){
  $p = 'placeholder="'.$f[2].'"';
}
if($f[3]=="required"){
  $r = "required";
}
$name_id = 'name="'.$fname.'" id="'.$fname.'"';
switch($ftype){
  case "text":
    $field .= '<input '.$name_id.' type="text" '.$p.' '.$r;
    break;
  case "textarea":
    $field .= '<textarea '.$name_id.' type="text" '.$p.'></textarea';
    break;
  case "url":
    $field .= '<input '.$name_id.' type="url" '.$p;
    break;
  case "select":
    $field .='<select '.$name_id.' type="text" >';
    $field .='<option value="custom">Select Option</option>';
    foreach($f[2] as $option){
      $field.= '<option value="'.$option.'" id="'.$option.'">'.$option.'</option>';
    }
    $field.='</select';
    break;
  case "image":
    $field .='<input '.$name_id.' type="text"';
    if(!empty($f[2])){
      $field.= 'class="'.$f[2].'"';
    }
    $field.='data-width="'.$f[3].'" data-height="'.$f[4].'" ';
    break;
  default:break;
}
$field.='>';

return $field;

}
