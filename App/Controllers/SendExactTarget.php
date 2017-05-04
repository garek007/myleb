<?php

class sendexacttarget{
  public function send($f3)
  {
    //require('vendor/exacttarget/exacttarget_soap_client.php');
    $wsdl = 'https://webservice.s4.exacttarget.com/etframework.wsdl';
    $etusername = $f3->ETLOGIN;//Enter the username you use to login to Exact Target
    $etpassword = $f3->ETPASS;//Enter the password you use to login to Exact Target

    $folderID = $sendData['folder_id'];//need to make these dependent on template read from JSON
    parse_str($f3->get('POST.formData'),$sendData);//this is our formData access it via $sendData['field_name'];
    $lists = $sendData['lists'];
    var_dump($sendData);
    
    $sender = new StdClass();
    $sender->name = $sendData['user_name'];
    $sender->email = $sendData['user_email'];
    $sender->send_date = $sendData['send_date'];
    $sender->send_time = $sendData['time'];
    $fulldatetime = $sender->send_date . ' ' . $sender->send_time;
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
    $email->CategoryID = $sendData['folder_id'];
 
    //sendEmail($email,$client, $lists,$sender);

    //$sendDate = strtotime("+1 minute");
    $sendDate = strtotime("+10 seconds");

    $emailSendDef = new ExactTarget_EmailSendDefinition();
    $emailSendDef->CustomerKey = "333333";
    //$emailSendDef->Name = "Shareable Content Send";

    $send = new ExactTarget_Send();
    $send->Email = $email;

    $myLists = array();
    foreach($lists as $list){
      $newList = new ExactTarget_List();
      $newList->ID = $list;
      $myLists[] = $newList;

    }

    $send->List = $myLists;

    //$send->SendDate = $sendDate;
    $send->SendDate = $sender->timestamp;
    $send->FromAddress = $sender->email;
    $send->FromName = $sender->name;
    $send->EmailSendDefinition = $emailSendDef;
    $send->UniqueOpens = '500';

    $object = new SoapVar($send, SOAP_ENC_OBJECT, 'Send', "http://exacttarget.com/wsdl/partnerAPI");

    $request = new ExactTarget_CreateRequest();
    $request->Options = NULL;
    $request->Objects = array($object);

    //$email->CategoryIDSpecified = true;
    //$email->CategoryID = 338240;

    $results = $client->Create($request);		
 
    
    echo '<pre>';var_dump($results);echo '</pre>';	


  }
}




function sendEmail($email,$client,$lists,$sender){



	//$lists = implode(",",$lists);

	//$sendDate = strtotime("+1 minute");
	$sendDate = strtotime("+10 seconds");

				$emailSendDef = new ExactTarget_EmailSendDefinition();
        $emailSendDef->CustomerKey = "333333";
        //$emailSendDef->Name = "Shareable Content Send";

								$send = new ExactTarget_Send();
								$send->Email = $email;

								$myLists = array();
								foreach($lists as $list){
									$newList = new ExactTarget_List();
									$newList->ID = $list;
									$myLists[] = $newList;

								}

								//$list = new ExactTarget_List();
								//$list->ID = "299500";
								//$list->ID = '300512';


								//$list2 = new ExactTarget_List();
								//$list2->ID = '384277';

								$send->List = $myLists;


								//$sendDefList->List = $list;
								//$sendDefList->DataSourceTypeID = "List";
								//$sendDefList->SendDefinitionListType = "SourceList";
								//$emailSendDef->SendDefinitionList[] = $sendDefList;


								//$send->SendDate = $sendDate;
								$send->SendDate = $sender->timestamp;
								$send->FromAddress = $sender->email;
								$send->FromName = $sender->name;
								$send->EmailSendDefinition = $emailSendDef;
								$send->UniqueOpens = '500';

                $object = new SoapVar($send, SOAP_ENC_OBJECT, 'Send', "http://exacttarget.com/wsdl/partnerAPI");

                $request = new ExactTarget_CreateRequest();
                $request->Options = NULL;
                $request->Objects = array($object);

								//$email->CategoryIDSpecified = true;
								//$email->CategoryID = 338240;

                $results = $client->Create($request);


				echo '<pre>';var_dump($results);echo '</pre>';

/*

				$emailSendDef = new ExactTarget_EmailSendDefinition();
        $emailSendDef->CustomerKey = "Shareable Content Send2";
        $emailSendDef->Name = "Shareable Content Send";

        //Setup the Send Classification
        $sendClass = new ExactTarget_SendClassification();
        $sendClass->CustomerKey = "470";
        $emailSendDef->SendClassification = $sendClass;

        // Setting Up the Source List
        $emailSendDef->SendDefinitionList = array();
        $sendDefList = new ExactTarget_SendDefinitionList();
        $list = new ExactTarget_List();
        $list->ID = "299500";
        $sendDefList->List = $list;
        $sendDefList->DataSourceTypeID = "List";
        $sendDefList->SendDefinitionListType = "SourceList";
        $emailSendDef->SendDefinitionList[] = $sendDefList;

        // Setting up the exclude list
        /* $sendDefListExclude = new ExactTarget_SendDefinitionList();
        $listExclude = new ExactTarget_List();
        $listExclude->ID = "1729515";
        $sendDefListExclude->List = $listExclude;
        $sendDefListExclude->DataSourceTypeID = "List";
        $sendDefListExclude->SendDefinitionListType = "ExclusionList";
        $emailSendDef->SendDefinitionList[] = $sendDefListExclude;

        // Specify the Email To Send
       // $email = new ExactTarget_Email();
       	$email->ID = "789022";
        $emailSendDef->Email = $email;
        $object = new SoapVar($emailSendDef, SOAP_ENC_OBJECT, 'EmailSendDefinition', "http://exacttarget.com/wsdl/partnerAPI");

        $request = new ExactTarget_CreateRequest();
        $request->Options = NULL;
        $request->Objects = array($object);

        $results = $client->Create($request);
			echo "ID is ".$email->ID;
				echo '<pre>';var_dump($results);echo '</pre>';

*/



	}
?>
