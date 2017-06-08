<?php

// Kickstart the framework
$f3=require('lib/base.php');
$f3->set('AUTOLOAD','App/Controllers/');
//require_once('vendor/exacttarget/exacttarget.php');
$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');
//$access=Access::instance();
//$access->policy('deny');

//Home page view
$f3->route('GET /',
	function($f3){
    $f3->set('content','home.htm');
    echo Template::instance()->render('base.htm');
	}
);
/*
$f3->route('GET /',
	function($f3) {
		$classes=array(
			'Base'=>
				array(
					'hash',
					'json',
					'session',
					'mbstring'
				),
			'Cache'=>
				array(
					'apc',
					'memcache',
					'memcached',
					'redis',
					'wincache',
					'xcache'
				),
			'DB\SQL'=>
				array(
					'pdo',
					'pdo_dblib',
					'pdo_mssql',
					'pdo_mysql',
					'pdo_odbc',
					'pdo_pgsql',
					'pdo_sqlite',
					'pdo_sqlsrv'
				),
			'DB\Jig'=>
				array('json'),
			'DB\Mongo'=>
				array(
					'json',
					'mongo'
				),
			'Auth'=>
				array('ldap','pdo'),
			'Bcrypt'=>
				array(
					'mcrypt',
					'openssl'
				),
			'Image'=>
				array('gd'),
			'Lexicon'=>
				array('iconv'),
			'SMTP'=>
				array('openssl'),
			'Web'=>
				array('curl','openssl','simplexml'),
			'Web\Geo'=>
				array('geoip','json'),
			'Web\OpenID'=>
				array('json','simplexml'),
			'Web\Pingback'=>
				array('dom','xmlrpc')
		);
		$f3->set('classes',$classes);
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);
*/
$f3->route('GET /userref',
	function($f3) {
		$f3->set('content','userref.htm');
		echo View::instance()->render('layout.htm');
	}
);
$f3->route('GET /noaccess',
	function($f3) {
		$f3->set('content','noaccess.htm');
		echo Template::instance()->render('base.htm');
	}
);
if($f3->get('SESSION.pass')=='750_BStreet!'){
	$f3->route('GET /sharethis','GenerateFields->processFields');
	$f3->route('POST /sharethis','ShareThis->processFields');

	$f3->route('GET /research','GenerateFields->processFields');
	$f3->route('POST /research','Research->processFields');

	$f3->route('GET /pressrelease','GenerateFields->processFields');
	$f3->route('POST /pressrelease','PressRelease->processFields');

	$f3->route('POST /send','SendExactTarget->send');
}else{
	$path = $f3->get('PATH');
	switch($path){
		case "/":
		case "/login":
		case "/logout": break;
		default: $f3->reroute('/noaccess');break;
	}


}

$f3->route('GET /logout',
  function($f3){
  echo $f3->get('SESSION.pass');
  $f3->clear('SESSION.pass');
  session_commit();
    //var_dump($f3);

    //$auth->logout();
  }
);
$f3->route('POST /login',
  function($f3){
    $data = $f3->get('POST');
    var_dump($data);
    $db = new \DB\Jig ( 'settings/' , \DB\Jig::FORMAT_JSON );
    $user= new \DB\Jig\Mapper($db, 'users.json');
    $auth = new \Auth($user, array('id' => 'username', 'pw' => 'password'));
    $login_result = $auth->login($data['username'],$data['password']); // returns true on successful login
    if($login_result){
      $f3->set('content','dashboard.htm');
			new Session();
      $f3->set('SESSION.pass', '750_BStreet!');
			$f3->set('SESSION.user', $data['username']);
      echo Template::instance()->render('base.htm');
    }else{
      echo "Username or password not correct, please go back and try again.";
      //echo Template::instance()->render('base.htm');
    }
  }
);

$f3->route('GET /brew/@count',
    function($f3) {
        echo $f3->get('PARAMS.count').' bottles of beer on the wall.';
    }
);


//echo $login_result;


//$auth->basic();
//$data = $db->read('users.json');
//echo $data['salachniewicz']['name'];





$f3->run();
