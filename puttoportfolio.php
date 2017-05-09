<?php
require('vendor/exacttarget/exacttarget.php');
$wsdl = 'https://webservice.s4.exacttarget.com/etframework.wsdl';
$etusername = 'salachniewicz';//Enter the username you use to login to Exact Target
$etpassword = '750_BStreet!';//Enter the password you use to login to Exact Target
//method



try {
 
    $client = new ExactTargetSoapClient($wsdl, array('trace'=>1));
		$client->username = $etusername;
    $client->password = $etpassword;
 
    $port = new ExactTarget_Portfolio();
    $port->DisplayName = $DisplayName;
    //$port->CustomerKey = 'API Uploaded Test v10';
    $rs = new ExactTarget_ResourceSpecification();  
    $rs->URN = $ET_file;
    $port->Source = $rs;
    $port->FileName = $DisplayName;
		//$port->FileURL="myfileurl";
    $object = new SoapVar($port, SOAP_ENC_OBJECT, 'Portfolio', "http://exacttarget.com/wsdl/partnerAPI");
 
    $request = new ExactTarget_CreateRequest();
    $request->Options = NULL;
    $request->Objects = array($object);
 
    $results = $client->Create($request);
		//echo '<pre>';
    //print_r($results);
 		//echo '</pre>';
		$myvar = 	array(
			'url'=> 'http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/'.$filename
		);
		echo json_encode($myvar);
		
} catch (SoapFault $e) {
	echo '<pre>';
    print_r($e);
		echo '</pre>';
}

?>