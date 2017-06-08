<?php

class sendexacttarget{
  public function send($f3)
  {
    //for some reason this is not working when including files separately
    //require('vendor/exacttarget/exacttarget_soap_client.php');
    //require('vendor/exacttarget/xmlseclibs.php');
    //require('vendor/exacttarget/soap-wsse.php');
    require('vendor/exacttarget/exacttarget.php');
    //require('vendor/exacttarget/exacttarget_soap_client.php');
    $wsdl = 'https://webservice.s4.exacttarget.com/etframework.wsdl';
    $etusername = $f3->ETLOGIN;//Enter the username you use to login to Exact Target
    $etpassword = $f3->ETPASS;//Enter the password you use to login to Exact Target


    parse_str($f3->get('POST.formData'),$sendData);//this is our formData access it via $sendData['field_name'];

    //if this is a test send we store in test folder, otherwise, real folder
    if($sendData['send_test']){
      $folder = 371004; //Test Send Folder
    }else{
      $folder = $sendData['folder_id'];//need to make these dependent on template read from JSON
    }

    


    //get the checked list inputs
    $myLists = array();
    foreach($sendData['lists'] as $list){
      if(!empty($list)){
        $newList = new ExactTarget_List();
        //$newList->ListName = "";//NOTE: Enabling this will rename existing lists if not careful
        $newList->ID = $list;
        $newList->IDSpecified = true;
        $myLists[] = $newList;
      }
    }
    //var_dump($sendData);

    $sender = new StdClass();
    $sender->name = (!empty($sendData['user_name'])) ? $sendData['user_name'] : "San Diego Tourism Authority" ;
    $sender->email = (!empty($sendData['user_email'])) ? $sendData['user_email'] : "SDINFO@sandiego.org";
    $sender->send_date = $sendData['send_date'];
    $sender->send_time = $sendData['time'];
    $sender->send_period = $sendData['ampm'];



    $fulldatetime = $sender->send_date . ' ' . $sender->send_time . ' ' . $sender->send_period;
    $sender->timestamp = strtotime($fulldatetime);


    $client = new ExactTargetSoapClient($wsdl, array('trace'=>1));
    $client->username = $etusername;
    $client->password = $etpassword;


    $email = new ExactTarget_Email();
    $email->Name = $sendData['email_name'];
    $email->Description = 'Email from MyLittleEmailBuilder.com';
    $email->HTMLBody = $f3->get('POST.html');
    $email->Subject = $sendData['email_subject'];
    $email->EmailType = 'HTML';
    $email->IsHTMLPaste = 'false';
    $email->CategoryIDSpecified = true;
    $email->CategoryID = $folder;


















    //sendEmail($email,$client, $lists,$sender);

    //$sendDate = strtotime("+1 minute");
    $sendDate = strtotime("+10 seconds");
    //If I wanted to create the email separately
    //$object = new SoapVar($email, SOAP_ENC_OBJECT, 'Email', "http://exacttarget.com/wsdl/partnerAPI");

    //$emailSendDef = new ExactTarget_EmailSendDefinition();
    //$emailSendDef->CustomerKey = "333333";
    //$emailSendDef->Name = "Shareable Content Send";
    $sendDef = new ExactTarget_EmailSendDefinition();
    $sendDef->$TestEmailAddr = "salachniewicz@sandiego.org";

    $send = new ExactTarget_Send();
    $send->Email = $email;
    $send->List = $myLists;
    $send->SendDate = $sender->timestamp;
    $send->FromAddress = $sender->email;
    $send->FromName = $sender->name;
    //$send->EmailSendDefinition = $emailSendDef;
    $send->UniqueOpens = '500';




    $object = new SoapVar($send, SOAP_ENC_OBJECT, 'Send', "http://exacttarget.com/wsdl/partnerAPI");

    $request = new ExactTarget_CreateRequest();
    $request->Options = NULL;
    $request->Objects = array($object);

    //$email->CategoryIDSpecified = true;
    //$email->CategoryID = 338240;

    $results = $client->Create($request);
    $response = $results->Results->StatusCode;
    $msg = $results->Results->StatusMessage;

    if($response != "OK"){
      $response = "ERROR: ".$msg.". Check Chrome console for details.";
    }



    $vars = 	array(
      'results'=> $results,
      'statusCode' => $response,
    );
    echo json_encode($vars);


  }
}



?>
