<?php

class research{
  public function processFields($f3)
  {
    //$loggedin_user = $f3->get('SERVER.PHP_AUTH_USER');
    $loggedin_user = $f3->get('SESSION.user');
    $data = $f3->get('POST');
    $data['description'] = nl2br(trim($f3->get('POST.description')));

    $perfFields = ["total_visitors","visitor_spending","large_attr_att","airport_arrivals","occupancy_rate","adr","sdcc_attendance","sdcc_room_nights"];

    foreach($perfFields as $field){
      $up = 'http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/arrow-up.png';
      $down = 'http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/arrow-down.png';
      $nochange = 'http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/diamond.png';
      $tol = .5;

      switch( true ){
        case $data[$field.'_chg'] < -$tol: $data[$field.'_ud'] = $down; break;
        case $data[$field.'_chg'] > $tol: $data[$field.'_ud'] = $up; break;
        default: $data[$field.'_ud'] = $nochange;break;
      }
      switch( true ){
        case $data['ytd_'.$field.'_chg'] < -$tol: $data['ytd_'.$field.'_ud'] = $down; break;
        case $data['ytd_'.$field.'_chg'] > $tol: $data['ytd_'.$field.'_ud'] = $up; break;
        default: $data['ytd_'.$field.'_ud'] = $nochange;break;
      }

    }




    $users = $f3->read('settings/users.json');
    $users = json_decode($users);
    $user = $users->$loggedin_user;

    //$data['user_name'] = $user->name;
    $data['user_title'] = $user->title;
    //$data['user_email'] = $user->email;
    $data['user_phone'] = $user->phone;
    $data['user_twitterhash'] = $user->twitterhash;
    $data['user_twitterlink'] = $user->twitterlink;
    $data['folder_id'] = 334647;//consider adding this to templates.json//this is where the email gets saved in ET



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
    $f3->set( 'content' , 'research/research.htm' );
		echo Template::instance()->render('email_template_base.htm');






  }
}//end of class
